<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Asset;
use App\Models\IntegrationSetting;
use App\Models\Project;
use App\Services\ActivityLogService;
use App\Services\AttachmentService;
use App\Traits\ClientIsolation;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    use ClientIsolation;

    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function index(Request $request)
    {
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        
        $query = Attachment::with(['attachable', 'uploader'])
            ->orderByDesc('created_at');

        // Client isolation - only show attachments from client's projects/assets
        if ($client) {
            $clientProjectIds = Project::where('client_id', $client->id)->pluck('id')->toArray();
            $clientAssetIds = Asset::whereIn('project_id', $clientProjectIds)->pluck('id')->toArray();
            
            $query->where(function($q) use ($clientProjectIds, $clientAssetIds) {
                // Project attachments
                $q->where(function($q2) use ($clientProjectIds) {
                    $q2->where('attachable_type', 'App\Models\Project')
                       ->whereIn('attachable_id', $clientProjectIds);
                })
                // Asset attachments
                ->orWhere(function($q2) use ($clientAssetIds) {
                    $q2->where('attachable_type', 'App\Models\Asset')
                       ->whereIn('attachable_id', $clientAssetIds);
                });
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('file_name', 'like', "%{$search}%");
        }

        // Project filter
        if ($request->filled('project_id')) {
            $projectId = $request->project_id;
            // Verify client can access this project
            if ($client) {
                $project = Project::find($projectId);
                if (!$project || $project->client_id !== $client->id) {
                    $projectId = null; // Invalid project, ignore filter
                }
            }
            if ($projectId) {
                $query->where(function($q) use ($projectId) {
                    // Project attachments
                    $q->where(function($q2) use ($projectId) {
                        $q2->where('attachable_type', 'App\Models\Project')
                           ->where('attachable_id', $projectId);
                    })
                    // Asset attachments from this project
                    ->orWhere(function($q2) use ($projectId) {
                        $assetIds = Asset::where('project_id', $projectId)->pluck('id')->toArray();
                        $q2->where('attachable_type', 'App\Models\Asset')
                           ->whereIn('attachable_id', $assetIds);
                    });
                });
            }
        }

        // File type filter
        if ($request->filled('file_type')) {
            $type = $request->file_type;
            if ($type === 'image') {
                $query->whereIn('file_type', ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']);
            } elseif ($type === 'document') {
                $query->whereIn('file_type', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
            } elseif ($type === 'spreadsheet') {
                $query->whereIn('file_type', ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
            }
        }

        $attachments = $query->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
        
        // Client isolation for project dropdown
        if ($client) {
            $projects = Project::where('client_id', $client->id)->orderBy('name')->get();
        } else {
            $projects = Project::orderBy('name')->get();
        }

        return view('external.attachments.index', compact('attachments', 'projects', 'client', 'isStaff'));
    }

    public function download(Attachment $attachment)
    {
        $client = $this->getClientForUser();
        
        // Client isolation - verify attachment belongs to client's project/asset
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
        
        // Log download activity
        try {
            ActivityLogService::logDownload('external_inventory', "Downloaded attachment {$attachment->file_name}", [
                'file_name' => $attachment->file_name,
                'file_type' => $attachment->file_type,
                'file_size' => $attachment->file_size,
            ]);
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
        
        // Route to appropriate storage
        if ($attachment->isR2Storage()) {
            return $this->downloadFromR2($attachment);
        }
        
        return $this->downloadFromLocal($attachment);
    }

    /**
     * Download from local storage
     */
    protected function downloadFromLocal(Attachment $attachment)
    {
        if (!$attachment->file_path || !Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    /**
     * Download from R2 storage
     */
    protected function downloadFromR2(Attachment $attachment)
    {
        if (!$attachment->storage_path) {
            abort(404, 'File not found');
        }

        $storageSetting = IntegrationSetting::where('integration_type', IntegrationSetting::TYPE_STORAGE)
            ->where('is_active', true)
            ->first();
        
        if (!$storageSetting) {
            abort(500, 'R2 storage not configured');
        }

        try {
            $credentials = $storageSetting->getDecryptedCredentials();
            
            if (empty($credentials['account_id']) || empty($credentials['access_key_id']) || 
                empty($credentials['secret_access_key']) || empty($credentials['bucket_name'])) {
                abort(500, 'R2 storage credentials incomplete');
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
            Log::error('R2 download failed', [
                'attachment_id' => $attachment->id,
                'storage_path' => $attachment->storage_path,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Failed to generate download link');
        }
    }

    public function destroy(Attachment $attachment)
    {
        $client = $this->getClientForUser();
        
        // Client isolation - verify attachment belongs to client's project/asset
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
                abort(403, 'You do not have permission to delete this attachment.');
            }
        }
        
        $this->attachmentService->delete($attachment);

        return redirect()->route('external.attachments.index')
            ->with('success', 'Attachment deleted successfully.');
    }
}
