<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Buat role Superadmin jika belum ada
        $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);

        // Ambil semua permission yang ada
        $permissions = Permission::pluck('name')->toArray();

        // Assign semua permission ke Superadmin
        $superadmin->syncPermissions($permissions);

        // Cari user dengan ID 1
        $user = User::find(1);
        if ($user) {
            $user->assignRole('Superadmin');
        }

        $this->command->info('Superadmin diberi semua permission.');
        $this->command->info('user dengan ID 1 telah diberikan role Superadmin.');

    }
}
