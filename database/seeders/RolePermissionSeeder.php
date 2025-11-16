<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);

        $permissions = [
            'manage translations',
            'manage languages',
            'access admin panel',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm], ['guard_name' => 'web']);
        }

        $admin->givePermissionTo($permissions);

        $user = User::first();
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
