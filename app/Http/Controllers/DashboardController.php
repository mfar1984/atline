<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Vendor;
use App\Models\Employee;
use App\Models\InternalAsset;
use App\Models\AssetMovement;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\InternalCategory;

class DashboardController extends Controller
{
    public function index()
    {
        // External Stats
        $externalStats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'total_assets' => Asset::count(),
            'total_asset_value' => Asset::sum('unit_price'),
            'total_clients' => Client::count(),
            'total_vendors' => Vendor::count(),
        ];

        // Internal Stats
        $internalStats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'total_internal_assets' => InternalAsset::count(),
            'checked_out_assets' => InternalAsset::where('status', 'checked_out')->count(),
            'total_movements' => AssetMovement::count(),
            'pending_returns' => AssetMovement::whereNull('actual_return_date')->count(),
        ];

        // Settings Stats
        $settingsStats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
        ];

        // Projects by Status for Chart
        $projectsByStatus = [
            'active' => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'on_hold' => Project::where('status', 'on_hold')->count(),
        ];

        // Recent Projects
        $recentProjects = Project::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent Employees
        $recentEmployees = Employee::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Assets by Category (External)
        $assetsByCategory = Category::withCount('assets')
            ->having('assets_count', '>', 0)
            ->orderBy('assets_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($cat) => ['name' => $cat->name, 'count' => $cat->assets_count]);

        // Internal Assets by Category
        $internalAssetsByCategory = InternalCategory::withCount('assets')
            ->having('assets_count', '>', 0)
            ->orderBy('assets_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($cat) => ['name' => $cat->name, 'count' => $cat->assets_count]);

        // Employee Status Distribution
        $employeesByStatus = [
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'on_leave' => Employee::where('status', 'on_leave')->count(),
            'terminated' => Employee::where('status', 'terminated')->count(),
        ];

        // Recent Asset Movements
        $recentMovements = AssetMovement::with(['asset', 'employee'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'externalStats',
            'internalStats',
            'settingsStats',
            'projectsByStatus',
            'recentProjects',
            'recentEmployees',
            'assetsByCategory',
            'internalAssetsByCategory',
            'employeesByStatus',
            'recentMovements'
        ));
    }
}
