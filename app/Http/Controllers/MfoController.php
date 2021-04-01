<?php

namespace App\Http\Controllers;

use App\Mfo;
use DB;
use App\User;
use App\Http\Resources\MfoDetailResource;
use App\Http\Resources\MfoResource;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;;
use Illuminate\Http\Request;

class MfoController extends Controller
{
      public function index(Request $request) {
        $per_page = $request->input('per_page');
        $srok = $request->input('srok');
        $mfo_id = $request->input('mfo_id');
        $amount = $request->input('amount');
        $sort = $request->input('sort');
        $rate = $request->input('rate');
        $approve = $request->input('approve');
        $sell = $request->input('sell');
        $stavka = $request->input('stavka');
        $archive = $request->input('archive');
        if($archive===true) {
            $mfos = DB::table('mfos')->where('active', false);
        }else {
          $mfos = DB::table('mfos')->where('active', true);
        }
        if($srok) {
          $mfos = DB::table('mfos')->where('srok_min','<=', $srok)->where('srok_max', '>=', $srok);
        }
        if($amount) {
          $mfos = DB::table('mfos')->where('amount_min','<=', $amount)->where('amount_max', '>=', $amount);
        }
  
        if($mfo_id) {
          $mfos = DB::table('mfos')->where('id', $mfo_id);
        }
        if($sort) {
          if($sort === 'rate') {
            $mfos = DB::table('mfos')->orderBy('rate','desc');
          }
          if($sort === 'approve') {
            $mfos = DB::table('mfos')->orderBy('approve_percent', 'desc');
          }
          if($sort ==='sell') {
            $mfos = DB::table('mfos')->orderBy('sell_quantity', 'desc');
          }
          if($sort === 'stavka') {
            $mfos = DB::table('mfos')->orderBy('stavka', 'asc');
          }
        }
  
        $mfos = $mfos->paginate(15);
      
        return response()->json($mfos);
      }

