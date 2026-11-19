
@extends('layouts.app')

@section('title', 'Master Satuan Data')
@section('heading', 'Master Satuan Data')

@section('content')

<section class="box">

    <div class="box-head">
        <h2>Tambah Satuan Data</h2>
        <p class="muted">
            Kelola satuan data yang dapat dipilih Pemda
            ketika mengajukan subsidi transportasi.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.master-satuan.store') }}"
        class="master-form"
    >
        @csrf

        <div class="field">
            <label for="nama_satuan">
                Nama Satuan
            </label>

            <input
                type="text"
                id="nama_satuan"
                name="nama_satuan"
                value="{{ old('nama_satuan') }}"
                maxlength="100"
                placeholder="Contoh: Kilogram"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            + Tambah Satuan
        </button>
    </form>

</section>


<section class="box">

    <div class="box-head">
        <h2>Daftar Satuan Data</h2>
        <p class="muted">
            {{ $satuan->count() }} satuan terdaftar.
            Satuan yang sudah digunakan tidak dapat
            dihapus permanen.
        </p>
    </div>

    <div class="table-box">
        <table class="table table-data">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Satuan</th>
                    <th>Status</th>
                    <th>Penggunaan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($satuan as $item)

                    @php
                        $digunakan = $item->sudah_digunakan;
                    @endphp

                    <tr>

                        <td data-label="No">
                            {{ $loop->iteration }}
                        </td>

                        <td data-label="Nama Satuan">
                            <strong>
                                {{ $item->nama_satuan }}
                            </strong>
                        </td>

                        <td data-label="Status">
                            @if ($item->status_aktif)
                                <span class="badge badge-success">
                                    Aktif
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    Nonaktif
                                </span>
                            @endif
                        </td>

                        <td data-label="Penggunaan">
                            @if ($digunakan)
                                <span class="muted">
                                    Sudah digunakan
                                </span>
                            @else
                                <span class="muted">
                                    Belum digunakan
                                </span>
                            @endif
                        </td>

                        <td data-label="Aksi">
                            <div class="master-actions">

                                <button
                                    type="button"
                                    class="btn btn-soft"
                                    onclick="toggleEdit({{ $item->id_satuan_data }})"
                                >
                                    Edit
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('admin.master-satuan.toggle', $item) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn btn-outline"
                                    >
                                        {{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                @if (! $digunakan)

                                    <form
                                        method="POST"
                                        action="{{ route('admin.master-satuan.destroy', $item) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus satuan ini secara permanen?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger"
                                        >
                                            Hapus
                                        </button>
                                    </form>

                                @else

                                    <button
                                        type="button"
                                        class="btn btn-outline"
                                        disabled
                                        title="Satuan sudah digunakan dalam pengajuan"
                                    >
                                        Hapus
                                    </button>

                                @endif

                            </div>
                        </td>

                    </tr>

                    <tr
                        id="edit-{{ $item->id_satuan_data }}"
                        class="master-edit-row"
                        hidden
                    >
                        <td colspan="5">

                            <form
                                method="POST"
                                action="{{ route('admin.master-satuan.update', $item) }}"
                                class="master-form"
                            >
                                @csrf
                                @method('PUT')

                                <div class="field">
                                    <label>
                                        Edit Nama Satuan
                                    </label>

                                    <input
                                        type="text"
                                        name="nama_satuan"
                                        value="{{ $item->nama_satuan }}"
                                        maxlength="100"
                                        required
                                    >
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Simpan Perubahan
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline"
                                    onclick="toggleEdit({{ $item->id_satuan_data }})"
                                >
                                    Batal
                                </button>
                            </form>

                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="empty">
                            Belum ada satuan data.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>

</section>


<style>
    .master-form {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 15px;
    }

    .master-form .field {
        flex: 1;
        min-width: 220px;
    }

    .master-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .master-actions form {
        margin: 0;
    }

    .master-edit-row td {
        background: #f0f9ff;
        padding: 16px;
    }

    .master-actions button:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    @media (max-width: 640px) {
        .master-form {
            flex-direction: column;
            align-items: stretch;
        }

        .master-actions {
            gap: 6px;
        }
    }
</style>


<script>
    function toggleEdit(id) {
        const row = document.getElementById('edit-' + id);

        if (!row) {
            return;
        }

        row.hidden = !row.hidden;
    }
</script>

@endsection
