<?php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Support\StatistikPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register', ['stats' => StatistikPortal::ringkasan()]);
    }

    public function store(RegisterRequest $request)
    {
        $data = $request->validated();
        $perluVerifikasi = (bool) config('datin.verifikasi_akun');

        $user = User::create([
            'id_role'         => Role::firstOrCreate(['nama_role' => 'Pemda'])->id_role,
            'username'        => $this->buatUsername($data['email']),   // kolom wajib & unik pada ERD
            'email'           => $data['email'],
            'password_hash'   => $data['password'],                     // di-hash oleh cast 'hashed'
            'full_name'       => $data['full_name'],
            'instansi'        => $data['instansi'],
            'diverifikasi_at' => $perluVerifikasi ? null : now(),
        ]);

        if ($perluVerifikasi) {
            return redirect()->route('login')
                ->with('status', 'Pendaftaran berhasil. Akun Anda akan aktif setelah diverifikasi administrator.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /** Username dibuat otomatis dari bagian depan email, ditambah angka bila sudah dipakai. */
    private function buatUsername(string $email): string
    {
        $dasar = Str::of(Str::before($email, '@'))->lower()->replaceMatches('/[^a-z0-9._-]+/', '')->limit(40, '')->toString();
        $dasar = $dasar !== '' ? $dasar : 'pengguna';

        $username = $dasar;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $dasar . (++$i);
        }

        return $username;
    }
}
