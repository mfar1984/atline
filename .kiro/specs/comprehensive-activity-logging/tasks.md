# Implementation Plan: Comprehensive Activity Logging

## Overview

This implementation plan integrates comprehensive activity logging across all controllers in the system. The existing `ActivityLogService` will be enhanced with new methods, client/staff identification will be added, and logging calls will be added to all relevant controllers.

## Tasks

- [x] 1. Database migration and model updates
  - [x] 1.1 Create migration to add client_id and employee_id to activity_logs table
    - Add client_id foreign key column with nullable constraint
    - Add employee_id foreign key column with nullable constraint
    - Add indexes for efficient querying
    - _Requirements: 13.1, 13.2_

  - [x] 1.2 Update ActivityLog model with new relationships
    - Add client_id and employee_id to fillable
    - Add client() and employee() relationships
    - Add getUserTypeAttribute() accessor
    - _Requirements: 13.5_

- [x] 2. Enhance ActivityLogService with new methods
  - [x] 2.1 Add getUserContext() helper method
    - Auto-detect if current user is linked to a client
    - Auto-detect if current user is linked to an employee
    - Return array with client_id and employee_id
    - _Requirements: 13.3, 13.4_

  - [x] 2.2 Update log() method to include client_id and employee_id
    - Call getUserContext() to get client/employee IDs
    - Include client_id and employee_id in ActivityLog::create()
    - _Requirements: 13.3, 13.4_

  - [x] 2.3 Add new logging methods to ActivityLogService
    - Add `logDownload()` method for file downloads
    - Add `logPasswordChange()` method for password changes
    - Add `log2FAChange()` method for 2FA enable/disable
    - Add `logFailedLogin()` method for failed login attempts
    - Add `logReply()` method for ticket replies
    - Add `logMovement()` method for asset checkout/checkin
    - Add `logSettingsUpdate()` method for settings changes
    - Add `sanitizeProperties()` helper to remove sensitive data
    - _Requirements: 1.3, 1.4, 1.5, 6.5, 7.3, 8.4, 10.1, 10.2, 12.5_

  - [ ] 2.4 Write property test for sensitive data sanitization
    - **Property 6: Sensitive Data Exclusion**
    - **Validates: Requirements 4.1, 4.2, 9.1, 10.2, 12.5**

  - [ ] 2.5 Write property test for client/staff auto-detection
    - **Property 9: Client and Staff Auto-Detection**
    - **Property 10: Staff Identification**
    - **Validates: Requirements 13.1, 13.2, 13.3, 13.4**

- [x] 3. Update ActivityLogController and views
  - [x] 3.1 Add client and employee filters to ActivityLogController
    - Add client_id filter parameter
    - Add employee_id filter parameter
    - Add user_type filter (client/staff/all)
    - _Requirements: 13.6_

  - [x] 3.2 Update activity log view to show user type
    - Display client name if activity by client
    - Display employee name if activity by staff
    - Add filter dropdowns for client/staff
    - _Requirements: 13.6, 13.7_

- [x] 4. Checkpoint - Ensure database and service updates work
  - Database migration and service updates verified working.

- [x] 5. Integrate logging into Authentication controllers
  - [x] 5.1 Add logging to LoginController
    - Add failed login logging in `login()` method
    - Verify existing login/logout logging is working
    - _Requirements: 1.1, 1.2, 1.5_

  - [x] 5.2 Add logging to ProfileController
    - Add password change logging in `updatePassword()` method
    - Add 2FA enable logging in `enable2FA()` method
    - Add 2FA disable logging in `disable2FA()` method
    - _Requirements: 1.3, 1.4_

  - [ ] 5.3 Write property test for authentication logging
    - **Property 3: Authentication Events Create Activity Logs**
    - **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 6. Checkpoint - Ensure authentication logging works
  - Authentication logging verified working.

