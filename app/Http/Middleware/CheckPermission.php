<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $module = null, ?string $action = null): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Auto-detect module and action from route if not provided
        if (!$module || !$action) {
            [$detectedModule, $detectedAction] = $this->detectFromRoute($request);
            $module = $module ?? $detectedModule;
            $action = $action ?? $detectedAction;
        }
        
        // If we still can't determine module/action, allow the request
        if (!$module || !$action) {
            return $next($request);
        }
        
        $permission = "{$module}.{$action}";
        
        // Check if user has the specific permission
        $hasPermission = $user->hasPermission($permission);
        
        // If not, check if this is a parent module with sub-modules
        // User should have access if they have permission to ANY sub-module
        if (!$hasPermission) {
            $hasPermission = $this->checkSubModuleAccess($user, $module, $action);
        }
        
        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }
            
            // Get module label for error message
            $moduleLabels = config('permissions.modules', []);
            $moduleLabel = $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module));
            
            return redirect()->back()->with('error', "You do not have permission to access {$moduleLabel}.");
        }
        
        return $next($request);
    }
    
    /**
     * Detect module and action from route name
     */
    protected function detectFromRoute(Request $request): array
    {
        $route = $request->route();
        
        if (!$route) {
            return [null, null];
        }
        
        $routeName = $route->getName();
        
        if (!$routeName) {
            return [null, null];
        }
        
        $routeMapping = config('permissions.route_mapping', []);
        $actionMapping = config('permissions.action_mapping', []);
        $tabMapping = config('permissions.tab_mapping', []);
        
        // Sort by length descending to match more specific routes first
        uksort($routeMapping, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($routeMapping as $prefix => $module) {
            if (str_starts_with($routeName, $prefix . '.') || $routeName === $prefix) {
                // Check if this module has tab-based sub-modules
                if (isset($tabMapping[$prefix])) {
                    $tab = $request->query('tab');
                    
                    // If no tab specified, return parent module to allow checkSubModuleAccess to work
                    if (!$tab) {
                        $module = $prefix; // Use prefix as module identifier for sub-module check
                    } else {
                        $module = $tabMapping[$prefix][$tab] ?? $module;
                    }
                }
                
                // Extract action from route name
                $routeAction = $routeName === $prefix ? 'index' : str_replace($prefix . '.', '', $routeName);
                
                // Handle nested actions like 'assets.store' -> 'store'
                $actionParts = explode('.', $routeAction);
                $routeAction = end($actionParts);
                
                $action = $actionMapping[$routeAction] ?? null;
                
                return [$module, $action];
            }
        }
        
        return [null, null];
    }
    
    /**
     * Check if user has access to any sub-module of a parent module
     * This handles cases like 'helpdesk' checking 'helpdesk_tickets', 'helpdesk_templates', etc.
     * Also handles tab-based modules like 'settings.integrations' checking all integration tabs
     */
    protected function checkSubModuleAccess($user, string $module, string $action): bool
    {
        $permissions = $user->role?->permissions ?? [];
        
        if (!is_array($permissions)) {
            return false;
        }
        
        // Check for tab-based sub-modules first (e.g., settings.integrations)
        $tabMapping = config('permissions.tab_mapping', []);
        if (isset($tabMapping[$module])) {
            foreach ($tabMapping[$module] as $tab => $subModule) {
                $permission = "{$subModule}.{$action}";
                if (in_array($permission, $permissions)) {
                    return true;
                }
            }
        }
        
        // Check for any sub-module permission (e.g., helpdesk_tickets.view, helpdesk_templates.view)
        // Convert module format: settings.integrations -> settings_integrations
        $modulePrefix = str_replace('.', '_', $module);
        foreach ($permissions as $permission) {
            if (str_starts_with($permission, $modulePrefix . '_') && str_ends_with($permission, '.' . $action)) {
                return true;
            }
        }
        
        return false;
    }
}
