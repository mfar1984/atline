# Requirements Document: R2 Storage Integration for Project Attachments

## Introduction

This feature integrates Cloudflare R2 storage with the project attachment upload system, enabling files uploaded through the external project creation/editing pages to be stored in cloud storage instead of local disk. The external attachments page will retrieve and display files from R2 storage, maintaining complete separation from the internal downloads module.

## Glossary

- **Attachment_System**: The polymorphic file attachment system for projects and assets
- **R2_Storage**: Cloudflare R2 object storage service (S3-compatible)
- **Storage_Integration**: The integration settings configured in settings/integrations
- **External_Module**: The client-facing project and attachment management system
- **Internal_Downloads**: The separate internal file download system (must remain isolated)
- **AttachmentService**: The service class handling attachment storage operations
- **Storage_Path**: The R2 object key/path where the file is stored
- **Storage_URL**: The public URL for accessing the file from R2

## Requirements

### Requirement 1: R2 Storage Integration for Attachments

**User Story:** As a system administrator, I want project attachments to be stored in Cloudflare R2, so that I can leverage cloud storage instead of consuming local disk space.

#### Acceptance Criteria

1. WHEN a file is uploaded through the project create/edit form, THE Attachment_System SHALL store the file to R2_Storage if Storage_Integration is configured and active
2. WHEN Storage_Integration is not configured or inactive, THE Attachment_System SHALL fall back to local storage (current behavior)
3. WHEN uploading to R2_Storage, THE Attachment_System SHALL store the file in the configured bucket and folder path
4. WHEN a file is successfully uploaded to R2, THE Attachment_System SHALL save the Storage_Path and Storage_URL in the attachments table
5. WHEN uploading to R2_Storage fails, THE Attachment_System SHALL return an error message and not create the attachment record

### Requirement 2: Database Schema for R2 Storage

**User Story:** As a developer, I want the attachments table to support both local and R2 storage, so that the system can handle both storage methods seamlessly.

#### Acceptance Criteria

1. THE attachments table SHALL have a storage_path column for storing the R2 object key
2. THE attachments table SHALL have a storage_url column for storing the public R2 URL
3. THE attachments table SHALL have a storage_type column to distinguish between 'local' and 'r2' storage
4. WHEN an attachment uses local storage, THE storage_type SHALL be 'local' and file_path SHALL contain the local path
5. WHEN an attachment uses R2 storage, THE storage_type SHALL be 'r2' and storage_path SHALL contain the R2 object key

### Requirement 3: Attachment Download from R2

**User Story:** As a user, I want to download attachments from the attachments page, so that I can access files that were uploaded to projects.

#### Acceptance Criteria

1. WHEN a user clicks download on an attachment with storage_type 'r2', THE Attachment_System SHALL retrieve the file from R2_Storage
2. WHEN a user clicks download on an attachment with storage_type 'local', THE Attachment_System SHALL retrieve the file from local storage
3. WHEN downloading from R2_Storage, THE Attachment_System SHALL use the stored Storage_Path to locate the file
4. WHEN the file exists in R2_Storage, THE Attachment_System SHALL stream the file to the user with the correct filename and content type
5. WHEN the file does not exist in R2_Storage, THE Attachment_System SHALL return a 404 error

### Requirement 4: Attachment Deletion from R2

**User Story:** As a user, I want deleted attachments to be removed from R2 storage, so that storage space is freed and files are properly cleaned up.

#### Acceptance Criteria

1. WHEN an attachment with storage_type 'r2' is deleted, THE Attachment_System SHALL delete the file from R2_Storage
2. WHEN an attachment with storage_type 'local' is deleted, THE Attachment_System SHALL delete the file from local storage
3. WHEN deleting from R2_Storage fails, THE Attachment_System SHALL log the error but still delete the database record
4. WHEN a project is deleted, THE Attachment_System SHALL delete all associated attachments from their respective storage locations
5. THE Attachment_System SHALL handle deletion gracefully if the file no longer exists in storage

### Requirement 5: Separation from Internal Downloads

