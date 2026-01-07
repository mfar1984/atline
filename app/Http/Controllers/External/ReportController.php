<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Client;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Location;
use App\Models\Vendor;
use App\Traits\ClientIsolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ClientIsolation;

    public function index()
    {
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        $isClient = !$isStaff;
        
        // Build queries with client isolation
        $projectQuery = Project::query();
        $assetQuery = Asset::query();
        
        if ($client) {
            $projectQuery->where('client_id', $client->id);
            $assetQuery->whereHas('project', function($q) use ($client) {
                $q->where('client_id', $client->id);
            });
        }

        // Summary Statistics
        $stats = [
            'total_projects' => (clone $projectQuery)->count(),
            'active_projects' => (clone $projectQuery)->where('status', 'active')->count(),
            'completed_projects' => (clone $projectQuery)->where('status', 'completed')->count(),
            'total_assets' => (clone $assetQuery)->count(),
            'total_value' => (clone $projectQuery)->sum('project_value'),
            'total_clients' => $client ? 1 : Client::count(),
        ];

        // Projects by Status (Pie Chart)
        $projectsByStatus = (clone $projectQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Assets by Category (Doughnut Chart)
        $assetsByCategoryQuery = clone $assetQuery;
        $assetsByCategory = $assetsByCategoryQuery
            ->select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category?->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        // Assets by Brand (Bar Chart)
        $assetsByBrandQuery = clone $assetQuery;
        $assetsByBrand = $assetsByBrandQuery
            ->select('brand_id', DB::raw('count(*) as count'))
            ->groupBy('brand_id')
            ->with('brand')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->brand?->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        // Project Value by Client (Horizontal Bar) - only for staff
        if ($client) {
            $projectValueByClient = collect([[
                'name' => $client->name,
                'value' => (float) (clone $projectQuery)->sum('project_value'),
            ]]);
        } else {
            $projectValueByClient = Project::select('client_id', DB::raw('SUM(project_value) as total_value'))
                ->groupBy('client_id')
                ->with('client')
                ->orderByDesc('total_value')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->client?->name ?? 'Unknown',
                        'value' => (float) $item->total_value,
                    ];
                });
        }

        // Assets by Location (Polar Area)
        $assetsByLocationQuery = clone $assetQuery;
        $assetsByLocation = $assetsByLocationQuery
            ->select('location_id', DB::raw('count(*) as count'))
            ->groupBy('location_id')
            ->with('location')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->location?->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        // Monthly Project Trend (Line Chart) - Last 12 months
        $monthlyProjects = (clone $projectQuery)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('count(*) as count'),
                DB::raw('SUM(project_value) as total_value')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Assets by Status (Pie Chart)
        $assetsByStatusQuery = clone $assetQuery;
        $assetsByStatus = $assetsByStatusQuery
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Top Vendors by Asset Count
        $topVendorsQuery = clone $assetQuery;
        $topVendors = $topVendorsQuery
            ->select('vendor_id', DB::raw('count(*) as count'))
            ->whereNotNull('vendor_id')
            ->groupBy('vendor_id')
            ->with('vendor')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->vendor?->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        // Asset Value Distribution by Category
        $assetValueByCategoryQuery = clone $assetQuery;
        $assetValueByCategory = $assetValueByCategoryQuery
            ->select('category_id', DB::raw('SUM(unit_price) as total_value'))
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total_value')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category?->name ?? 'Unknown',
                    'value' => (float) $item->total_value,
                ];
            });

        // Top Projects by Value
        $topProjects = (clone $projectQuery)
            ->with('client')
            ->orderByDesc('project_value')
            ->limit(5)
            ->get();

        // Recent Assets
        $recentAssets = (clone $assetQuery)
            ->with(['category', 'brand'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('external.reports.index', compact(
            'stats',
            'projectsByStatus',
            'assetsByCategory',
            'assetsByBrand',
            'projectValueByClient',
            'assetsByLocation',
            'monthlyProjects',
            'assetsByStatus',
            'topVendors',
            'assetValueByCategory',
            'topProjects',
            'recentAssets',
            'client',
            'isStaff',
            'isClient'
        ));
    }
}
