# Design Document: R2 Storage Integration for Project Attachments

## Overview

This design integrates Cloudflare R2 object storage with the external project attachment system. The implementation follows the same pattern as the existing internal downloads R2 integration but operates on the `attachments` table instead of the `downloads` table, maintaining complete separation between the two systems.

The system will:
- Store project/asset attachments to R2 when storage integration is configured
- Fall back to local storage when R2 is not configured
- Support both local and R2 storage simultaneously (existing local files remain accessible)
- Maintain client isolation for all attachment operations
- Provide seamless upload, download, and deletion operations

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    External Module                           │
│  ┌──────────────────┐         ┌──────────────────┐         │
│  │ ProjectController│         │AttachmentController│        │
│  └────────┬─────────┘         └────────┬──────────┘        │
│           │                             │                    │
│           └──────────┬──────────────────┘                    │
│                      │                                       │
│           ┌──────────▼──────────┐                           │
│           │  AttachmentService  │                           │
│           └──────────┬──────────┘                           │
│                      │                                       │
│        ┌─────────────┼─────────────┐                        │
│        │             │             │                         │
│   ┌────▼────┐   ┌───▼────┐   ┌───▼────────┐               │
│   │ Local   │   │   R2   │   │ Attachment │               │
│   │ Storage │   │ Client │   │   Model    │               │
│   └─────────┘   └────────┘   └────────────┘               │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    Internal Module (Isolated)                │
│  ┌──────────────────┐                                       │
│  │ DownloadController│                                      │
│  └────────┬─────────┘                                       │
│           │                                                  │
│      ┌────▼────────┐                                        │
│      │  Download   │                                        │
│      │   Model     │                                        │
│      └─────────────┘                                        │
└─────────────────────────────────────────────────────────────┘
```

### Storage Decision Flow

```
File Upload Request
       │
       ▼
Check R2 Configuration
       │
       ├─── Configured & Active ──► Upload to R2
       │                              │
       │                              ├─ Success ──► Save to DB (storage_type='r2')
       │                              │
       │                              └─ Failure ──► Return Error
       │
       └─── Not Configured ────────► Upload to Local Storage
                                      │
                                      └─ Success ──► Save to DB (storage_type='local')
```

## Components and Interfaces

### 1. Database Schema Changes

**Migration: `add_r2_storage_fields_to_attachments_table`**

```php
Schema::table('attachments', function (Blueprint $table) {
    $table->string('storage_type')->default('local')->after('file_path');
    $table->string('storage_path')->nullable()->after('storage_type');
    $table->text('storage_url')->nullable()->after('storage_path');
});
```

**Fields:**
- `storage_type`: enum('local', 'r2') - Identifies storage location
- `storage_path`: string - R2 object key (e.g., "attachments/2026/01/uuid.pdf")
- `storage_url`: text - Public R2 URL for direct access

### 2. AttachmentService Enhancement

**Location:** `app/Services/AttachmentService.php`

**New Methods:**

```php
class AttachmentService
{
    // Existing properties
    protected array $allowedTypes = [...];
    protected array $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    protected int $maxFileSize = 15728640; // 15MB

    /**
     * Store attachment with R2 support
     */
    public function store(UploadedFile $file, $model, string $displayName = null): Attachment
    {
        // Check R2 configuration
        $storageSetting = IntegrationSetting::where('integration_type', 'storage')
            ->where('is_active', true)
            ->first();

        if ($storageSetting && $storageSetting->isConnected()) {
            return $this->storeToR2($file, $model, $displayName, $storageSetting);
        }

        return $this->storeToLocal($file, $model, $displayName);
    }

