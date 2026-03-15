<?php

namespace App\Jobs;

use App\Models\Download;
use App\Models\IntegrationSetting;
use Aws\S3\S3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadToR2Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    protected $downloadId;
    protected $tempPath;

    public function __construct(int $downloadId, string $tempPath)
    {
        $this->downloadId = $downloadId;
        $this->tempPath = $tempPath;
    }

    public function handle()
    {
        $download = Download::find($this->downloadId);
        
        if (!$download) {
            return;
        }

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

            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => 'auto',
                'endpoint' => "https://{$credentials['account_id']}.r2.cloudflarestorage.com",
                'credentials' => [
                    'key' => $credentials['access_key_id'],
                    'secret' => $credentials['secret_access_key'],
                ],
            ]);

            $download->update(['upload_progress' => 10]);

            $filename = date('Y/m/') . Str::uuid() . '.' . $download->file_extension;
            $folderPath = $credentials['folder_path'] ?? 'downloads';
            $objectName = trim($folderPath, '/') . '/' . $filename;

            $tempFullPath = Storage::disk('local')->path($this->tempPath);
            
            if (!file_exists($tempFullPath)) {
                throw new \Exception('Temporary file not found');
            }

            $fileSize = filesize($tempFullPath);

            // Upload with progress tracking (10-95%)
            $this->uploadWithProgress($s3Client, $bucketName, $objectName, $tempFullPath, $download, $fileSize);

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

            // Clean up temp file
            Storage::disk('local')->delete($this->tempPath);

        } catch (\Exception $e) {
            $download->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            // Clean up temp file on error
            if (Storage::disk('local')->exists($this->tempPath)) {
                Storage::disk('local')->delete($this->tempPath);
            }
        }
    }

    protected function uploadWithProgress($s3Client, $bucketName, $objectName, $filePath, Download $download, $fileSize)
    {
        // Use multipart upload for files > 5MB for better progress tracking
        if ($fileSize > 5 * 1024 * 1024) {
            $this->multipartUpload($s3Client, $bucketName, $objectName, $filePath, $download);
        } else {
            // For smaller files, simulate progress while uploading
            $this->simpleUploadWithProgress($s3Client, $bucketName, $objectName, $filePath, $download, $fileSize);
        }
    }

    protected function simpleUploadWithProgress($s3Client, $bucketName, $objectName, $filePath, Download $download, $fileSize)
    {
        // Read file content
        $content = file_get_contents($filePath);
        
        // Simulate progress updates for small files
        $steps = min(20, max(5, (int)($fileSize / 10000))); // 5-20 steps based on file size
        
        for ($i = 1; $i <= $steps; $i++) {
            $progress = 10 + (int)(($i / $steps) * 40); // 10-50%
            $download->update(['upload_progress' => $progress]);
            usleep(50000); // 50ms delay for visual feedback
        }
        
        // Actual upload
        $download->update(['upload_progress' => 55]);
        
        $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key' => $objectName,
            'Body' => $content,
            'ContentType' => $download->file_type,
        ]);
        
        // Post-upload progress
        for ($i = 60; $i <= 95; $i += 5) {
            $download->update(['upload_progress' => $i]);
            usleep(30000); // 30ms delay
        }
    }

    protected function multipartUpload($s3Client, $bucketName, $objectName, $filePath, Download $download)
    {
        $fileSize = filesize($filePath);
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
                
                // Update progress smoothly (15-90% range)
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
}
