<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;

/**
 * Reference lists in one call.
 *
 * These tables are small and change rarely, so returning them together avoids
 * four round trips on every sync run.
 */
class LookupController extends BaseApiController
{
    /** GET /api/v1/lookups */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => Category::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'code', 'fields_config', 'is_active', 'updated_at'])
                    ->map(fn ($c) => [
                        'id'            => $c->id,
                        'name'          => $c->name,
                        'code'          => $c->code,
                        // Drives assets.specs — the consumer needs it to label dynamic fields.
                        'fields_config' => $c->fields_config,
                        'is_active'     => (bool) $c->is_active,
                        'updated_at'    => $this->iso($c->updated_at),
                    ])->all(),

                'brands' => Brand::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'is_active', 'updated_at'])
                    ->map(fn ($b) => [
                        'id'         => $b->id,
                        'name'       => $b->name,
                        'is_active'  => (bool) $b->is_active,
                        'updated_at' => $this->iso($b->updated_at),
                    ])->all(),

                'locations' => Location::query()
                    ->orderBy('name')
                    ->get(['id', 'parent_id', 'name', 'type', 'is_active', 'updated_at'])
                    ->map(fn ($l) => [
                        'id'         => $l->id,
                        'parent_id'  => $l->parent_id,
                        'name'       => $l->name,
                        'type'       => $l->type,
                        'is_active'  => (bool) $l->is_active,
                        'updated_at' => $this->iso($l->updated_at),
                    ])->all(),

                'vendors' => Vendor::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'organization_type', 'phone', 'email', 'is_active', 'updated_at'])
                    ->map(fn ($v) => [
                        'id'                => $v->id,
                        'name'              => $v->name,
                        'organization_type' => $v->organization_type,
                        'phone'             => $v->phone,
                        'email'             => $v->email,
                        'is_active'         => (bool) $v->is_active,
                        'updated_at'        => $this->iso($v->updated_at),
                    ])->all(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
