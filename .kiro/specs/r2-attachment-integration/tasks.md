# Implementation Plan: R2 Storage Integration for Project Attachments

## Overview

This implementation plan integrates Cloudflare R2 storage with the external project attachment system, following the same pattern as the internal downloads R2 integration while maintaining complete separation between the two systems.

## Tasks

- [x] 1. Create database migration for R2 storage fields
  - Add `storage_type`, `storage_path`, and `storage_url` columns to attachments table
  - Set default value for `storage_type` to 'local' for backward compatibility
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [-] 2. Update Attachment model with R2 support
  - [x] 2.1 Add new fields to fillable array
    - Add `storage_type`, `storage_path`, `storage_url` to fillable
    - _Requirements: 2.1, 2.2, 2.3_
  
  - [x] 2.2 Add storage type constants and helper methods
    - Add `STORAGE_LOCAL` and `STORAGE_R2` constants
    - Implement `isR2Storage()`, `isLocalStorage()`, and `getStorageLocationAttribute()` methods
    - _Requirements: 2.4, 2.5_
  
  - [ ] 2.3 Write property test for storage type consistency
    - **Property 1: Storage Type Consistency**
    - **Validates: Requirements 2.4, 2.5**

- [-] 3. Enhance AttachmentService with R2 upload support
  - [x] 3.1 Update allowed file extensions and max file size
    - Add 'xls' and 'xlsx' to allowed extensions
    - Update max file size to 15MB (15728640 bytes)
    - _Requirements: 6.5, 6.1_
  
  - [x] 3.2 Implement R2 configuration check in store() method
    - Check if R2 storage integration is configured and active
    - Route to `storeToR2()` if configured, otherwise `storeToLocal()`
    - _Requirements: 1.1, 1.2, 9.1, 9.2_
  
  - [x] 3.3 Implement storeToR2() method
    - Get R2 credentials from IntegrationSetting
    - Validate credentials completeness
    - Create S3 client with R2 endpoint
    - Generate unique object path with folder structure
    - Upload file to R2 bucket
    - Build public URL if configured
    - Create attachment record with R2 storage details
    - Log successful upload
    - _Requirements: 1.1, 1.3, 1.4, 9.5, 10.5_
  
  - [x] 3.4 Update storeToLocal() method
    - Ensure it creates attachment with `storage_type='local'`
    - Set `storage_path` and `storage_url` to null
    - _Requirements: 1.2, 2.4_
  
  - [x] 3.5 Add error handling for R2 upload failures
    - Catch R2 upload exceptions
    - Log error with file details
    - Throw exception with user-friendly message
    - Do not create attachment record on failure
    - _Requirements: 1.5, 6.3, 6.4, 10.1, 10.4_
  
  - [ ] 3.6 Write property test for R2 upload success
    - **Property 2: R2 Upload Success Implies Record Creation**
    - **Validates: Requirements 1.1, 1.4**
  
  - [ ] 3.7 Write property test for local fallback
    - **Property 3: Local Fallback on R2 Unavailable**
    - **Validates: Requirements 1.2**
  
  - [ ] 3.8 Write property test for file validation
    - **Property 8: File Validation Enforcement**
    - **Validates: Requirements 6.1, 6.2, 6.5**

