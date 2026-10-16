<?php
namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Support\PeranUser;
use Illuminate\Console\Command;

class AkunSuperAdmin extends Command
{
    protected $signature = 'akun:superadmin {email : Email akun yang akan dijadikan Super Admin}';
    protected $description = 'Menjadikan sebuah akun sebagai Super Admin (sekaligus mengaktifkannya)';

    public function handle(): int
    {
        $user = User::where('email', mb_strtolower(trim($this->argument('email'))))->first();
        if (! $user) {
            $this->error('Akun dengan email tersebut tidak ditemukan. Daftarkan akunnya dulu di halaman /daftar.');

            return self::FAILURE;
        }

        $role = Role::firstOrCreate(['nama_role' => PeranUser::SUPER_ADMIN]);
        $user->forceFill([
            'id_role'         => $role->id_role,
            'diverifikasi_at' => $user->diverifikasi_at ?? now(),
        ])->save();

        $this->info("{$user->full_name} ({$user->email}) sekarang Super Admin.");

        return self::SUCCESS;
    }
}
