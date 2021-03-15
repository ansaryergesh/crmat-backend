<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;

class BankController extends Controller
{   
    public function test(Request $request) {
      $token = $request->input('token');
      $result['roles'] = $this->getUserPermission($token);
      return response()->json($result);
    }

    public function add(Request $request) {
      $token = $request->input('token');
      $name = $request->input('name');
      $logo = $request->input('logo');
      $amount_min = $request->input('amount_min');
      $amount_max = $request->input('amount_max');
    }


    // Private functions
    private function getUserRole($token) {
      $user = User::where('remember_token', $token)->first();
      $role=$user->roles[0]->id;
      return $role;
    }

    private function getUserPermission($token) {
      $permissions= [];
      $user = User::where('remember_token', $token)->first();
      $permission=$user->getAllPermissions();
      foreach($permission as $p) {
        array_push($permissions,$p->pivot->permission_id);
      }
      return $permissions;
    }
}
