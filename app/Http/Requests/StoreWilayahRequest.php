<?php

namespace App\Http\Requests;

use App\Models\KabKota;
use App\Models\Provinsi;
use App\Models\MasterSatuanData;
use App\Support\FormOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWilayahRequest extends FormRequest
{
    /**
     * Daftar satuan lama.
     *
     * Dipertahankan untuk kompatibilitas apabila
     * masih digunakan oleh bagian sistem lainnya.
     *
     * Validasi aktual menggunakan MasterSatuanData.
     */
    public const SATUAN_DATA = [
        'Ton',
        'Ekor',
        'Perjalanan Wisatawan',
        'Batang',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil pilihan satuan aktif dari database.
        $satuanAktif = MasterSatuanData::aktif()
            ->pluck('nama_satuan')
            ->all();

        $rules = [
            'id_provinsi' => [
                'required',
                'integer',
                'exists:provinsi,id_provinsi',
            ],

            'id_wilayah' => [
                'required',
                'integer',
                'exists:wilayah,id_wilayah',
            ],

            'id_kab_kota' => [
                'required',
                'integer',
                'exists:kab_kota,id_kab_kota',
            ],

            'klasifikasi_pemda' => [
                'required',
                Rule::in(FormOptions::KLASIFIKASI),
            ],

            'keterangan' => [
                'nullable',
                'array',
            ],

            'keterangan.*' => [
                'string',
                Rule::in(
                    array_keys(FormOptions::KETERANGAN)
                ),
            ],

            'potensi' => [
                'required',
                'array',
                'max:5',
            ],

            'potensi.*' => [
                'nullable',
                'string',
                'max:200',
            ],

            // Moda boleh sama dengan pengajuan sebelumnya.
            'id_moda' => [
                'required',
                'integer',
                'exists:moda,id_moda',
            ],

            // Daftar tujuan.
            'tujuan' => [
                'required',
                'array',
                'min:1',
                'max:20',
            ],

            'tujuan.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:kab_kota,id_kab_kota',
                'different:id_kab_kota',
            ],

            // Setiap tujuan minimal satu komoditas.
            'tujuan.*.komoditas' => [
                'required',
                'array',
                'min:1',
            ],

            // Nama komoditas.
            'tujuan.*.komoditas.*.nama' => [
                'required',
                'string',
                'max:150',
            ],

            // Jumlah komoditas.
            'tujuan.*.komoditas.*.jumlah' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999',
            ],

            // Satuan berdasarkan master satuan aktif.
            'tujuan.*.komoditas.*.satuan_data' => [
                'required',
                'string',
                Rule::in($satuanAktif),
            ],
        ];

        // Validasi dokumen pengajuan.
        foreach (array_keys(FormOptions::DOKUMEN) as $field) {
            $rules[$field] = [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
            ];

            $rules['nomor.' . $field] = [
                'required',
                'string',
                'max:100',
            ];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {

            // Validasi kesesuaian wilayah dan provinsi.
            if (! $v->errors()->hasAny([
                'id_provinsi',
                'id_wilayah',
            ])) {
                $provinsi = Provinsi::find(
                    $this->input('id_provinsi')
                );

                if (
                    $provinsi
                    && (int) $provinsi->id_wilayah
                        !== (int) $this->input('id_wilayah')
                ) {
                    $v->errors()->add(
                        'id_wilayah',
                        'Wilayah regional tidak sesuai dengan provinsi.'
                    );
                }
            }

            // Validasi Kabupaten/Kota asal.
            if (! $v->errors()->hasAny([
                'id_provinsi',
                'id_kab_kota',
            ])) {
                $kab = KabKota::with(
                    'dataPemda.ruteSubsidi',
                    'dataPemda.permintaanRevisi'
                )->find(
                    $this->input('id_kab_kota')
                );

                if (
                    $kab
                    && (int) $kab->id_provinsi
                        !== (int) $this->input('id_provinsi')
                ) {
                    $v->errors()->add(
                        'id_kab_kota',
                        'Kabupaten/Kota tidak berada di provinsi yang dipilih.'
                    );
                } elseif (
                    $kab
                    && ($alasan = $kab->alasanTidakTersedia(
                        $this->user()
                    ))
                ) {
                    $v->errors()->add(
                        'id_kab_kota',
                        'Kabupaten/Kota ini '
                        . $alasan
                        . '.'
                    );
                }
            }

            // Minimal satu potensi unggulan.
            if (! $v->errors()->has('potensi')) {
                $terisi = collect(
                    $this->input('potensi', [])
                )->filter(
                    fn ($x) =>
                        is_string($x)
                        && trim($x) !== ''
                );

                if ($terisi->isEmpty()) {
                    $v->errors()->add(
                        'potensi',
                        'Isi minimal satu potensi unggulan daerah.'
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'id_provinsi' =>
                'Provinsi',

            'id_wilayah' =>
                'Wilayah regional',

            'id_kab_kota' =>
                'Kabupaten/Kota',

            'klasifikasi_pemda' =>
                'Klasifikasi wilayah',

            'id_moda' =>
                'Moda transportasi',

            'potensi' =>
                'Potensi unggulan daerah',

            'tujuan' =>
                'Rute tujuan',

            'tujuan.*.id' =>
                'Kabupaten/Kota tujuan',

            'tujuan.*.komoditas' =>
                'Daftar komoditas',

            'tujuan.*.komoditas.*.nama' =>
                'Nama komoditas',

            'tujuan.*.komoditas.*.jumlah' =>
                'Jumlah komoditas',

            'tujuan.*.komoditas.*.satuan_data' =>
                'Satuan data komoditas',
        ];
    }

    public function messages(): array
    {
        return [
            'required' =>
                ':attribute wajib diisi.',

            'exists' =>
                ':attribute tidak valid.',

            'tujuan.required' =>
                'Pilih minimal satu rute tujuan.',

            'tujuan.*.id.distinct' =>
                'Kabupaten/Kota tujuan tidak boleh dipilih dua kali.',

            'tujuan.*.id.different' =>
                'Tujuan tidak boleh sama dengan Kabupaten/Kota asal.',

            'tujuan.*.komoditas.required' =>
                'Setiap tujuan wajib memiliki minimal satu komoditas.',

            'tujuan.*.komoditas.min' =>
                'Setiap tujuan wajib memiliki minimal satu komoditas.',

            'tujuan.*.komoditas.*.nama.required' =>
                'Nama komoditas wajib diisi.',

            'tujuan.*.komoditas.*.jumlah.required' =>
                'Jumlah komoditas wajib diisi.',

            'tujuan.*.komoditas.*.jumlah.numeric' =>
                'Jumlah komoditas harus berupa angka.',

            'tujuan.*.komoditas.*.jumlah.gt' =>
                'Jumlah komoditas harus lebih dari 0.',

            'tujuan.*.komoditas.*.satuan_data.required' =>
                'Satuan data wajib dipilih.',

            'tujuan.*.komoditas.*.satuan_data.in' =>
                'Pilih satuan data yang tersedia dan aktif pada Master Satuan Data.',

            'mimes' =>
                ':attribute harus berformat PDF, DOC, atau DOCX.',

            'max.file' =>
                ':attribute maksimal 5 MB.',

            'uploaded' =>
                ':attribute gagal diunggah. Periksa ukuran berkas dan coba kembali.',
        ];
    }
}