**User Story:** As a system architect, I want external attachments to be completely separate from internal downloads, so that the two systems do not interfere with each other.

#### Acceptance Criteria

1. THE External_Module attachments SHALL NOT appear in the Internal_Downloads listing
2. THE Internal_Downloads SHALL NOT appear in the External_Module attachments listing
3. THE Attachment_System SHALL use the attachments table exclusively for external attachments
4. THE Internal_Downloads SHALL use the downloads table exclusively for internal downloads
5. THE R2 upload logic for attachments SHALL be separate from the R2 upload logic for downloads

### Requirement 6: File Upload Validation

**User Story:** As a user, I want to receive clear feedback when file uploads fail, so that I can correct issues and successfully upload files.

#### Acceptance Criteria

1. WHEN a file exceeds the maximum size limit (15MB), THE Attachment_System SHALL reject the upload with a clear error message
2. WHEN a file type is not allowed, THE Attachment_System SHALL reject the upload with a clear error message
3. WHEN R2_Storage credentials are invalid, THE Attachment_System SHALL return an error and fall back to local storage
4. WHEN the R2 bucket is not accessible, THE Attachment_System SHALL return an error message indicating the storage issue
5. THE Attachment_System SHALL validate file extensions match allowed types: jpg, jpeg, png, pdf, doc, docx, xls, xlsx

### Requirement 7: Attachment Display in Attachments Page

**User Story:** As a user, I want to view all project attachments in the attachments page, so that I can manage and download files regardless of storage location.

#### Acceptance Criteria

1. THE attachments page SHALL display attachments from both local and R2 storage
2. WHEN displaying an attachment, THE Attachment_System SHALL show the file name, type, size, upload date, and uploader
3. WHEN displaying an attachment, THE Attachment_System SHALL provide download and delete actions
4. THE attachments page SHALL filter attachments by project, file type, and search query
5. THE attachments page SHALL NOT display any records from the Internal_Downloads system

### Requirement 8: Client Isolation for Attachments

**User Story:** As a client user, I want to only see attachments from my own projects, so that I cannot access other clients' files.

#### Acceptance Criteria

1. WHEN a client user views the attachments page, THE Attachment_System SHALL only show attachments from projects belonging to that client
2. WHEN a client user attempts to download an attachment from another client's project, THE Attachment_System SHALL return a 403 forbidden error
3. WHEN a client user attempts to delete an attachment from another client's project, THE Attachment_System SHALL return a 403 forbidden error
4. WHEN a staff user views the attachments page, THE Attachment_System SHALL show all attachments from all projects
5. THE client isolation SHALL apply to both R2 and local storage attachments

### Requirement 9: Storage Configuration Flexibility

**User Story:** As a system administrator, I want to configure R2 storage settings, so that I can control where and how attachments are stored.

#### Acceptance Criteria

1. WHEN R2 storage is configured and active, THE Attachment_System SHALL use R2 for new uploads
2. WHEN R2 storage is disabled, THE Attachment_System SHALL use local storage for new uploads
3. WHEN R2 configuration changes, THE Attachment_System SHALL use the new configuration for subsequent uploads
4. THE Attachment_System SHALL continue to serve existing attachments from their original storage location regardless of current configuration
5. THE Storage_Integration settings SHALL include account_id, access_key_id, secret_access_key, bucket_name, folder_path, and public_url

### Requirement 10: Error Handling and Logging

**User Story:** As a system administrator, I want detailed error logs for storage operations, so that I can troubleshoot issues with file uploads and downloads.

#### Acceptance Criteria

1. WHEN an R2 upload fails, THE Attachment_System SHALL log the error with details including file name, size, and error message
2. WHEN an R2 download fails, THE Attachment_System SHALL log the error and return a user-friendly error message
3. WHEN an R2 deletion fails, THE Attachment_System SHALL log the error but continue with database cleanup
4. WHEN R2 credentials are invalid, THE Attachment_System SHALL log the authentication error
5. THE Attachment_System SHALL log successful uploads to R2 for audit purposes
