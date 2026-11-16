<?php
namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Support\PeranUser;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AkunSuperAdmin extends Command
{
    protected $signature = 'akun:superadmin {email : Email akun Super Admin}
                            {--nama= : Nama lengkap (wajib bila akun belum ada)}
                            {--password= : Kata sandi, minimal 8 karakter (wajib bila akun belum ada)}';
    protected $description = 'Menjadikan sebuah akun sebagai Super Admin; membuat akunnya bila belum ada (untuk akun pertama)';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));
        $user = User::where('email', $email)->first();

        if (! $user) {
            $nama = trim((string) $this->option('nama'));
            $sandi = (string) $this->option('password');
            if ($nama === '' || mb_strlen($sandi) < 8) {
                $this->error('Akun belum ada. Sertakan --nama="Nama Lengkap" dan --password=minimal8karakter untuk membuatnya.');

                return self::FAILURE;
            }

            $user = new User([
                'username'      => $this->buatUsername($email),
                'email'         => $email,
                'full_name'     => $nama,
                'password_hash' => $sandi,   // di-hash otomatis oleh cast 'hashed'
            ]);
        }

        $role = Role::firstOrCreate(['nama_role' => PeranUser::SUPER_ADMIN]);
        $user->id_role = $role->id_role;
        $user->diverifikasi_at = $user->diverifikasi_at ?? now();
        $user->save();

        $this->info("{$user->full_name} ({$user->email}) sekarang Super Admin.");

        return self::SUCCESS;
    }

    private function buatUsername(string $email): string
    {
        $dasar = Str::of(Str::before($email, '@'))->lower()->replaceMatches('/[^a-z0-9._-]+/', '')->limit(40, '')->toString() ?: 'admin';
        $username = $dasar;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $dasar . (++$i);
        }

        return $username;
    }
}
