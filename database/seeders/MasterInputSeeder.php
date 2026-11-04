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

            // Akun demo untuk mencoba alur Moda -> INTRAM (Intram tanpa cakupan wilayah/moda)
            User::firstOrCreate(
                ['username' => 'intram'],
                [
                    'id_role'         => Role::where('nama_role', 'Intram')->value('id_role'),
                    'email'           => 'intram@example.test',
                    'full_name'       => 'User INTRAM',
                    'password_hash'   => 'intram12345',
                    'diverifikasi_at' => now(),
                ]
            );
            foreach (['Darat', 'Laut'] as $namaModa) {
                User::firstOrCreate(
                    ['username' => 'moda.' . strtolower($namaModa)],
                    [
                        'id_role'         => Role::where('nama_role', 'Moda')->value('id_role'),
                        'id_moda'         => Moda::where('nama_moda', $namaModa)->value('id_moda'),
                        'email'           => 'moda.' . strtolower($namaModa) . '@example.test',
                        'full_name'       => 'User Moda ' . $namaModa,
                        'password_hash'   => 'moda12345',
                        'diverifikasi_at' => now(),
                    ]
                );
            }
        }
    }
}
