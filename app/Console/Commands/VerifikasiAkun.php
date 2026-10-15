<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifikasiAkun extends Command
{
    protected $signature = 'akun:verifikasi {email? : Email akun yang akan diverifikasi. Kosongkan untuk melihat daftar yang menunggu}';
    protected $description = 'Verifikasi akun instansi yang mendaftar, atau tampilkan daftar akun yang masih menunggu';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! $email) {
            $menunggu = User::whereNull('diverifikasi_at')->orderBy('created_at')->get();
            if ($menunggu->isEmpty()) {
                $this->info('Tidak ada akun yang menunggu verifikasi.');

                return self::SUCCESS;
            }
            $this->table(
                ['Nama', 'Instansi', 'Email', 'Mendaftar'],
                $menunggu->map(fn ($u) => [$u->full_name, $u->instansi, $u->email, $u->created_at])->all()
            );

            return self::SUCCESS;
        }

        $user = User::where('email', mb_strtolower(trim($email)))->first();
        if (! $user) {
            $this->error("Akun dengan email {$email} tidak ditemukan.");

            return self::FAILURE;
        }
        if ($user->diverifikasi_at !== null) {
            $this->info("Akun {$user->email} sudah terverifikasi.");

            return self::SUCCESS;
        }

        $user->forceFill(['diverifikasi_at' => now()])->save();
        $this->info("Akun {$user->full_name} ({$user->email}) berhasil diverifikasi.");

        return self::SUCCESS;
    }
}
