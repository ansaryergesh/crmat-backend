<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;

class BankController extends Controller
{   
    public function test(Request $request) {
      $token = $request->input('token');
      $result['roles'] = $this->getUserRole($token);
      echo $this->getUserRole($token);
      return response()->json($result);
    }

    // only Super Admin and Moderator(with permission add bank) can add banks
    public function add(Request $request) {
      $token = $request->input('token');
      $name = $request->input('name');
      $logo = $request->input('logo');
      $amount_min = $request->input('amount_min');
      $amount_max = $request->input('amount_max');
      $srok_min = $request->input('srok_min');
      $srok_max = $request->input('srok_max');
      $stavka = $request->input('stavka');
      $approve_percent = $request->input('approve_percent');
      $user_role = null;
      $user_permissions = null;
      if($this->checkUser($token)) {
        $user_role = $this->getUserRole($token);
        $user_permissions = $this->getUserPermission($token);
      }
 
      $result['success'] = false;

      do {
        if(!$token) {
          $result['message'] = 'Вы не зашли в систему';
          break;
        }
        if(!$this->checkUser($token)) {
          $result['message'] = 'Пользователь не найден!';
          break;
        }

        if($user_role!== 1 && $user_role!== 3) {
          $result['message'] = 'У вас нету доступа сделать эту действие';
          break;
        }
        if($user_role===3 && !in_array('4', $user_permissions)) {
          $result['message'] = 'У вас нету доступа сделать эту действие. Пожалуйста обращайтесь администратору!';
          break;
        }
        if(!$name) {
          $result['message'] = 'Имя не указан';
          break;
        }
        if(!$logo) {
          $result['message'] = 'Лого не указан';
          break;
        }
        if(!$amount_min) {
          $result['message'] = 'Мин. сумма не указан';
          break;
        }
        if(!$amount_max) {
          $result['message'] = 'Макс. сумма не указан';
          break;
        }
        if(!$stavka) {
          $result['message'] = 'Ставка не указан';
          break;
        }
        if(!$srok_max) {
          $result['message'] = 'Макс срок не указан';
          break;
        }
        if(!$srok_min) {
          $result['message'] = 'Мин. срок не указан';
          break;
        }
        if(!$approve_percent) {
          $result['message'] = 'Одобрение не указан';
          break;
        }

        DB::beginTransaction();
        $new_bank = DB::table('banks')->insertGetId(
            array(
              'name'=>$name,
              'amount_min'=>$amount_min,
              'amount_max'=>$amount_max,
              'srok_min'=>$srok_min,
              'srok_max'=>$srok_max,
              'logo'=>$logo,
              'stavka'=>$stavka,
              'approve_percent'=>$approve_percent,
              'created_at'=>Carbon::now(),
              'updated_at'=>Carbon::now(),
            )
        );
        if (!$new_bank){
            DB::rollback();
            $result['message'] = 'Что то произошло не так попробуйте позже';
            break;
        }
        DB::commit();
        $result['success'] = true;
        $result['message'] = 'Успешно добавлен банк';

      }while(false);
      return response()->json($result);
    }


    // Private functions
    private function checkUser($token) {
      $user = User::where('remember_token', $token)->first();
      if($user){
        return true;
      }else {
        return false;
      }
    }
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
