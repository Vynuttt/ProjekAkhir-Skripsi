<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // === 1. Buat Roles ===
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $kasir = Role::firstOrCreate(['name' => 'kasir']);

        // === 2. Buat Permissions ===
        $permissions = [
            'manage products',
            'manage purchases',
            'manage sales',
            'view reports',
            'manage users',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // === 3. Assign Permissions ke Role ===
        $owner->givePermissionTo(Permission::all());
        $admin->givePermissionTo(['manage products','manage purchases','manage sales','view reports']);
        $kasir->givePermissionTo(['manage sales']);

        // === 4. Buat Users ===
        $users = [
            ['name'=>'Owner JayaMuncul','email'=>'owner@jayamuncul.test','password'=>'password','role'=>'owner'],
            ['name'=>'Admin JayaMuncul','email'=>'admin@jayamuncul.test','password'=>'password','role'=>'admin'],
            ['name'=>'Kasir JayaMuncul','email'=>'kasir@jayamuncul.test','password'=>'password','role'=>'kasir'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make($u['password']),
                    'role' => $u['role'],
                    'is_active' => true,
                ]
            );

            $user->assignRole($u['role']);
        }

        // === 5. Jalankan seeder lain jika ada ===
        $this->call([
            MasterDataSeeder::class,
            ProductsTableSeeder::class,
            TransactionsTableSeeder::class,
        ]);
    }
}
