<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;
use Validator;

class RoleController extends Controller
{
    // api
    public function all () {
      $roles = Role::select('id', 'name')
               ->with('users')
               ->with('permissions')
               ->get();
    	return response()->json($roles, 200);
    }

    public function store_api (Request $req) {
      $validator = Validator::make($req->all(), [
        'name' => 'required|unique:roles,name',
        'permissions' => 'required',
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors()->getMessages();
        $obj = $validator->failed();
        $result = [];
        foreach($obj as $input => $rules){
          $i = 0;
          foreach($rules as $rule => $ruleInfo){
            $key = $rule;
            $key = $input.'|'.strtolower($key);
            $result[$key] = $errors[$input][$i];
            $i++;
          }
        }
        return response()->json($result, 422);
      }

    	$role = new Role;
      $role->name = $req->name;
    	// $role->guard_name = 'api';
    	$role->guard_name = 'web'; // temporary for webportal 8.0
    	$role->save();

      //Looping thru selected permissions
      foreach ($req->permissions as $permission) {
        $p = Permission::where('id', '=', $permission)->firstOrFail();
        // $p->guard_name = 'api';
        // $p->update();
        //Fetch the newly created role and assign permission
        $role = Role::find($role->id);
        $role->givePermissionTo($p);
      }

      $role = Role::select('id', 'name')
              ->with('users')
              ->with('permissions')
              ->where('id', $role->id)
              ->first();

    	return response()->json($role, 200);
    }

    public function update_api (Request $req) {
      $validator = Validator::make($req->all(), [
        'name' => 'required|unique:roles,name,'.$req->id,
        'permissions' => 'required',
      ]);

      if ($validator->fails()) {
        $errors = $validator->errors()->getMessages();
        $obj = $validator->failed();
        $result = [];
        foreach($obj as $input => $rules){
          $i = 0;
          foreach($rules as $rule => $ruleInfo){
            $key = $rule;
            $key = $input.'|'.strtolower($key);
            $result[$key] = $errors[$input][$i];
            $i++;
          }
        }
        return response()->json($result, 422);
      }

      $role = Role::find($req->id);
      $role->name = $req->name;
      // $role->guard_name = 'api';
      $role->guard_name = 'web'; // temmporary for webportal 8.0
      $role->update();

      $p_all = Permission::all();//Get all permissions

      foreach ($p_all as $p) {
        $role->revokePermissionTo($p); //Remove all permissions associated with role
      }

      foreach ($req->permissions as $permission) {
        $p = Permission::where('id', '=', $permission)->firstOrFail(); //Get corresponding form //permission in db
        $role->givePermissionTo($p);  //Assign permission to role
      }

      $role = Role::select('id', 'name')
              ->with('users')
              ->with('permissions')
              ->where('id', $role->id)
              ->first();

      return response()->json($role, 200);
    }

    public function delete_multiple (Request $req) {
      $ids = $req;
      $role = Role::whereIn('id', $ids)->select('id', 'name')->get();
      $response = $role;
      Role::whereIn('id', $ids)->delete();

      return response()->json($response, 200);
    }
}