- [-] 4. Enhance AttachmentService with R2 deletion support
  - [x] 4.1 Update delete() method to route by storage type
    - Check `storage_type` field
    - Call `deleteFromR2()` for R2 attachments
    - Call `deleteFromLocal()` for local attachments
    - Delete database record after file deletion
    - _Requirements: 4.1, 4.2_
  
  - [x] 4.2 Implement deleteFromR2() method
    - Check if storage_path exists
    - Get R2 credentials from IntegrationSetting
    - Create S3 client
    - Delete object from R2 bucket
    - Log successful deletion
    - Handle errors gracefully (log but don't throw)
    - _Requirements: 4.1, 4.3, 4.5, 10.3_
  
  - [x] 4.3 Update deleteFromLocal() method
    - Ensure it handles missing files gracefully
    - _Requirements: 4.2, 4.5_
  
  - [ ] 4.4 Write property test for deletion completeness
    - **Property 5: Deletion Completeness**
    - **Validates: Requirements 4.1, 4.2**

- [ ] 5. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [-] 6. Enhance AttachmentController with R2 download support
  - [x] 6.1 Update download() method to route by storage type
    - Keep existing client isolation checks
    - Check `storage_type` field after authorization
    - Call `downloadFromR2()` for R2 attachments
    - Call `downloadFromLocal()` for local attachments
    - _Requirements: 3.1, 3.2, 8.2_
  
  - [x] 6.2 Implement downloadFromR2() method
    - Validate storage_path exists
    - Get R2 credentials from IntegrationSetting
    - Create S3 client with R2 endpoint
    - Generate presigned URL with 1-hour expiration
    - Set Content-Disposition header with original filename
    - Redirect to presigned URL
    - Log errors and return 500 on failure
    - _Requirements: 3.1, 3.3, 3.4, 10.2_
  
  - [x] 6.3 Extract downloadFromLocal() method
    - Move existing local download logic to separate method
    - Check file exists in public storage
    - Return 404 if not found
    - Stream file download with original filename
    - _Requirements: 3.2, 3.5_
  
  - [ ] 6.4 Write property test for download routing
    - **Property 4: Download Routing Correctness**
    - **Validates: Requirements 3.1, 3.2**
  
  - [ ] 6.5 Write property test for client isolation
    - **Property 6: Client Isolation for All Operations**
    - **Validates: Requirements 8.1, 8.2, 8.3**

- [ ] 7. Update AttachmentController index() to verify separation
  - [x] 7.1 Review query to ensure it only fetches from attachments table
    - Verify no joins or references to downloads table
    - Ensure query uses `Attachment::with()` exclusively
    - _Requirements: 5.1, 5.3, 7.5_
  
  - [ ] 7.2 Write property test for attachment-download separation
    - **Property 7: Attachment-Download Separation**
    - **Validates: Requirements 5.1, 5.3, 7.5**

- [-] 8. Update views to display storage location
  - [x] 8.1 Update external/attachments/index.blade.php
    - Add storage location badge/indicator in attachment list
    - Show "Cloud Storage" or "Local Storage" based on storage_type
    - _Requirements: 7.1, 7.2_
  
  - [x] 8.2 Update external/projects/show.blade.php
    - Add storage location indicator for project attachments
    - _Requirements: 7.1, 7.2_

- [x] 9. Add AWS SDK import to AttachmentService
  - Add `use Aws\S3\S3Client;` import
  - Add `use Illuminate\Support\Str;` import
  - Add `use App\Models\IntegrationSetting;` import
  - _Requirements: 1.1, 1.3_

- [ ] 10. Update project deletion to handle R2 attachments
  - [x] 10.1 Verify ProjectController destroy() method
    - Ensure it calls `attachmentService->delete()` for each attachment
    - Verify it works for both local and R2 attachments
    - _Requirements: 4.4_
  
  - [ ] 10.2 Write integration test for project deletion with mixed attachments
    - Test project with both local and R2 attachments
    - Verify all attachments are deleted from respective storage
    - _Requirements: 4.4_

- [ ] 11. Add error handling and logging
  - [ ] 11.1 Add try-catch blocks in AttachmentService R2 methods
    - Wrap R2 operations in try-catch
    - Log errors with context (file name, size, operation)
    - Return user-friendly error messages
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  
  - [ ] 11.2 Write unit tests for error scenarios
    - Test invalid R2 credentials
    - Test inaccessible R2 bucket
    - Test file size exceeded
    - Test invalid file type
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [ ] 12. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 13. Manual testing and verification
  - [ ] 13.1 Test R2 upload flow
    - Configure R2 in Settings > Integrations > Storage
    - Upload attachment to project
    - Verify file stored in R2
    - Verify database record has correct storage_type and storage_path
    - _Requirements: 1.1, 1.3, 1.4_
  
  - [ ] 13.2 Test local fallback
    - Disable R2 storage
    - Upload attachment to project
    - Verify file stored locally
    - Verify database record has storage_type='local'
    - _Requirements: 1.2_
  
  - [ ] 13.3 Test download from both storage types
    - Download R2 attachment (verify presigned URL redirect)
    - Download local attachment (verify direct download)
    - _Requirements: 3.1, 3.2, 3.4_
  
  - [ ] 13.4 Test deletion from both storage types
    - Delete R2 attachment (verify removed from R2 and database)
    - Delete local attachment (verify removed from local storage and database)
    - _Requirements: 4.1, 4.2_
  
  - [ ] 13.5 Test client isolation
    - Login as client user
    - Verify can only see own project attachments
    - Verify cannot download other client's attachments (403 error)
    - Verify cannot delete other client's attachments (403 error)
    - _Requirements: 8.1, 8.2, 8.3_
  
  - [ ] 13.6 Verify attachment-download separation
    - Check /external/attachments page shows only attachments
    - Check /internal/download page shows only downloads
    - Verify no cross-contamination
    - _Requirements: 5.1, 5.2, 5.3, 7.5_
  
  - [ ] 13.7 Test configuration flexibility
    - Upload attachment with R2 enabled (verify R2 storage)
    - Disable R2
    - Verify can still download previously uploaded R2 attachment
    - Upload new attachment (verify local storage)
    - Re-enable R2
    - Verify can download both R2 and local attachments
    - _Requirements: 9.1, 9.2, 9.3, 9.4_

## Notes

- All tasks are required for comprehensive implementation
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- The implementation follows the same pattern as internal downloads R2 integration
- Complete separation is maintained between attachments and downloads systems
- Backward compatibility is preserved for existing local attachments
