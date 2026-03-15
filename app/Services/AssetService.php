<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetService
{
    /**
     * Generate unique 6-character Asset Tag ID (format: XXX-XXX like X78-17S)
     */
    public function generateAssetTag(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude confusing chars like 0,O,1,I
        $maxAttempts = 100;
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            // Generate format: 3 chars + hyphen + 3 chars = XXX-XXX
            $part1 = '';
            $part2 = '';
            
            for ($i = 0; $i < 3; $i++) {
                $part1 .= $characters[random_int(0, strlen($characters) - 1)];
                $part2 .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            $assetTag = $part1 . '-' . $part2;
            
            // Check if unique
            if (!Asset::where('asset_tag', $assetTag)->exists()) {
                return $assetTag;
            }
        }
        
        // Fallback: use timestamp-based unique ID
        return strtoupper(substr(md5(uniqid()), 0, 3)) . '-' . strtoupper(substr(md5(uniqid()), 0, 3));
    }

    /**
     * Generate unique Asset ID based on category code and sequence (legacy method)
     */
    public function generateAssetId(Category $category): string
    {
        $prefix = strtoupper($category->code);
        $year = date('Y');
        
        // Get the last asset number for this category and year
        $lastAsset = Asset::where('asset_tag', 'like', "{$prefix}-{$year}-%")
            ->orderBy('asset_tag', 'desc')
            ->first();
        
        if ($lastAsset) {
            // Extract the sequence number and increment
            $parts = explode('-', $lastAsset->asset_tag);
            $sequence = (int) end($parts) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }

    /**
     * Validate dynamic fields based on category configuration
     */
    public function validateDynamicFields(array $data, Category $category): array
    {
        $errors = [];
        $fields = $category->getDynamicFields();
        
        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $isRequired = $field['required'] ?? false;
            
            if ($isRequired && empty($data[$fieldName])) {
                $errors[$fieldName] = "The {$field['label']} field is required.";
            }
        }
        
        return $errors;
    }

    /**
     * Log changes to asset fields
     */
    public function logChanges(Asset $asset, array $oldValues, array $newValues): void
    {
        $fieldsToTrack = ['status', 'location_id', 'assigned_to', 'department'];
        
        foreach ($fieldsToTrack as $field) {
            $oldValue = $oldValues[$field] ?? null;
            $newValue = $newValues[$field] ?? null;
            
            if ($oldValue !== $newValue) {
                AssetLog::create([
                    'asset_id' => $asset->id,
                    'field_name' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'changed_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            }
        }
    }

    /**
     * Get assets with warranty expiring within N days
     */
    public function getAssetsExpiringWarranty(int $days = 30): Collection
    {
        return Asset::with(['project', 'category', 'brand'])
            ->whereNotNull('warranty_expiry')
            ->whereBetween('warranty_expiry', [now(), now()->addDays($days)])
            ->orderBy('warranty_expiry')
            ->get();
    }

    /**
     * Get asset statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => Asset::count(),
            'by_status' => Asset::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_category' => Asset::select('category_id', DB::raw('count(*) as count'))
                ->groupBy('category_id')
                ->with('category:id,name')
                ->get()
                ->mapWithKeys(fn($item) => [$item->category->name ?? 'Unknown' => $item->count])
                ->toArray(),
            'total_value' => Asset::sum('unit_price'),
            'expiring_warranty' => Asset::warrantyExpiring(30)->count(),
        ];
    }
}
