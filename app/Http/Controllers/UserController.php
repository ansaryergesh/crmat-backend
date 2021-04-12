<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('users')->get();
        return response()->json($users);
    }

    public function getProfile(Request $request)
    {
        $token = $request->input('token');
        $result['success'] = false;

        do {
            if (!$token) {
                $result['message'] = 'Не передан токен';
                break;
            }

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                $result['message'] = 'Не найден пользователь';
                break;
            }
            $result['name'] = $user->name;
            $result['email'] = $user->email;
            $result['id'] = $user->id;
            $result['roles'] = $this->getUserRole($token);
            $result['permissions'] = $this->getUserPermission($token);
            $result['success'] = true;
        } while (false);

        return response()->json($result);
    }

    public function editOwn(Request $request)
    {
        $token = $request->input('token');
        $email = $request->input('email');
        $user_name = $request->input('name');
        $user = User::where('remember_token', $token)->first();
        $user_id = $user->id;

        $result['success'] = false;

        do {
            if (!$token) {
                $result['message'] = 'Не передан токен';
                break;
            }
            if (!$email) {
                $result['message'] = 'username required';
                break;
            }
            if (!$user_name) {
                $result['message'] = 'name required';
                break;
            }

            $user = User::where('id', $user_id)->first();
            $user->email = $email;
            $user->name = $user_name;
            $user->save();
            $result['success'] = true;
            $result['message'] = 'Успешно обновлен!';
        } while (false);
        return response()->json($result);
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $result['success'] = false;

        do {
            if (!$email) {
                $result['message'] = 'Email required';
                break;
            }
            if (!$password) {
                $result['message'] = 'Password required';
                break;
            }
            $user = User::where('email', $email)->first();
            if (!$user) {
                $result['message'] = 'Такой пользователь не существует';
                break;
            }
            $psw = Hash::check($password, $user->password);
            if (!$psw) {
                $result['message'] = 'Неправильный логин или пароль';
                break;
            }
            $token = Str::random(60);
            $token = sha1($token);
            $user->remember_token = $token;
            $user->save();
            $result['success'] = true;
            $result['name'] = $user->name;
            $result['email'] = $user->email;
            $result['token'] = $token;
        } while (false);
        return response()->json($result);
    }


    public function logout(Request $request)
    {
        $email = $request->input('email');
        $result['success'] = false;

        do {
            if (!$email) {
                $result['message'] = 'Не передан эмейл';
                break;
            }
            $user = User::where('email', $email)->first();
            if (!$user) {
                $result['message'] = 'Не существует такой логин';
                break;
            }
            $user->token = '';
            $user->save;
            $result['success'] = true;
        } while (false);
        return response()->json($result);
    }

    public function changePassword(Request $request)
    {
        $password = $request->input('password');
        $token = $request->input('token');
        $result['success'] = false;

        do {
            if (!$password) {
                $result['message'] = 'Не передан пароль';
                break;
            }

            if (!$token) {
                $result['message'] = 'Не передан токен';
                break;
            }

            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                $result['message'] = 'Не найден токен';
                break;
            }
            $user->password = bcrypt($password);
            $user->save();
            $result['success'] = true;
        } while (false);

        return response()->json($result);
    }

    private function checkUser($token)
    {
        $user = User::where('remember_token', $token)->first();
        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    private function getUserRole($token)
    {
        $user = User::where('remember_token', $token)->first();
        $role = $user->roles[0]->id;
        return $role;
    }

    private function getUserPermission($token)
    {
        $permissions = [];
        $user = User::where('remember_token', $token)->first();
        $permission = $user->getAllPermissions();
        foreach ($permission as $p) {
            array_push($permissions, $p->pivot->permission_id);
        }
        return $permissions;
    }

}
