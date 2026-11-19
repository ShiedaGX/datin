@extends('layouts.app')

@section('title', 'Master Sektor & Komoditas')
@section('heading', 'Master Sektor & Komoditas')

@section('content')
<section class="box">
    <div class="box-head">
        <h2>Tambah sektor</h2>
        <p class="muted">Sektor dan komoditas di sini dipakai pada form Input Data dan deteksi sektor potensi. Perubahan langsung berlaku.</p>
    </div>
    <form class="sektor-form" method="POST" action="{{ route('admin.sektor.store') }}">
        @csrf
        <div class="field">
            <label for="nama_sektor_baru">Nama sektor</label>
            <input type="text" id="nama_sektor_baru" name="nama_sektor" maxlength="100" required value="{{ old('nama_sektor') }}" placeholder="mis. Perikanan &amp; Kelautan">
        </div>
        <div class="field field-kecil">
            <label for="urutan_baru">Urutan</label>
            <input type="number" id="urutan_baru" name="urutan" min="0" max="65535" value="{{ $urutanBerikut }}">
        </div>
        <button type="submit" class="btn btn-primary">Tambah Sektor</button>
    </form>
</section>

@forelse ($sektor as $s)
    @php($dipakaiS = (int) ($pakaiSektor[$s->id_sektor] ?? 0))
    <section class="box sektor-box">
        <div class="box-head row-between">
            <div>
                <h2>{{ $s->nama_sektor }}</h2>
                <p class="muted">{{ $s->komoditas->count() }} komoditas @if ($dipakaiS > 0)&middot; dipakai {{ $dipakaiS }} pengajuan @endif</p>
            </div>
        </div>

        <div class="sektor-aksi">
            <form class="sektor-form" method="POST" action="{{ route('admin.sektor.update', $s->id_sektor) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="ns{{ $s->id_sektor }}">Nama sektor</label>
                    <input type="text" id="ns{{ $s->id_sektor }}" name="nama_sektor" maxlength="100" required value="{{ $s->nama_sektor }}">
                </div>
                <div class="field field-kecil">
                    <label for="us{{ $s->id_sektor }}">Urutan</label>
                    <input type="number" id="us{{ $s->id_sektor }}" name="urutan" min="0" max="65535" value="{{ $s->urutan }}">
                </div>
                <button type="submit" class="btn btn-soft">Simpan</button>
            </form>
            <form method="POST" action="{{ route('admin.sektor.destroy', $s->id_sektor) }}" data-confirm="Hapus sektor {{ $s->nama_sektor }} beserta {{ $s->komoditas->count() }} komoditasnya?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline btn-hapus">Hapus Sektor</button>
            </form>
        </div>

        <div class="table-box">
            <table class="table table-data">
                <thead><tr><th>Komoditas</th><th class="num">Dipakai</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach ($s->komoditas as $k)
                        <tr>
                            <td data-label="Komoditas">
                                <form class="baris-form" method="POST" action="{{ route('admin.komoditas.update', $k->id_komoditas) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nama_komoditas" maxlength="100" required value="{{ $k->nama_komoditas }}" aria-label="Nama komoditas">
                                    <button type="submit" class="btn btn-soft">Simpan</button>
                                </form>
                            </td>
                            <td data-label="Dipakai" class="num">{{ (int) ($pakaiKomoditas[$k->id_komoditas] ?? 0) }}</td>
                            <td class="td-aksi">
                                <form method="POST" action="{{ route('admin.komoditas.destroy', $k->id_komoditas) }}" data-confirm="Hapus komoditas {{ $k->nama_komoditas }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-hapus">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="baris-tambah">
                        <td colspan="3">
                            <form class="baris-form" method="POST" action="{{ route('admin.sektor.komoditas.store', $s->id_sektor) }}">
                                @csrf
                                <input type="text" name="nama_komoditas" maxlength="100" required placeholder="Komoditas baru untuk {{ $s->nama_sektor }}" aria-label="Komoditas baru untuk {{ $s->nama_sektor }}">
                                <button type="submit" class="btn btn-primary">Tambah Komoditas</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
@empty
    <section class="box"><p class="empty-note">Belum ada sektor. Tambahkan sektor pertama di atas.</p></section>
@endforelse
@endsection