    /**
     * Store to local storage (existing behavior)
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
            'storage_type' => 'local',
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
            throw new \Exception('R2 storage credentials incomplete');
        }

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

        // Create attachment record
        return Attachment::create([
            'attachable_id' => $model->id,
            'attachable_type' => get_class($model),
            'file_name' => $displayName ?? $file->getClientOriginalName(),
            'file_path' => null,
            'storage_type' => 'r2',
            'storage_path' => $objectName,
            'storage_url' => $publicUrl,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Delete attachment (supports both local and R2)
     */
    public function delete(Attachment $attachment): bool
    {
        if ($attachment->storage_type === 'r2') {
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
            $storageSetting = IntegrationSetting::where('integration_type', 'storage')
                ->where('is_active', true)
                ->first();

            if (!$storageSetting) {
                \Log::warning("R2 not configured, cannot delete file: {$attachment->storage_path}");
                return;
            }

            $credentials = $storageSetting->getDecryptedCredentials();
            
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

            \Log::info("Deleted R2 file: {$attachment->storage_path}");
        } catch (\Exception $e) {
            \Log::error("Failed to delete R2 file: {$attachment->storage_path}", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

### 3. AttachmentController Enhancement

**Location:** `app/Http/Controllers/External/AttachmentController.php`

**Modified download() method:**

```php
public function download(Attachment $attachment)
{
    $client = $this->getClientForUser();
    
    // Client isolation check (existing)
    if ($client) {
        $canAccess = false;
        
        if ($attachment->attachable_type === 'App\Models\Project') {
            $project = Project::find($attachment->attachable_id);
            $canAccess = $project && $project->client_id === $client->id;
        } elseif ($attachment->attachable_type === 'App\Models\Asset') {
            $asset = Asset::with('project')->find($attachment->attachable_id);
            $canAccess = $asset && $asset->project && $asset->project->client_id === $client->id;
        }
        
        if (!$canAccess) {
            abort(403, 'You do not have permission to download this attachment.');
        }
    }
    
    // Route to appropriate storage
    if ($attachment->storage_type === 'r2') {
        return $this->downloadFromR2($attachment);
    }
    
    return $this->downloadFromLocal($attachment);
}

protected function downloadFromLocal(Attachment $attachment)
{
    if (!Storage::disk('public')->exists($attachment->file_path)) {
        abort(404, 'File not found');
    }

    return Storage::disk('public')->download(
        $attachment->file_path,
        $attachment->file_name
    );
}

protected function downloadFromR2(Attachment $attachment)
{
    if (!$attachment->storage_path) {
        abort(404, 'File not found');
    }

    $storageSetting = IntegrationSetting::where('integration_type', 'storage')
        ->where('is_active', true)
        ->first();
    
    if (!$storageSetting) {
        abort(500, 'R2 storage not configured');
    }

    try {
        $credentials = $storageSetting->getDecryptedCredentials();
        
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => "https://{$credentials['account_id']}.r2.cloudflarestorage.com",
            'credentials' => [
                'key' => $credentials['access_key_id'],
                'secret' => $credentials['secret_access_key'],
            ],
        ]);
        
        // Generate presigned URL for download
        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $credentials['bucket_name'],
            'Key' => $attachment->storage_path,
            'ResponseContentDisposition' => 'attachment; filename="' . $attachment->file_name . '"',
        ]);
        
        $request = $s3Client->createPresignedRequest($cmd, '+1 hour');
        $presignedUrl = (string) $request->getUri();
        
        return redirect($presignedUrl);
    } catch (\Exception $e) {
        \Log::error('R2 download failed', [
            'attachment_id' => $attachment->id,
            'error' => $e->getMessage()
        ]);
        abort(500, 'Failed to generate download link');
    }
}
```

### 4. Attachment Model Enhancement

**Location:** `app/Models/Attachment.php`

**Add new methods:**

```php
/**
 * Check if attachment is stored in R2
 */
public function isR2Storage(): bool
{
    return $this->storage_type === 'r2';
}

/**
 * Check if attachment is stored locally
 */
public function isLocalStorage(): bool
{
    return $this->storage_type === 'local';
}

/**
 * Get storage location label
 */
public function getStorageLocationAttribute(): string
{
    return $this->storage_type === 'r2' ? 'Cloud Storage' : 'Local Storage';
}
```

## Data Models

### Attachment Model (Enhanced)

```php
class Attachment extends Model
{
    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'file_name',
        'file_path',          // Used for local storage
        'storage_type',       // 'local' or 'r2'
        'storage_path',       // R2 object key
        'storage_url',        // R2 public URL
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    // Storage type constants
    const STORAGE_LOCAL = 'local';
    const STORAGE_R2 = 'r2';
}
```

### IntegrationSetting Model (Existing)

```php
class IntegrationSetting extends Model
{
    // Existing implementation
    // Used to retrieve R2 credentials:
    // - account_id
    // - access_key_id
    // - secret_access_key
    // - bucket_name
    // - folder_path
    // - public_url
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Storage Type Consistency
*For any* attachment record, if `storage_type` is 'r2', then `storage_path` must be non-null, and if `storage_type` is 'local', then `file_path` must be non-null.
**Validates: Requirements 2.4, 2.5**

### Property 2: R2 Upload Success Implies Record Creation
*For any* valid file upload when R2 is configured and active, if the R2 upload succeeds, then an attachment record with `storage_type='r2'` must be created in the database.
**Validates: Requirements 1.1, 1.4**

### Property 3: Local Fallback on R2 Unavailable
*For any* file upload when R2 is not configured or inactive, the system must store the file locally and create an attachment record with `storage_type='local'`.
**Validates: Requirements 1.2**

### Property 4: Download Routing Correctness
*For any* attachment download request, if `storage_type='r2'`, the system must retrieve from R2 storage, and if `storage_type='local'`, the system must retrieve from local storage.
**Validates: Requirements 3.1, 3.2**

### Property 5: Deletion Completeness
*For any* attachment deletion, if `storage_type='r2'`, the file must be deleted from R2 and the database record removed, and if `storage_type='local'`, the file must be deleted from local storage and the database record removed.
**Validates: Requirements 4.1, 4.2**

### Property 6: Client Isolation for All Operations
*For any* client user and any attachment operation (view, download, delete), the system must only allow access to attachments belonging to projects owned by that client.
**Validates: Requirements 8.1, 8.2, 8.3**

### Property 7: Attachment-Download Separation
*For any* query to the attachments listing page, the results must only include records from the `attachments` table and must not include any records from the `downloads` table.
**Validates: Requirements 5.1, 5.3, 7.5**

### Property 8: File Validation Enforcement
*For any* file upload attempt, if the file size exceeds 15MB or the file extension is not in the allowed list (jpg, jpeg, png, pdf, doc, docx, xls, xlsx), the upload must be rejected with an error message.
**Validates: Requirements 6.1, 6.2, 6.5**

### Property 9: R2 Configuration Flexibility
*For any* attachment, regardless of current R2 configuration status, the system must be able to retrieve the file from its original storage location (local or R2) based on the `storage_type` field.
**Validates: Requirements 9.4**

### Property 10: Error Logging Completeness
*For any* R2 operation (upload, download, delete) that fails, the system must log the error with relevant details (file name, operation type, error message) before returning an error to the user.
**Validates: Requirements 10.1, 10.2, 10.3, 10.4**

## Error Handling

### Upload Errors

1. **R2 Credentials Invalid**
   - Log error with credential validation failure details
   - Return user-friendly error: "Storage configuration error. Please contact administrator."
   - Do NOT create attachment record

2. **R2 Bucket Inaccessible**
   - Log error with bucket name and access attempt details
   - Return error: "Unable to access cloud storage. Please try again later."
   - Do NOT create attachment record

3. **File Size Exceeded**
   - Validate before upload attempt
   - Return error: "File size exceeds maximum limit of 15MB"
   - Do NOT attempt upload

4. **Invalid File Type**
   - Validate before upload attempt
   - Return error: "File type not allowed. Allowed types: JPG, JPEG, PNG, PDF, DOC, DOCX, XLS, XLSX"
   - Do NOT attempt upload

### Download Errors

1. **File Not Found in R2**
   - Log error with storage_path
   - Return 404 error: "File not found"

2. **R2 Connection Failed**
   - Log error with connection details
   - Return 500 error: "Failed to generate download link"

3. **Client Isolation Violation**
   - Log security warning with user ID and attachment ID
   - Return 403 error: "You do not have permission to download this attachment"

### Deletion Errors

1. **R2 Deletion Failed**
   - Log error but continue with database deletion
   - Rationale: Orphaned files in R2 are preferable to orphaned database records
   - Return success to user (database record deleted)

2. **File Already Deleted from Storage**
   - Handle gracefully (no error)
   - Continue with database deletion
   - Return success to user

## Testing Strategy

### Unit Tests

Unit tests will verify specific examples and edge cases:

1. **AttachmentService Tests**
   - Test local storage when R2 not configured
   - Test R2 storage when configured and active
   - Test file validation (size, type)
   - Test deletion from local storage
   - Test deletion from R2 storage

2. **AttachmentController Tests**
   - Test download routing (local vs R2)
   - Test client isolation enforcement
   - Test 404 handling for missing files
   - Test 403 handling for unauthorized access

3. **Integration Tests**
   - Test full upload flow (project creation with attachments)
   - Test attachment listing with mixed storage types
   - Test project deletion with attachment cleanup

### Property-Based Tests

Property-based tests will verify universal properties across all inputs using **PHPUnit with Eris** (PHP property-based testing library):

**Configuration:** Each property test must run minimum 100 iterations.

**Test Tags:** Each test must include a comment referencing the design property:
```php
/**
 * Feature: r2-attachment-integration, Property 1: Storage Type Consistency
 * @test
 */
```

**Property Tests to Implement:**

1. **Property 1: Storage Type Consistency**
   - Generate random attachments with various storage_type values
   - Verify storage_path/file_path consistency

2. **Property 6: Client Isolation**
   - Generate random client users and attachments
   - Verify access control for all operations

3. **Property 7: Attachment-Download Separation**
   - Generate random queries
   - Verify no downloads table records in results

4. **Property 8: File Validation**
   - Generate random file sizes and extensions
   - Verify rejection of invalid files

### Manual Testing Checklist

1. Configure R2 storage in Settings > Integrations > Storage
2. Upload attachment to project (verify R2 storage)
3. Disable R2 storage
4. Upload attachment to project (verify local storage)
5. Re-enable R2 storage
6. Download both attachments (verify both work)
7. Delete both attachments (verify cleanup)
8. Test client isolation (client user cannot access other client's attachments)
9. Verify attachments page shows only attachments (not downloads)
10. Verify internal downloads page shows only downloads (not attachments)

## Implementation Notes

### Separation from Internal Downloads

The implementation must maintain strict separation:

1. **Different Tables:** `attachments` vs `downloads`
2. **Different Controllers:** `AttachmentController` vs `DownloadController`
3. **Different Routes:** `/external/attachments/*` vs `/internal/download/*`
4. **Different Services:** `AttachmentService` (new R2 logic) vs `UploadToR2Job` (existing download logic)
5. **Different Folder Paths in R2:** `attachments/projects/` vs `downloads/`

### AWS SDK Dependency

The implementation requires `aws/aws-sdk-php` package (already installed for internal downloads).

### Configuration Reuse

The implementation will reuse the existing `integration_settings` table with `integration_type='storage'` for R2 credentials. No new configuration table needed.

### Backward Compatibility

Existing attachments with `storage_type='local'` will continue to work. The system supports mixed storage types simultaneously.
