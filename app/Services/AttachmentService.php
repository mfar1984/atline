<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\IntegrationSetting;
use App\Models\SystemSetting;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    /**
     * Get allowed MIME types based on allowed extensions
     */
    protected function getAllowedMimeTypes(): array
    {
        $extensionToMime = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
        ];
        
        $allowedExtensions = $this->getAllowedExtensions();
        $mimeTypes = [];
        
        foreach ($allowedExtensions as $ext) {
            if (isset($extensionToMime[$ext])) {
                $mimeTypes[] = $extensionToMime[$ext];
            }
        }
        
        return array_unique($mimeTypes);
    }

    /**
     * Get allowed extensions from SystemSetting
     */
    protected function getAllowedExtensions(): array
    {
        $allowedTypes = SystemSetting::getValue('defaults', 'allowed_file_types', 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip');
        return array_map('trim', explode(',', strtolower($allowedTypes)));
    }

    /**
     * Get max file size in bytes from SystemSetting
     */
    protected function getMaxFileSize(): int
    {
        $maxSizeMB = SystemSetting::getValue('defaults', 'attachment_max_size', 10);
        return $maxSizeMB * 1048576; // Convert MB to bytes
    }

    /**
     * Validate file type
     */
    public function validateFileType(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        
        return in_array($extension, $this->getAllowedExtensions()) 
            && in_array($mimeType, $this->getAllowedMimeTypes());
    }

    /**
     * Validate file size
     */
    public function validateFileSize(UploadedFile $file): bool
    {
        return $file->getSize() <= $this->getMaxFileSize();
    }


    /**
     * Store attachment with R2 support
     */
    public function store(UploadedFile $file, $model, string $displayName = null): Attachment
    {
        // Check R2 configuration
        $storageSetting = IntegrationSetting::where('integration_type', IntegrationSetting::TYPE_STORAGE)
            ->where('is_active', true)
            ->first();

        if ($storageSetting && $storageSetting->isConnected()) {
            return $this->storeToR2($file, $model, $displayName, $storageSetting);
        }

        return $this->storeToLocal($file, $model, $displayName);
    }

    /**
     * Store to local storage
     */
    protected function storeToLocal(UploadedFile $file, $model, string $displayName = null): Attachment
    {
        $folder = strtolower(class_basename($model)) . 's';
        $path = $file->store("attachments/{$folder}", 'public');
        
        return Attachment::create([
            'attachable_id' => $model->id,
            'attachable_type' => get_class($model),
            'file_name' => $displayName ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'storage_type' => Attachment::STORAGE_LOCAL,
            'storage_path' => null,
            'storage_url' => null,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Store to R2 storage
     */
    protected function storeToR2(UploadedFile $file, $model, string $displayName, IntegrationSetting $storageSetting): Attachment
    {
        $credentials = $storageSetting->getDecryptedCredentials();
        
        // Validate credentials
        if (empty($credentials['account_id']) || empty($credentials['access_key_id']) || 
            empty($credentials['secret_access_key']) || empty($credentials['bucket_name'])) {
            Log::error('R2 storage credentials incomplete', [
                'has_account_id' => !empty($credentials['account_id']),
                'has_access_key' => !empty($credentials['access_key_id']),
                'has_secret_key' => !empty($credentials['secret_access_key']),
                'has_bucket' => !empty($credentials['bucket_name']),
            ]);
            throw new \Exception('Storage configuration error. Please contact administrator.');
        }

        try {
            // Create S3 client
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => 'auto',
                'endpoint' => "https://{$credentials['account_id']}.r2.cloudflarestorage.com",
                'credentials' => [
                    'key' => $credentials['access_key_id'],
                    'secret' => $credentials['secret_access_key'],
                ],
            ]);

            // Generate object path
            $folder = strtolower(class_basename($model)) . 's';
            $filename = date('Y/m/') . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $folderPath = $credentials['folder_path'] ?? 'attachments';
            $objectName = trim($folderPath, '/') . '/' . $folder . '/' . $filename;

            // Upload to R2
            $s3Client->putObject([
                'Bucket' => $credentials['bucket_name'],
                'Key' => $objectName,
                'Body' => fopen($file->getRealPath(), 'rb'),
                'ContentType' => $file->getMimeType(),
            ]);

            // Build public URL
            $publicUrl = null;
            if (!empty($credentials['public_url'])) {
                $publicUrl = rtrim($credentials['public_url'], '/') . '/' . $objectName;
            }

            Log::info('File uploaded to R2', [
                'object_name' => $objectName,
                'file_name' => $displayName ?? $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);

            // Create attachment record
            return Attachment::create([
                'attachable_id' => $model->id,
                'attachable_type' => get_class($model),
                'file_name' => $displayName ?? $file->getClientOriginalName(),
                'file_path' => null,
                'storage_type' => Attachment::STORAGE_R2,
                'storage_path' => $objectName,
                'storage_url' => $publicUrl,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        } catch (\Aws\Exception\AwsException $e) {
            Log::error('R2 upload failed', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Unable to access cloud storage. Please try again later.');
        } catch (\Exception $e) {
            Log::error('R2 upload error', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }


    /**
     * Delete attachment (supports both local and R2)
     */
    public function delete(Attachment $attachment): bool
    {
        if ($attachment->storage_type === Attachment::STORAGE_R2) {
            $this->deleteFromR2($attachment);
        } else {
            $this->deleteFromLocal($attachment);
        }
        
        return $attachment->delete();
    }

    /**
     * Delete from local storage
     */
    protected function deleteFromLocal(Attachment $attachment): void
    {
        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
    }

    /**
     * Delete from R2 storage
     */
    protected function deleteFromR2(Attachment $attachment): void
    {
        if (!$attachment->storage_path) {
            return;
        }

        try {
            $storageSetting = IntegrationSetting::where('integration_type', IntegrationSetting::TYPE_STORAGE)
                ->where('is_active', true)
                ->first();

            if (!$storageSetting) {
                Log::warning("R2 not configured, cannot delete file: {$attachment->storage_path}");
                return;
            }

            $credentials = $storageSetting->getDecryptedCredentials();
            
            if (empty($credentials['account_id']) || empty($credentials['access_key_id']) || 
                empty($credentials['secret_access_key']) || empty($credentials['bucket_name'])) {
                Log::warning("R2 credentials incomplete, cannot delete file: {$attachment->storage_path}");
                return;
            }

            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => 'auto',
                'endpoint' => "https://{$credentials['account_id']}.r2.cloudflarestorage.com",
                'credentials' => [
                    'key' => $credentials['access_key_id'],
                    'secret' => $credentials['secret_access_key'],
                ],
            ]);

            $s3Client->deleteObject([
                'Bucket' => $credentials['bucket_name'],
                'Key' => $attachment->storage_path,
            ]);

            Log::info("Deleted R2 file: {$attachment->storage_path}");
        } catch (\Exception $e) {
            Log::error("Failed to delete R2 file: {$attachment->storage_path}", [
                'error' => $e->getMessage()
            ]);
            // Continue with database deletion even if R2 deletion fails
        }
    }

    /**
     * Get allowed file types for display
     */
    public function getAllowedTypesString(): string
    {
        return implode(', ', array_map('strtoupper', $this->getAllowedExtensions()));
    }

    /**
     * Get max file size in MB
     */
    public function getMaxFileSizeMB(): int
    {
        return SystemSetting::getValue('defaults', 'attachment_max_size', 10);
    }
}
