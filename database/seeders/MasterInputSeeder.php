<?php
namespace Database\Seeders;

use App\Models\Moda;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterInputSeeder extends Seeder
{
    public function run(): void
    {
        // Pilihan moda sesuai desain form
        foreach (['Darat', 'Laut', 'Udara', 'Kereta Api'] as $nama) {
            Moda::firstOrCreate(['nama_moda' => $nama]);
        }

        foreach (['Super Admin', 'Moda', 'Intram'] as $nama) {
            Role::firstOrCreate(['nama_role' => $nama]);
        }
        $role = Role::firstOrCreate(['nama_role' => 'Pemda']);

        // Akun demo untuk pengembangan lokal. JANGAN dipakai di produksi.
        if (! app()->isProduction()) {
            User::firstOrCreate(
                ['username' => 'pemda'],
                [
                    'id_role'       => $role->id_role,
                    'email'         => 'pemda@example.test',
                    'full_name'     => 'User Pemda',
                    'password_hash' => 'pemda12345',   // di-hash otomatis oleh cast 'hashed'
                    'diverifikasi_at' => now(),
                ]
            );

            User::firstOrCreate(
                ['username' => 'superadmin'],
                [
                    'id_role'         => Role::where('nama_role', 'Super Admin')->value('id_role'),
                    'email'           => 'superadmin@example.test',
                    'full_name'       => 'Super Admin',
                    'password_hash'   => 'superadmin12345',
                    'diverifikasi_at' => now(),
                ]
            );
        }
    }
}
