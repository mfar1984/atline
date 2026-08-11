<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * External assets (the `assets` table) — read only.
 *
 * This is the External Asset Management inventory. It is NOT `internal_assets`,
 * which is the separate office/internal inventory and is deliberately not
 * exposed here.
 */
class AssetController extends BaseApiController
{
    /**
     * GET /api/v1/assets
     *
     * ?project_id=      ?organization_id=   ?category_id=
     * ?status=          ?serial=            ?search=
     * ?updated_since=   ?per_page=          ?page=
     */
    public function index(Request $request): JsonResponse
    {
        $query = Asset::query()
            ->with(['project:id,name,organization_id', 'category:id,name,code', 'brand:id,name', 'location:id,name', 'vendor:id,name'])
            ->select('assets.*');

        if ($request->filled('project_id')) {
            $query->where('assets.project_id', (int) $request->query('project_id'));
        }

        if ($request->filled('organization_id')) {
            $orgId = (int) $request->query('organization_id');
            $query->whereHas('project', fn ($q) => $q->where('organization_id', $orgId));
        }

        if ($request->filled('category_id')) {
            $query->where('assets.category_id', (int) $request->query('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('assets.status', $request->query('status'));
        }

        // Exact serial match — used by the consumer to resolve a scanned S/N.
        if ($request->filled('serial')) {
            $query->where('assets.serial_number', $request->query('serial'));
        }

        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s) {
                $q->where('assets.asset_tag', 'like', "%{$s}%")
                  ->orWhere('assets.serial_number', 'like', "%{$s}%")
                  ->orWhere('assets.model', 'like', "%{$s}%");
            });
        }

        $this->applySince($query, $request, 'assets');

        $page = $query->orderBy('assets.id')->paginate($this->perPage($request));

        return $this->paginated($page, fn (Asset $a) => $this->transform($a));
    }

    /** GET /api/v1/assets/{id} */
    public function show(int $id): JsonResponse
    {
        $asset = Asset::withTrashed()
            ->with(['project:id,name,organization_id', 'category:id,name,code', 'brand:id,name', 'location:id,name', 'vendor:id,name'])
            ->find($id);

        if (!$asset) {
            return $this->notFound('Asset not found.');
        }

        return $this->item($this->transform($asset));
    }

    /**
     * GET /api/v1/assets/lookup?serial=XXXX
     *
     * Convenience for the field app: resolve a serial number a technician typed
     * or scanned on site. Returns the single best match.
     */
    public function lookup(Request $request): JsonResponse
    {
        $serial = trim((string) $request->query('serial', ''));
        if ($serial === '') {
            return response()->json([
                'success' => false,
                'message' => 'Query parameter "serial" is required.',
            ], 422);
        }

        $asset = Asset::query()
            ->with(['project:id,name,organization_id', 'category:id,name,code', 'brand:id,name', 'location:id,name', 'vendor:id,name'])
            ->where('serial_number', $serial)
            ->orderByDesc('id')
            ->first();

        if (!$asset) {
            return $this->notFound('No asset with that serial number.');
        }

        return $this->item($this->transform($asset));
    }

    private function transform(Asset $a): array
    {
        return [
            'id'            => $a->id,
            'asset_tag'     => $a->asset_tag,
            'serial_number' => $a->serial_number,
            'model'         => $a->model,
            'status'        => $a->status,
            'specs'         => $a->specs,
            'assigned_to'   => $a->assigned_to,
            'department'    => $a->department,
            'notes'         => $a->notes,
            'unit_price'    => $a->unit_price !== null ? (string) $a->unit_price : null,

            'project' => $a->project ? [
                'id'              => $a->project->id,
                'name'            => $a->project->name,
                'organization_id' => $a->project->organization_id,
            ] : null,

            'category' => $a->category ? ['id' => $a->category->id, 'name' => $a->category->name, 'code' => $a->category->code] : null,
            'brand'    => $a->brand    ? ['id' => $a->brand->id,    'name' => $a->brand->name] : null,
            'location' => $a->location ? ['id' => $a->location->id, 'name' => $a->location->name] : null,
            'vendor'   => $a->vendor   ? ['id' => $a->vendor->id,   'name' => $a->vendor->name] : null,

            'warranty_expiry' => $this->date($a->warranty_expiry),

            'created_at' => $this->iso($a->created_at),
            'updated_at' => $this->iso($a->updated_at),
            // Non-null tells the consumer to tombstone its mirrored copy.
            'deleted_at' => $this->iso($a->deleted_at),
        ];
    }
}