      public function archive(Request $request) {
          $token = $request->input('token');
          $id = $request->input('id');
          $user_role = null;
          $user_permissions = null;

          if($this->checkUser($token)) {
            $user_role = $this->getUserRole($token);
            $user_permissions = $this->getUserPermission($token);
          }
    
          $result['success'] = false;
          do {
            if(!$token) {
              $result['message'] = 'ключ нету';
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
            $mfo = DB::table('mfos')->where('id', $id);
            if(!$mfo) {
              $result['message'] = 'по этой айди не найден данные';
              break;
            }
            
            // Active or not
            $status = DB::table('mfos')->where('id', $id)->select('active')->get();
            $status = $status[0]->active;
            if($status === 0) {
                $status = true;
            }else {
                $status = false;
            };

            // Active or not end

            $mfo = DB::table('mfos')->where('id', $id)->update([
                'updated_at' => Carbon::now(),
                'active' => $status,
            ]);

            if(!$mfo) {
                DB::rollBack();
                $result['message'] = 'Что то пошло не так';
              };
              DB::commit();
              $result['success'] = true;
              $result['message'] = 'статус МФО изменен в архив';
    
            $result['success'] = true;
    
        }while (false);
    
        return response()->json($result);
      }
  
  
  
      public function delete($id, Request $request) {
        $token = $request->input('token');
        $user_role = null;
        $user_permissions = null;
        if($this->checkUser($token)) {
          $user_role = $this->getUserRole($token);
          $user_permissions = $this->getUserPermission($token);
        }
  
        $result['success'] = false;
        do {
          if(!$token) {
            $result['message'] = 'ключ нету';
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
          $mfo = DB::table('mfos')->where('id', $id);
          if(!$mfo) {
            $result['message'] = 'по этой айди не найден данные';
            break;
          }
  
          
          DB::table('mfos')->where('id', $id)->delete();
          DB::table('mfo_details')->where('mfo_id', $id)->delete();
  
          $result['success'] = true;
  
      }while (false);
  
      return response()->json($result);
    }
      public function mfo($id) {
        $result['success']  = false;
        do {
          if(!$id) {
            $result['message'] = 'Не передан айди';
            break;
          }
          $mfo_details = DB::table('mfos')->where('id', $id)->get();
          $data  = MfoResource::collection($mfo_details);
          $result['data'] = $data;
          $result['success'] = true;
        } while (false);
        return response()->json($result);
      }
      // only Super Admin and Moderator(with permission add bank) can add banks
      
      public function add(Request $request) {
        $token = $request->input('token');
        $name = $request->input('name');
        $logo = $request->input('logo');
        $url = $request->input('url');
        $amount_min = $request->input('amount_min');
        $amount_max = $request->input('amount_max');
        $srok_min = $request->input('srok_min');
        $srok_max = $request->input('srok_max');
        $stavka = $request->input('stavka');
        $approve_percent = $request->input('approve_percent');
        $review_time = $request->input('review_time');
        $user_role = null;
        $user_permissions = null;
        if($this->checkUser($token)) {
          $user_role = $this->getUserRole($token);
          $user_permissions = $this->getUserPermission($token);
        }
        
        $descripton = $request->input('description');
        $background_img = $request->input('background_img');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $address = $request->input('address');
        $documents = $request->input('documents');
   
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
          if(!$name || !$logo || !$amount_max || !$amount_min || !$srok_max || !$srok_min || !$stavka || !$approve_percent || !$review_time) {
            $result['message']= 'Заполните все поля';
            break;
          }
          if(!$url || !$descripton || !$background_img || !$phone || !$email || !$address || !$documents ) {
            $result['message']= 'Заполните остальные все поля';
            break;
          }
  
          DB::beginTransaction();
          $new_mfo = DB::table('mfos')->insertGetId(
              array(
                'name'=>$name,
                'amount_min'=>$amount_min,
                'amount_max'=>$amount_max,
                'srok_min'=>$srok_min,
                'srok_max'=>$srok_max,
                'logo'=>$logo,
                'stavka'=>$stavka,
                'approve_percent'=>$approve_percent,
                'review_time'=>$review_time,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
              )
          );
          if (!$new_mfo){
              DB::rollback();
              $result['message'] = 'Что то произошло не так попробуйте позже';
              break;
          }
  
          $mfo_detail = DB::table('mfo_details')->insert(
            array(
              'mfo_id'=>$new_mfo,
              'description'=>$descripton,
              'background_img'=>$background_img,
              'address'=>$address,
              'email'=>$email,
              'phone'=>$phone,
              'url'=>$url,
              'documents'=>$documents,
              'created_at'=>Carbon::now(),
              'updated_at'=>Carbon::now(),
            )
          );
          if (!$mfo_detail){
            DB::rollback();
            $result['message'] = 'Что то произошло не так попробуйте позже';
            break;
        }
          DB::commit();
          $result['success'] = true;
          $result['message'] = 'Успешно добавлен МФО';
  
        }while(false);
        return response()->json($result);
      }
  
      public function edit($id, Request $request) {
        $result['success'] = false;
        $token = $request->input('token');
        $name = $request->input('name');
        $logo = $request->input('logo');
        $amount_min = $request->input('amount_min');
        $amount_max = $request->input('amount_max');
        $srok_min = $request->input('srok_min');
        $srok_max = $request->input('srok_max');
        $stavka = $request->input('stavka');
        $approve_percent = $request->input('approve_percent');
        $review_time = $request->input('revi$review_time');
        $user_role = null;
        $user_permissions = null;
        if($this->checkUser($token)) {
          $user_role = $this->getUserRole($token);
          $user_permissions = $this->getUserPermission($token);
        }
        
        $descripton = $request->input('description');
        $background_img = $request->input('background_img');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $address = $request->input('address');
        $documents = $request->input('documents');
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
          if(!$name || !$logo || !$amount_max || !$amount_min || !$srok_max || !$srok_min || !$stavka || !$approve_percent || !$review_time) {
            $result['message']= 'Заполните все поля';
            break;
          }
        
          if(!$descripton || !$background_img || !$phone || !$email || !$address || !$documents ) {
            $result['message']= 'Заполните остальные все поля';
            break;
          }
  
          DB::beginTransaction();
  
          $mfo = DB::table('mfos')->where('id', $id)->update([
            'name'=>$name,
            'amount_min'=>$amount_min,
            'amount_max'=>$amount_max,
            'srok_min'=>$srok_min,
            'srok_max'=>$srok_max,
            'logo'=>$logo,
            'stavka'=>$stavka,
            'review_time'=>$review_time,
            'approve_percent'=>$approve_percent,
            'updated_at'=>Carbon::now(),
          ]);
  
          $mfo_details = DB::table('mfo_details')->where('mfo_id', $id)->update([
            'description'=>$descripton,
            'background_img'=>$background_img,
            'address'=>$address,
            'email'=>$email,
            'phone'=>$phone,
            'documents'=>$documents,
            'updated_at'=>Carbon::now(),
          ]);
  
          if(!$mfo || !$mfo_details) {
            DB::rollBack();
            $result['message'] = 'Что то пошло не так';
          };
          DB::commit();
          $result['success'] = true;
          $result['message'] = 'Успешно обновлен банк';
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
  
      private function returnMessage($var) {
  
        return $var + ' не указан';
      }
}
