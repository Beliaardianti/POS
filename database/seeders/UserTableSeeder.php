<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Membuat peran admin, owner, dan gudang jika belum ada
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $gudangRole = Role::firstOrCreate(['name' => 'gudang']);

        // Membuat user admin
        $admin = User::create([
            'name' => 'Ganot Suyoto',
            'email' => 'admin@gmail.com',
            'password' => 'password', // Hash password
        ]);

        // Menetapkan peran admin ke user admin
        $admin->assignRole($adminRole);

        // Membuat user owner
        $owner = User::create([
            'name' => 'Surtiyah',
            'email' => 'owner@gmail.com',
            'password' => 'password',
        ]);

        // Menetapkan peran owner ke user owner
        $owner->assignRole($ownerRole);

        // Membuat user gudang
        $gudang = User::create([
            'name' => 'Lia',
            'email' => 'gudang@gmail.com',
            'password' => 'password',
        ]);

        // Menetapkan peran gudang ke user gudang
        $gudang->assignRole($gudangRole);
    }
}
