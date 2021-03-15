<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\User;
class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      app()[PermissionRegistrar::class]->forgetCachedPermissions();
      
      Permission::create(['name' => 'edit users']);
      Permission::create(['name' => 'delete users']);
      Permission::create(['name' => 'add users']);

      Permission::create(['name' => 'edit news']);
      Permission::create(['name' => 'delete news']);
      Permission::create(['name' => 'publish news']);

      Permission::create(['name' => 'add banks']);
      Permission::create(['name' => 'delete banks']);
      Permission::create(['name' => 'edit banks']);

    
      $role1 = Role::create(['name' => 'super-admin']);
      $role2 = Role::create(['name' => 'admin']);
      $role3 = Role::create(['name' => 'moderator']);
      $role4 = Role::create(['name' => 'marketolog']);
      $role5 = Role::create(['name' => 'guest']);

      $role2->givePermissionTo('edit users');
      $role2->givePermissionTo('add users');
      $role2->givePermissionTo('delete users');

      $role3->givePermissionTo('edit news');
      $role3->givePermissionTo('publish news');
      $role3->givePermissionTo('delete news');

      $user = Factory(User::class)->create([
        'name' => 'Admin User',
        'email' => 'admin@admin.com',
      ]);
      $user->assignRole($role1);

      $user = Factory(User::class)->create([
        'name' => 'Super Admin',
        'email' => 'moderator@mod.com',
      ]);
      $user->assignRole($role4);

      $user = Factory(User::class)->create([
        'name' => 'Moderator Admin',
        'email' => 'super@super.com',
      ]);
      $user->assignRole($role2);
    }
}
