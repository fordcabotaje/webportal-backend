<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BranchClearance
{
    
    public function handle($request, Closure $next) {
      // Show :: Show Branches permission doesn't affect showing lists
      if ($request->route()->getName() === 'branches') {
        if (Auth::user()->hasPermissionTo('Show Branches')) {
          return $next($request);
        } else {
          if (Auth::user()->hasAnyPermission('Create Branches',
                                            'Edit Branches',
                                            'Delete Branches',
                                            'Show Users',
                                            'Create Users',
                                            'Edit Users',
                                            'Delete Users',
                                            'Show Power Interruptions',
                                            'Create Power Interruptions',
                                            'Edit Power Interruptions',
                                            'Delete Power Interruptions',
                                            'Show User Employments',
                                            'Create User Employments',
                                            'Edit User Employments',
                                            'Delete User Employments',
                                            'Show Computerware Tickets',
                                            'Create Computerware Tickets',
                                            'Edit Computerware Tickets',
                                            'Delete Computerware Tickets')) {
            return $next($request);
          } else {
            return response()->json('Forbidden', 403);
          }
        }
      }

      // Create
      if ($request->route()->getName() === 'branch.store') {
        if (!Auth::user()->hasPermissionTo('Create Branches')) {
          return response()->json('Forbidden', 418);
        } else {
          return $next($request);
        }
      }

      // Edit/Update
      if ($request->route()->getName() === 'branch.update') {
        if (!Auth::user()->hasPermissionTo('Edit Branches')) {
          return response()->json('Forbidden', 418);
        } else {
          return $next($request);
        }
      }

      // Delete
      if ($request->route()->getName() === 'branch.delete') {
        if (!Auth::user()->hasPermissionTo('Delete Branches')) {
          return response()->json('Forbidden', 418);
        } else {
          return $next($request);
        }
      }

      return $next($request);
    }
}
