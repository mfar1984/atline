<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\IntegrationSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aws\S3\S3Client;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        $query = Download::query()->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('file_type')) {
            $query->where('file_extension', $request->file_type);
        }

        $downloads = $query->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
        $fileTypes = Download::distinct()->pluck('file_extension')->filter()->sort()->values();

        return view('internal.download.index', compact('downloads', 'fileTypes'));
    }

    public function show(Download $download)
    {
        return view('internal.download.show', compact('download'));
    }

    public function store(Request $request)
        {
            // Log at the very beginning to confirm we reach this method
            \Log::info('=== STORE METHOD CALLED ===');
            \Log::info('Request data', [
                'has_file' => $request->hasFile('file'),
                'name' => $request->input('name'),
                'all_input' => $request->all(),
                'files' => $request->allFiles(),
            ]);

            // Validate input first
            try {
                $request->validate([
                    'name' => 'required|string|max:255',
                ]);
                \Log::info('Name validation passed');
            } catch (\Exception $e) {
                \Log::error('Name validation failed', ['error' => $e->getMessage()]);
                throw $e;
            }

            // Manual file handling to bypass upload_tmp_dir issue
            if (!$request->hasFile('file')) {
                \Log::error('No file in request');
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded.',
                ], 422);
            }

            $file = $request->file('file');
            \Log::info('File object retrieved', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'error' => $file->getError(),
                'is_valid' => $file->isValid(),
            ]);

            // Validate file manually
            if (!$file->isValid()) {
                \Log::error('File is not valid', ['error_code' => $file->getError()]);
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed. Error code: ' . $file->getError(),
                ], 422);
            }

            // Validate file size (100MB max)
            if ($file->getSize() > 104857600) {
                \Log::error('File too large', ['size' => $file->getSize()]);
                return response()->json([
                    'success' => false,
                    'message' => 'File size must not exceed 100MB.',
                ], 422);
            }

            // Check R2 configuration first
            $storageSetting = IntegrationSetting::where('integration_type', 'storage')->first();
            if (!$storageSetting || !$storageSetting->isConnected()) {
                \Log::error('R2 not configured');
                return response()->json([
                    'success' => false,
                    'message' => 'Cloudflare R2 not configured. Please configure storage in Settings > Integrations > Storage.',
                ], 400);
            }

            \Log::info('Creating download record');
            $download = Download::create([
                'name' => $request->name,
                'original_filename' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_extension' => strtolower($file->getClientOriginalExtension()),
                'file_size' => $file->getSize(),
                'status' => 'pending',
                'upload_progress' => 0,
                'uploaded_by' => auth()->id(),
            ]);
            \Log::info('Download record created', ['id' => $download->id]);

            // Store file temporarily
            try {
                \Log::info('Attempting to store file temporarily');
                $tempPath = $file->store('temp/downloads', 'local');
                \Log::info('File stored temporarily', ['path' => $tempPath]);
            } catch (\Exception $e) {
                \Log::error('Failed to store temp file', [
                    'error' => $e->getMessage(),
                    'file' => $file->getClientOriginalName(),
                ]);

                $download->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process file. Please try again.',
                ], 500);
            }

            $downloadId = $download->id;

            // Use queue for background processing
            \Log::info('Dispatching upload job');
            \App\Jobs\UploadToR2Job::dispatch($downloadId, $tempPath);

            // Log upload activity
            try {
                ActivityLogService::logUpload('internal_downloads', "Uploaded file {$download->name}", [
                    'file_name' => $download->original_filename,
                    'file_type' => $download->file_type,
                    'file_size' => $download->file_size,
                ]);
            } catch (\Exception $e) {
                \Log::error('Activity logging failed: ' . $e->getMessage());
            }

            \Log::info('=== STORE METHOD COMPLETED SUCCESSFULLY ===');
            return response()->json([
                'success' => true,
                'message' => 'File queued for upload',
                'download_id' => $downloadId,
            ]);
        }

    protected function processUploadToR2(Download $download, string $tempPath)
    {
        try {
            $download->update(['status' => 'uploading', 'upload_progress' => 1]);

            $storageSetting = IntegrationSetting::where('integration_type', 'storage')->first();
            
            if (!$storageSetting || !$storageSetting->isConnected()) {
                throw new \Exception('Cloudflare R2 not configured.');
            }

            $credentials = $storageSetting->getDecryptedCredentials();
            
            if (empty($credentials['account_id']) || empty($credentials['access_key_id']) || empty($credentials['secret_access_key'])) {
                throw new \Exception('Cloudflare R2 credentials not configured');
            }

            $bucketName = $credentials['bucket_name'] ?? null;
            if (!$bucketName) {
                throw new \Exception('Bucket name not configured');
            }

            $download->update(['upload_progress' => 5]);

            $s3Client = $this->createS3Client($credentials);

            $download->update(['upload_progress' => 10]);

            $filename = date('Y/m/') . Str::uuid() . '.' . $download->file_extension;
            $folderPath = $credentials['folder_path'] ?? 'downloads';
            $objectName = trim($folderPath, '/') . '/' . $filename;

            $tempFullPath = Storage::disk('local')->path($tempPath);
            $fileSize = filesize($tempFullPath);

            // Use multipart for files > 5MB (better progress tracking)
            if ($fileSize > 5 * 1024 * 1024) {
                $this->multipartUploadToR2($s3Client, $bucketName, $objectName, $tempFullPath, $download, $fileSize);
            } else {
                $this->simpleUploadToR2($s3Client, $bucketName, $objectName, $tempFullPath, $download);
            }

            // Build public URL
            $publicUrl = null;
            if (!empty($credentials['public_url'])) {
                $publicUrl = rtrim($credentials['public_url'], '/') . '/' . $objectName;
            }

            $download->update([
                'storage_path' => $objectName,
                'storage_url' => $publicUrl,
                'status' => 'completed',
                'upload_progress' => 100,
            ]);

            Storage::disk('local')->delete($tempPath);

        } catch (\Exception $e) {
            $download->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Storage::disk('local')->delete($tempPath);
        }
    }

    protected function simpleUploadToR2($s3Client, $bucketName, $objectName, $filePath, Download $download)
    {
        $download->update(['upload_progress' => 15]);
        
        $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key' => $objectName,
            'SourceFile' => $filePath,
            'ContentType' => $download->file_type,
        ]);
        
        $download->update(['upload_progress' => 95]);
    }

    protected function multipartUploadToR2($s3Client, $bucketName, $objectName, $filePath, Download $download, $fileSize)
    {
        $partSize = 5 * 1024 * 1024; // 5MB per part
        $totalParts = ceil($fileSize / $partSize);
        
        $download->update(['upload_progress' => 12]);
        
        $result = $s3Client->createMultipartUpload([
            'Bucket' => $bucketName,
            'Key' => $objectName,
            'ContentType' => $download->file_type,
        ]);
        
        $uploadId = $result['UploadId'];
        $parts = [];
        
        $download->update(['upload_progress' => 15]);
        
        try {
            $handle = fopen($filePath, 'rb');
            $partNumber = 1;
            
            while (!feof($handle)) {
                $data = fread($handle, $partSize);
                
                $uploadResult = $s3Client->uploadPart([
                    'Bucket' => $bucketName,
                    'Key' => $objectName,
                    'UploadId' => $uploadId,
                    'PartNumber' => $partNumber,
                    'Body' => $data,
                ]);
                
                $parts[] = [
                    'PartNumber' => $partNumber,
                    'ETag' => $uploadResult['ETag'],
                ];
                
                // Progress: 15-90%
                $progress = 15 + (int)(($partNumber / $totalParts) * 75);
                $download->update(['upload_progress' => min($progress, 90)]);
                
                $partNumber++;
            }
            
            fclose($handle);
            
            $download->update(['upload_progress' => 92]);
            
            $s3Client->completeMultipartUpload([
                'Bucket' => $bucketName,
                'Key' => $objectName,
                'UploadId' => $uploadId,
                'MultipartUpload' => ['Parts' => $parts],
            ]);
            
            $download->update(['upload_progress' => 95]);
            
        } catch (\Exception $e) {
            $s3Client->abortMultipartUpload([
                'Bucket' => $bucketName,
                'Key' => $objectName,
                'UploadId' => $uploadId,
            ]);
            throw $e;
        }
    }


    public function destroy(Download $download)
    {
        if ($download->storage_path) {
            $storageSetting = IntegrationSetting::where('integration_type', 'storage')->first();
            
            if ($storageSetting && $storageSetting->isConnected()) {
                try {
                    $credentials = $storageSetting->getDecryptedCredentials();
                    $bucketName = $credentials['bucket_name'] ?? null;
                    
                    if ($credentials['account_id'] && $credentials['access_key_id'] && $credentials['secret_access_key'] && $bucketName) {
                        $s3Client = $this->createS3Client($credentials);
                        
                        $s3Client->deleteObject([
                            'Bucket' => $bucketName,
                            'Key' => $download->storage_path,
                        ]);
                    }
                } catch (\Exception $e) {
                    // Continue with deletion from database
                }
            }
        }

        // Log delete activity
        try {
            ActivityLogService::logDelete($download, 'internal_downloads', "Deleted file {$download->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $download->delete();

        return redirect()->route('internal.download.index')->with('success', 'File deleted successfully.');
    }


    public function download(Download $download)
    {
        if ($download->status !== 'completed' || !$download->storage_path) {
            return back()->with('error', 'File is not available for download.');
        }

        $download->incrementDownloadCount();
        
        // Log download activity
        try {
            ActivityLogService::logDownload('internal_downloads', "Downloaded file {$download->name}", [
                'file_name' => $download->original_filename,
                'file_type' => $download->file_type,
                'file_size' => $download->file_size,
            ]);
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $storageSetting = IntegrationSetting::where('integration_type', 'storage')->first();
        
        if (!$storageSetting || !$storageSetting->isConnected()) {
            return back()->with('error', 'Cloudflare R2 not configured.');
        }

        try {
            $credentials = $storageSetting->getDecryptedCredentials();
            $bucketName = $credentials['bucket_name'] ?? null;
            
            $s3Client = $this->createS3Client($credentials);
            
            // Generate presigned URL for download
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => $bucketName,
                'Key' => $download->storage_path,
                'ResponseContentDisposition' => 'attachment; filename="' . $download->original_filename . '"',
            ]);
            
            $request = $s3Client->createPresignedRequest($cmd, '+1 hour');
            $presignedUrl = (string) $request->getUri();
            
            return redirect($presignedUrl);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate download link: ' . $e->getMessage());
        }
    }

    public function checkProgress(Download $download)
    {
        return response()->json([
            'id' => $download->id,
            'status' => $download->status,
            'progress' => $download->upload_progress,
            'error_message' => $download->error_message,
        ]);
    }

    /**
     * Create S3 Client for R2 with proper SSL configuration
     */
    protected function createS3Client(array $credentials): S3Client
    {
        $clientConfig = [
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => "https://{$credentials['account_id']}.r2.cloudflarestorage.com",
            'credentials' => [
                'key' => $credentials['access_key_id'],
                'secret' => $credentials['secret_access_key'],
            ],
            'use_path_style_endpoint' => true,
        ];
        
        // Disable SSL verification for local development
        if (config('app.env') === 'local') {
            $clientConfig['http'] = [
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ],
            ];
        }
        
        return new S3Client($clientConfig);
    }
}
