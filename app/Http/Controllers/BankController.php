<?php

namespace App\Http\Controllers;
use DB;
use App\User;
use App\Http\Resources\BankDetailResource;
use App\Http\Resources\BankResource;
use App\Model\Bank;
use App\BankDetail;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;

class BankController extends Controller
{   
    public function test(Request $request) {
      $token = $request->input('token');
      $result['roles'] = $this->getUserPermission($token);
      echo $this->getUserRole($token);
      return response()->json($result);
    }

    public function bankResult(Request $request) {
      $srok = $request->input('srok');
      $id_bank = $request->input('id_bank');
      $amount = $request->input('amount');
      $rate = $request->input('rate');
      $approve = $request->input('approve');
      $sell = $request->input('sell');
      $stavka = $request->input('stavka');

      $banks = DB::table('banks')->where('active', true);
      if($srok) {
        $banks = DB::table('banks')->where('srok_min','<=', $srok)->where('srok_max', '>=', $srok);
      }
      if($amount) {
        $banks = DB::table('banks')->where('amount_min','<=', $amount)->where('amount_max', '>=', $amount);
      }

      $banks = $banks->count();
      $result['count'] = $banks;

      return response()->json($result);
    }

    public function banks(Request $request) {
      $per_page = $request->input('per_page');
      $srok = $request->input('srok');
      $id_bank = $request->input('id_bank');
      $amount = $request->input('amount');
      $sort = $request->input('sort');
      $rate = $request->input('rate');
      $approve = $request->input('approve');
      $sell = $request->input('sell');
      $stavka = $request->input('stavka');

      $banks = DB::table('banks')->orderBy('rate', 'desc');
      if($srok) {
        $banks = DB::table('banks')->where('srok_min','<=', $srok)->where('srok_max', '>=', $srok);
      }
      if($amount) {
        $banks = DB::table('banks')->where('amount_min','<=', $amount)->where('amount_max', '>=', $amount);
      }

      if($id_bank) {
        $banks = DB::table('banks')->where('id', $id_bank);
      }
      if($sort) {
        if($sort === 'rate') {
          $banks = DB::table('banks')->orderBy('rate','desc');
        }
        if($sort === 'approve') {
          $banks = DB::table('banks')->orderBy('approve_percent', 'desc');
        }
        if($sort ==='sell') {
          $banks = DB::table('banks')->orderBy('sell_quantity', 'desc');
        }
        if($sort === 'stavka') {
          $banks = DB::table('banks')->orderBy('stavka', 'asc');
        }
      }

      $banks = $banks->paginate(15);

    
      return response()->json($banks);
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
        $bank = DB::table('banks')->where('id', $id);

        if(!$bank) {
          $result['message'] = 'по этой айди не найден банк';
          break;
        }

        
        DB::table('banks')->where('id', $id)->delete();
        DB::table('bank_details')->where('bank_id', $id)->delete();

        $result['success'] = true;

    }while (false);

    return response()->json($result);
  }
    public function bank($id) {
      $result['success']  = false;
      do {
        if(!$id) {
          $result['message'] = 'Не передан айди';
          break;
        }
        $bank_detail = DB::table('banks')->where('id', $id)->get();
        $data  = BankResource::collection($bank_detail);
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
      $pension = $request->input('pension');
 
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
        if(!$name || !$logo || !$amount_max || !$amount_min || !$srok_max || !$srok_min || !$stavka || !$approve_percent) {
          $result['message']= 'Заполните все поля';
          break;
        }
        if(!$url || !$descripton || !$background_img || !$phone || !$email || !$address || !$documents || !$pension) {
          $result['message']= 'Заполните остальные все поля';
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

        $bank_detail = DB::table('bank_details')->insert(
          array(
            'bank_id'=>$new_bank,
            'description'=>$descripton,
            'background_img'=>$background_img,
            'address'=>$address,
            'email'=>$email,
            'phone'=>$phone,
            'url'=>$url,
            'documents'=>$documents,
            'pension'=>$pension,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now(),
          )
        );
        if (!$bank_detail){
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
      $pension = $request->input('pension');
 
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
        if(!$name || !$logo || !$amount_max || !$amount_min || !$srok_max || !$srok_min || !$stavka || !$approve_percent) {
          $result['message']= 'Заполните все поля';
          break;
        }
      
        if(!$descripton || !$background_img || !$phone || !$email || !$address || !$documents || !$pension) {
          $result['message']= 'Заполните остальные все поля';
          break;
        }

        DB::beginTransaction();

        $bank = DB::table('banks')->where('id', $id)->update([
          'name'=>$name,
          'amount_min'=>$amount_min,
          'amount_max'=>$amount_max,
          'srok_min'=>$srok_min,
          'srok_max'=>$srok_max,
          'logo'=>$logo,
          'stavka'=>$stavka,
          'approve_percent'=>$approve_percent,
          'updated_at'=>Carbon::now(),
        ]);

        $bank_details = DB::table('bank_details')->where('bank_id', $id)->update([
          'description'=>$descripton,
          'background_img'=>$background_img,
          'address'=>$address,
          'email'=>$email,
          'phone'=>$phone,
          'documents'=>$documents,
          'pension'=>$pension,
          'updated_at'=>Carbon::now(),
        ]);

        if(!$bank || !$bank_details) {
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