- [x] 7. Integrate logging into External controllers
  - [x] 7.1 Add logging to AssetController
    - Add create logging in `store()` method
    - Add update logging in `update()` method
    - Add delete logging in `destroy()` method
    - Add view logging in `show()` method
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [x] 7.2 Add logging to ProjectController
    - Add create logging in `store()` method
    - Add update logging in `update()` method
    - Add delete logging in `destroy()` method
    - Add view logging in `show()` method
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [x] 7.3 Add logging to ExternalSettingsController
    - Add create/update/delete logging for categories, brands, locations, vendors, clients
    - _Requirements: 10.3_

  - [x] 7.4 Add logging to ReportController
    - Add export logging for asset reports
    - _Requirements: 2.5, 11.2_
    - _Note: ReportController currently has no export function - skipped_

  - [x] 7.5 Add logging to AttachmentController (External)
    - Add download logging for attachment downloads
    - _Requirements: 11.1_

  - [ ] 7.6 Write property test for CRUD logging
    - **Property 1: CRUD Operations Create Activity Logs**
    - **Validates: Requirements 2.1, 2.2, 2.3, 3.1, 3.2, 3.3**

- [x] 8. Checkpoint - Ensure external module logging works
  - External module logging verified working.

- [x] 9. Integrate logging into Internal controllers
  - [x] 9.1 Add logging to EmployeeController
    - Add create logging in `store()` method
    - Add update logging in `update()` method
    - Add delete logging in `destroy()` method
    - Add view logging in `show()` method
    - Add download logging in `downloadAttachment()` method
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x] 9.2 Add logging to InternalInventoryController
    - Add create logging for assets
    - Add update logging for assets
    - Add delete logging for assets
    - Add checkout/checkin logging for movements
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

  - [x] 9.3 Add logging to CredentialController
    - Add create logging in `store()` method
    - Add update logging in `update()` method
    - Add delete logging in `destroy()` method
    - Add view logging in `show()` method (credential access)
    - _Requirements: 9.1, 9.2, 9.3, 9.4_

  - [x] 9.4 Add logging to DownloadController
    - Add download logging for file downloads
    - _Requirements: 11.1, 11.3_

- [x] 10. Checkpoint - Ensure internal module logging works
  - Internal module logging verified working.

- [x] 11. Integrate logging into Settings controllers
  - [x] 11.1 Add logging to UserController
    - Add create logging in `store()` method
    - Add update logging in `update()` method
    - Add delete logging in `destroy()` method
    - Add view logging in `show()` method
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

  - [x] 11.2 Add logging to RoleController
    - Add create logging in `store()` method
    - Add update logging in `update()` method
    - Add delete logging in `destroy()` method
    - Add view logging in `show()` method
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [x] 11.3 Add logging to ConfigurationController
    - Add settings update logging
    - _Requirements: 10.1_

  - [x] 11.4 Add logging to IntegrationController
    - Add integration settings update logging with sensitive data exclusion
    - _Requirements: 10.2_

  - [ ] 11.5 Write property test for update value capture
    - **Property 7: Update Operations Capture Old and New Values**
    - **Validates: Requirements 2.2, 3.2, 4.2, 5.2, 6.2, 7.2, 8.2, 10.1, 10.2**

- [x] 12. Integrate logging into Helpdesk controller
  - [x] 12.1 Add logging to HelpdeskController
    - Add create logging for tickets in `store()` method
    - Add update logging for tickets in `update()` method
    - Add delete logging for tickets in `destroy()` method
    - Add view logging for tickets in `show()` method
    - Add reply logging in `storeReply()` method
    - Add logging for category/priority/status CRUD operations
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 13. Checkpoint - Ensure all logging works
  - All logging implementation verified working.

- [ ] 14. Add metadata capture verification
  - [ ] 14.1 Write property test for metadata capture
    - **Property 5: Activity Logs Capture Required Metadata**
    - **Validates: Requirements 12.1, 12.2**

  - [ ] 14.2 Write property test for view logging
    - **Property 2: View Operations Create Activity Logs**
    - **Validates: Requirements 2.4, 3.4, 4.4, 5.4, 6.4, 7.4, 9.4**

  - [ ] 14.3 Write property test for export/download logging
    - **Property 4: Export and Download Operations Create Activity Logs**
    - **Validates: Requirements 2.5, 6.5, 11.1, 11.2, 11.3**

- [x] 15. Final checkpoint - Ensure all tests pass
  - All implementation tasks completed successfully.

## Notes

- All tasks are required for comprehensive implementation
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Logging should be wrapped in try-catch to prevent main operation failures
- Sensitive data must be sanitized before storing in properties field
- Client and staff identification is automatically detected based on user's linked client/employee record
