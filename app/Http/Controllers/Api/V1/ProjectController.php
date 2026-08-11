<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * External projects — read only.
 *
 * A project is the bridge between a customer (organization) and its assets,
 * so the consumer needs these to build a customer → project → asset picker.
 */
class ProjectController extends BaseApiController
{
    /**
     * GET /api/v1/projects
     *
     * ?organization_id=  ?status=  ?search=  ?updated_since=  ?per_page=  ?page=
     */
    public function index(Request $request): JsonResponse
    {
        // select() must come BEFORE withCount(): calling it after would replace
        // the select list and drop withCount's `assets_count` subquery, making
        // every count silently return 0.
        $query = Project::query()
            ->select('projects.*')
            ->with(['organization:id,name'])
            ->withCount('assets');

        if ($request->filled('organization_id')) {
            $query->where('projects.organization_id', (int) $request->query('organization_id'));
        }

        if ($request->filled('status')) {
            $query->where('projects.status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s) {
                $q->where('projects.name', 'like', "%{$s}%")
                  ->orWhere('projects.po_number', 'like', "%{$s}%");
            });
        }

        $this->applySince($query, $request, 'projects');

        $page = $query->orderBy('projects.id')->paginate($this->perPage($request));

        return $this->paginated($page, fn (Project $p) => $this->transform($p));
    }

    /** GET /api/v1/projects/{id} */
    public function show(int $id): JsonResponse
    {
        $project = Project::withTrashed()
            ->with(['organization:id,name'])
            ->withCount('assets')
            ->find($id);

        if (!$project) {
            return $this->notFound('Project not found.');
        }

        return $this->item($this->transform($project));
    }

    private function transform(Project $p): array
    {
        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description,
            'status'      => $p->status,

            'organization' => $p->organization
                ? ['id' => $p->organization->id, 'name' => $p->organization->name]
                : null,
            // Legacy free-text customer, kept so older projects still resolve.
            'client_name' => $p->client_name,

            // The number the customer knows the engagement by. Printed on every
            // preventive-maintenance service form, so the consumer mirrors it.
            'contract_reference' => $p->contract_reference,

            'start_date' => $this->date($p->start_date),
            'end_date'   => $this->date($p->end_date),

            'po_number'       => $p->po_number,
            'purchase_date'   => $this->date($p->purchase_date),
            'warranty_period' => $p->warranty_period,
            'warranty_expiry' => $this->date($p->warranty_expiry),

            'asset_count' => (int) ($p->assets_count ?? 0),

            'created_at' => $this->iso($p->created_at),
            'updated_at' => $this->iso($p->updated_at),
            'deleted_at' => $this->iso($p->deleted_at),
        ];
    }
}
