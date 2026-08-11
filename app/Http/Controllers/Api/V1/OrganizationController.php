<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Organizations (customers) — read only.
 *
 * `organizations` is the current customer table; `projects.organization_id`
 * points here. The older `clients` table is intentionally not exposed — new
 * consumers should standardise on organizations.
 */
class OrganizationController extends BaseApiController
{
    /**
     * GET /api/v1/organizations
     *
     * ?search=  ?active=1  ?updated_since=  ?per_page=  ?page=
     */
    public function index(Request $request): JsonResponse
    {
        // select() must come BEFORE withCount(), otherwise it replaces the
        // select list and withCount's `projects_count` subquery is dropped,
        // silently returning 0 for every row.
        $query = Organization::query()
            ->select('organizations.*')
            ->withCount('projects');

        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s) {
                $q->where('organizations.name', 'like', "%{$s}%")
                  ->orWhere('organizations.contact_person', 'like', "%{$s}%");
            });
        }

        if ($request->boolean('active')) {
            $query->where('organizations.is_active', true);
        }

        $this->applySince($query, $request, 'organizations');

        $page = $query->orderBy('organizations.name')->paginate($this->perPage($request));

        return $this->paginated($page, fn (Organization $o) => $this->transform($o));
    }

    /** GET /api/v1/organizations/{id} */
    public function show(int $id): JsonResponse
    {
        $org = Organization::withTrashed()->withCount('projects')->find($id);

        if (!$org) {
            return $this->notFound('Organization not found.');
        }

        return $this->item($this->transform($org));
    }

    private function transform(Organization $o): array
    {
        return [
            'id'                => $o->id,
            'name'              => $o->name,
            'organization_type' => $o->organization_type,

            'address_1' => $o->address_1,
            'address_2' => $o->address_2,
            'postcode'  => $o->postcode,
            'district'  => $o->district,
            'state'     => $o->state,
            'country'   => $o->country,

            'phone'          => $o->phone,
            'email'          => $o->email,
            'website'        => $o->website,
            'contact_person' => $o->contact_person,

            'is_active'     => (bool) $o->is_active,
            'project_count' => (int) ($o->projects_count ?? 0),

            'created_at' => $this->iso($o->created_at),
            'updated_at' => $this->iso($o->updated_at),
            'deleted_at' => $this->iso($o->deleted_at),
        ];
    }
}
