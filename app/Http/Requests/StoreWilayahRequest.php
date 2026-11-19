<?php

namespace App\Http\Requests;

use App\Models\KabKota;
use App\Models\Provinsi;
use App\Support\FormOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWilayahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'id_provinsi' => ['required', 'integer', 'exists:provinsi,id_provinsi'],
            'id_wilayah' => ['required', 'integer', 'exists:wilayah,id_wilayah'],
            'id_kab_kota' => ['required', 'integer', 'exists:kab_kota,id_kab_kota'],
            'klasifikasi_pemda' => ['required', Rule::in(FormOptions::KLASIFIKASI)],

            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['string', Rule::in(array_keys(FormOptions::KETERANGAN))],

            'potensi' => ['required', 'array', 'max:5'],
            'potensi.*' => ['nullable', 'string', 'max:200'],

            'id_moda' => ['required', 'integer', 'exists:moda,id_moda'],

            // Tujuan
            'tujuan' => ['required', 'array', 'min:1', 'max:20'],
            'tujuan.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:kab_kota,id_kab_kota',
                'different:id_kab_kota',
            ],

            // Setiap tujuan wajib memiliki minimal satu komoditas
            'tujuan.*.komoditas' => ['required', 'array', 'min:1'],

            'tujuan.*.komoditas.*.nama' => [
                'required',
                'string',
                'max:150',
            ],

            'tujuan.*.komoditas.*.berat' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999',
            ],
        ];

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

            if (!$v->errors()->hasAny(['id_provinsi', 'id_wilayah'])) {
                $provinsi = Provinsi::find($this->input('id_provinsi'));

                if ($provinsi &&
                    (int) $provinsi->id_wilayah !== (int) $this->input('id_wilayah')) {
                    $v->errors()->add(
                        'id_wilayah',
                        'Wilayah regional tidak sesuai dengan provinsi.'
                    );
                }
            }

            if (!$v->errors()->hasAny(['id_provinsi', 'id_kab_kota'])) {
                $kab = KabKota::with(
                    'dataPemda.ruteSubsidi',
                    'dataPemda.permintaanRevisi'
                )->find($this->input('id_kab_kota'));

                if ($kab && (int) $kab->id_provinsi !== (int) $this->input('id_provinsi')) {
                    $v->errors()->add(
                        'id_kab_kota',
                        'Kabupaten/Kota tidak berada di provinsi yang dipilih.'
                    );
                } elseif ($kab && ($alasan = $kab->alasanTidakTersedia($this->user()))) {
                    $v->errors()->add(
                        'id_kab_kota',
                        'Kabupaten/Kota ini ' . $alasan . '.'
                    );
                }
            }

            $terisi = collect($this->input('potensi', []))
                ->filter(fn ($x) => trim((string) $x) !== '');

            if ($terisi->isEmpty() && !$v->errors()->has('potensi')) {
                $v->errors()->add(
                    'potensi',
                    'Isi minimal satu potensi unggulan daerah.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'id_provinsi' => 'Provinsi',
            'id_wilayah' => 'Wilayah regional',
            'id_kab_kota' => 'Kabupaten/Kota',
            'klasifikasi_pemda' => 'Klasifikasi wilayah',
            'id_moda' => 'Moda transportasi',
            'tujuan' => 'Rute tujuan',
            'tujuan.*.id' => 'Kabupaten/Kota tujuan',
            'tujuan.*.komoditas' => 'Daftar komoditas',
            'tujuan.*.komoditas.*.nama' => 'Nama komoditas',
            'tujuan.*.komoditas.*.berat' => 'Berat komoditas',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak valid.',
            'tujuan.required' => 'Pilih minimal satu rute tujuan.',
            'tujuan.*.id.distinct' => 'Kabupaten/Kota tujuan tidak boleh dipilih dua kali.',
            'tujuan.*.id.different' => 'Tujuan tidak boleh sama dengan Kabupaten/Kota asal.',
            'tujuan.*.komoditas.required' => 'Setiap tujuan wajib memiliki minimal satu komoditas.',
            'tujuan.*.komoditas.min' => 'Setiap tujuan wajib memiliki minimal satu komoditas.',
            'tujuan.*.komoditas.*.nama.required' => 'Nama komoditas wajib diisi.',
            'tujuan.*.komoditas.*.berat.required' => 'Berat komoditas wajib diisi.',
            'tujuan.*.komoditas.*.berat.numeric' => 'Berat harus berupa angka.',
            'tujuan.*.komoditas.*.berat.gt' => 'Berat harus lebih dari 0.',
            'mimes' => ':attribute harus berformat PDF, DOC, atau DOCX.',
            'max.file' => ':attribute maksimal 5 MB.',
        ];
    }
}
