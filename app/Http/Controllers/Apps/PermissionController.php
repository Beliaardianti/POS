<?php

namespace App\Http\Controllers\Apps;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Get search query
        $search = $request->get('q');

        // Get per_page parameter, default to 15
        $perPage = $request->get('per_page', 15);

        // Build base query
        $query = Permission::query();

        // Apply search filter if exists
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Order by latest
        $query->latest();

        // Handle pagination based on per_page parameter
        if ($perPage === 'all') {
            // Get all data without pagination
            $allPermissions = $query->get();

            // Create a custom pagination-like structure for frontend compatibility
            $permissions = (object) [
                'data' => $allPermissions,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $allPermissions->count(),
                'total' => $allPermissions->count(),
                'from' => $allPermissions->count() > 0 ? 1 : null,
                'to' => $allPermissions->count(),
                'links' => [] // No pagination links for 'all'
            ];
        } else {
            // Paginate data with specified per_page
            $permissions = $query->paginate((int)$perPage);

            // Append query parameters to pagination links
            $permissions->appends($request->only(['q', 'per_page']));
        }

        // Return inertia view
        return inertia('Apps/Permissions/Index', [
            'permissions' => $permissions
        ]);
    }
}
