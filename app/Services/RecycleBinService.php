<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Credential;
use App\Models\Download;
use App\Models\Employee;
use App\Models\InternalAsset;
use App\Models\Location;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecycleBinService
{
    /**
     * Recyclable models with their type keys
     */
    protected array $recyclableModels = [
        'project' => Project::class,
        'asset' => Asset::class,
        'client' => Client::class,
        'vendor' => Vendor::class,
        'category' => Category::class,
        'brand' => Brand::class,
        'location' => Location::class,
        'employee' => Employee::class,
        'internal_asset' => InternalAsset::class,
        'credential' => Credential::class,
        'download' => Download::class,
        'ticket' => Ticket::class,
    ];

    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Get all trashed items with filters
     */
    public function getAllTrashedItems(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $items = collect();

        foreach ($this->recyclableModels as $type => $modelClass) {
            $query = $modelClass::onlyTrashed()->with('deletedByUser');

            // Apply search filter
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%");
                    // Try common name fields
                    foreach (['name', 'title', 'subject', 'full_name', 'asset_tag', 'ticket_number'] as $field) {
                        if (\Schema::hasColumn($q->getModel()->getTable(), $field)) {
                            $q->orWhere($field, 'like', "%{$search}%");
                        }
                    }
                });
            }

            // Apply date filters
            if (!empty($filters['date_from'])) {
                $query->whereDate('deleted_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('deleted_at', '<=', $filters['date_to']);
            }

            // Get items and add type info
            $trashedItems = $query->get()->map(function ($item) use ($type) {
                $item->recycle_type = $type;
                return $item;
            });

            $items = $items->merge($trashedItems);
        }

        // Filter by type if specified
        if (!empty($filters['type'])) {
            $items = $items->filter(fn($item) => $item->recycle_type === $filters['type']);
        }

        // Sort by deleted_at descending
        $items = $items->sortByDesc('deleted_at')->values();

        // Manual pagination
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $items->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $paginatedItems,
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Restore a trashed item
     */
    public function restore(string $type, int $id): bool
    {
        $modelClass = $this->recyclableModels[$type] ?? null;
        if (!$modelClass) {
            return false;
        }

        $item = $modelClass::onlyTrashed()->find($id);
        if (!$item) {
            return false;
        }

        $itemName = $item->getRecycleBinName();
        $result = $item->restore();

        if ($result) {
            ActivityLogService::logRestore($item, 'recycle_bin', "Restored {$type}: {$itemName}");
        }

        return $result;
    }

    /**
     * Permanently delete a trashed item
     */
    public function forceDelete(string $type, int $id): bool
    {
        $modelClass = $this->recyclableModels[$type] ?? null;
        if (!$modelClass) {
            return false;
        }

        $item = $modelClass::onlyTrashed()->find($id);
        if (!$item) {
            return false;
        }

        $itemName = $item->getRecycleBinName();
        $itemId = $item->id;

        $result = $item->forceDelete();

        if ($result) {
            ActivityLogService::logDelete($item, 'recycle_bin', "Permanently deleted {$type}: {$itemName}");
        }

        return $result;
    }

    /**
     * Bulk delete items older than specified days
     */
    public function bulkDeleteByAge(int $days): int
    {
        $cutoffDate = now()->subDays($days);
        $deletedCount = 0;

        foreach ($this->recyclableModels as $type => $modelClass) {
            $items = $modelClass::onlyTrashed()
                ->where('deleted_at', '<', $cutoffDate)
                ->get();

            foreach ($items as $item) {
                $item->forceDelete();
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            ActivityLogService::log('bulk_delete', "Bulk deleted {$deletedCount} items older than {$days} days", 'recycle_bin');
        }

        return $deletedCount;
    }

    /**
     * Get statistics for recycle bin
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => 0,
            'by_type' => [],
        ];

        foreach ($this->recyclableModels as $type => $modelClass) {
            $count = $modelClass::onlyTrashed()->count();
            $stats['by_type'][$type] = $count;
            $stats['total'] += $count;
        }

        return $stats;
    }

    /**
     * Get available types for filter dropdown
     */
    public function getAvailableTypes(): array
    {
        return [
            'project' => 'Project',
            'asset' => 'Asset',
            'client' => 'Client',
            'vendor' => 'Vendor',
            'category' => 'Category',
            'brand' => 'Brand',
            'location' => 'Location',
            'employee' => 'Employee',
            'internal_asset' => 'Internal Asset',
            'credential' => 'Credential',
            'download' => 'Download',
            'ticket' => 'Ticket',
        ];
    }

    /**
     * Get model class by type
     */
    public function getModelClass(string $type): ?string
    {
        return $this->recyclableModels[$type] ?? null;
    }
}
