<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Moda;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use App\Support\Paginasi;
use App\Support\PeranUser;
use App\Support\XlsxExporter;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private const PER_HALAMAN = 8;
    private const URUTAN_KARTU = [PeranUser::PEMDA, PeranUser::MODA, PeranUser::INTRAM];

    public function index(Request $request)
    {
        $filter = $this->filter($request);
        $users = $this->query($filter)->paginate(self::PER_HALAMAN)->withQueryString();

        $kartu = Role::with('hakAkses')->whereIn('nama_role', self::URUTAN_KARTU)->get()
            ->sortBy(fn ($r) => array_search($r->nama_role, self::URUTAN_KARTU, true))->values();

        return view('admin.users.index', [
            'users'     => $users,
            'filter'    => $filter,
            'roles'     => Role::orderBy('id_role')->get(),
            'halaman'   => Paginasi::nomor($users->currentPage(), $users->lastPage()),
            'adaFilter' => $filter['role'] !== null || $filter['q'] !== '',
            'kartu'     => $kartu,
            'cakupan'   => PeranUser::CAKUPAN,
            'sayaId'    => $request->user()->id_user,
        ]);
    }

    public function create()
    {
        $user = new User(['diverifikasi_at' => now()]);

        return view('admin.users.form', $this->dataForm($user, 'create'));
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $user = User::create($this->atribut($data) + ['password_hash' => $data['password']]);

        return redirect()->route('admin.user.index')
            ->with('success', 'Akun "' . $user->username . '" berhasil dibuat.');
    }

    public function show(User $user)
    {
        $user->load('role.hakAkses', 'moda', 'wilayah');

        return view('admin.users.show', [
            'user'         => $user,
            'jumlahData'   => $user->dataPemda()->count(),
            'jumlahDokumen' => $user->dokumen()->count(),
            'cakupan'      => PeranUser::CAKUPAN,
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.form', $this->dataForm($user, 'edit'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        $atribut = $this->atribut($data);
        $aktif = ! empty($data['aktif']);
        $roleBaru = Role::findOrFail($atribut['id_role']);

        // Perlindungan: Super Admin tidak boleh kehilangan aksesnya sendiri atau Super Admin terakhir
        if ($user->isSuperAdmin() && (! $roleBaru->isSuperAdmin() || ! $aktif)) {
            if ($user->id_user === $request->user()->id_user) {
                return back()->withInput()->with('error', 'Anda tidak dapat menurunkan role atau menonaktifkan akun Anda sendiri.');
            }
            if ($this->superAdminAktifLain($user) === 0) {
                return back()->withInput()->with('error', 'Tidak dapat dilakukan: ini Super Admin aktif terakhir.');
            }
        }

        if (! empty($data['password'])) {
            $atribut['password_hash'] = $data['password'];
        }
        if ($aktif && $user->diverifikasi_at !== null) {
            unset($atribut['diverifikasi_at']);       // tetap memakai waktu verifikasi yang lama
        }

        $user->update($atribut);

        return redirect()->route('admin.user.index')
            ->with('success', 'Akun "' . $user->username . '" berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id_user === $request->user()->id_user) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        if ($user->isSuperAdmin() && $this->superAdminAktifLain($user) === 0) {
            return back()->with('error', 'Tidak dapat menghapus Super Admin aktif terakhir.');
        }
        if ($user->dataPemda()->whereHas('ruteSubsidi')->exists() || $user->dokumen()->exists() || $user->verifikasi()->exists() || $user->pengajuanSurat()->exists()
            || $user->peninjauanSurat()->exists() || $user->rekomendasiTeknis()->exists() || $user->permintaanRevisi()->exists()) {
            return back()->with('error', 'Akun tidak dapat dihapus karena sudah memiliki pengajuan, dokumen, surat, atau riwayat verifikasi. Nonaktifkan akun lewat menu Edit.');
        }

        $username = $user->username;
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'Akun "' . $username . '" berhasil dihapus.');
    }

    public function export(Request $request)
    {
        if (! class_exists(\ZipArchive::class)) {
            return back()->with('error', 'Ekspor Excel membutuhkan ekstensi PHP "zip". Aktifkan extension=zip di php.ini lalu restart server.');
        }

        $rows = [];
        foreach ($this->query($this->filter($request))->get() as $i => $u) {
            $rows[] = [
                $i + 1,
                $u->full_name,
                $u->username,
                $u->email,
                $u->role->nama_role ?? '',
                $u->instansi,
                $u->wilayah->nama_wilayah ?? '',
                $u->moda->nama_moda ?? '',
                $u->diverifikasi_at ? 'Aktif' : 'Belum aktif',
                optional($u->created_at)->format('d-m-Y H:i'),
            ];
        }

        $path = XlsxExporter::buat(
            ['No', 'Nama Lengkap', 'Username', 'Email', 'Role', 'Instansi', 'Cakupan Wilayah', 'Cakupan Moda', 'Status', 'Terdaftar'],
            $rows,
            'Data User',
            [6, 28, 22, 34, 14, 34, 18, 16, 14, 18]
        );

        return response()->download(
            $path,
            'data-user-' . now()->format('Ymd-His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    // ------------------------------------------------------------------

    private function filter(Request $r): array
    {
        $role = (string) $r->query('role', '');

        return [
            'q'    => trim((string) $r->query('q', '')),
            'role' => ctype_digit($role) ? (int) $role : null,
        ];
    }

    private function query(array $f)
    {
        return User::query()
            ->with('role', 'moda', 'wilayah')
            ->when($f['role'], fn ($q, $r) => $q->where('id_role', $r))
            ->when($f['q'] !== '', function ($q) use ($f) {
                $like = '%' . addcslashes($f['q'], '\\%_') . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('full_name', 'like', $like)
                      ->orWhere('username', 'like', $like)
                      ->orWhere('email', 'like', $like)
                      ->orWhere('instansi', 'like', $like);
                });
            })
            ->orderBy('id_user');
    }

    /** Atribut akun sesuai role: cakupan yang tidak dipakai role itu dikosongkan. */
    private function atribut(array $d): array
    {
        $role = Role::findOrFail($d['id_role']);
        $syarat = PeranUser::syarat($role->nama_role);

        return [
            'full_name'       => $d['full_name'],
            'username'        => $d['username'],
            'email'           => $d['email'],
            'id_role'         => $role->id_role,
            'instansi'        => $d['instansi'] ?? null,
            'id_wilayah'      => in_array('wilayah', $syarat, true) ? ($d['id_wilayah'] ?? null) : null,
            'id_moda'         => in_array('moda', $syarat, true) ? ($d['id_moda'] ?? null) : null,
            'diverifikasi_at' => ! empty($d['aktif']) ? now() : null,
        ];
    }

    private function superAdminAktifLain(User $kecuali): int
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', PeranUser::SUPER_ADMIN))
            ->whereNotNull('diverifikasi_at')
            ->where('id_user', '!=', $kecuali->id_user)
            ->count();
    }

    private function dataForm(User $user, string $mode): array
    {
        $roles = Role::with('hakAkses')->orderBy('id_role')->get();

        return [
            'user'     => $user,
            'mode'     => $mode,
            'roles'    => $roles,
            'moda'     => Moda::orderBy('id_moda')->get(),
            'wilayah'  => Wilayah::orderBy('nama_wilayah')->get(),
            // dipakai JavaScript: isian cakupan & daftar hak akses per role
            'infoRole' => $roles->mapWithKeys(fn ($r) => [$r->id_role => [
                'syarat' => $r->isSuperAdmin() ? [] : PeranUser::syarat($r->nama_role),
                'super'  => $r->isSuperAdmin(),
                'hak'    => $r->hakAkses->pluck('nama')->values()->all(),
            ]]),
        ];
    }
}
