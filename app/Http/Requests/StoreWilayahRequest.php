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
        return true; // akses dibatasi middleware 'auth' pada route
    }

    public function rules(): array
    {
        $rules = [
            'id_provinsi'            => ['required', 'integer', 'exists:provinsi,id_provinsi'],
            'id_wilayah'             => ['required', 'integer', 'exists:wilayah,id_wilayah'],
            'nama_kab_kota'          => ['required', 'string', 'max:100'],
            'klasifikasi_pemda'      => ['required', Rule::in(FormOptions::KLASIFIKASI)],
            'keterangan'             => ['nullable', 'array'],
            'keterangan.*'           => ['string', Rule::in(array_keys(FormOptions::KETERANGAN))],
            'potensi'                => ['required', 'array', 'max:5'],
            'potensi.*'              => ['nullable', 'string', 'max:200'],
            'id_moda'                => ['required', 'integer', 'exists:moda,id_moda'],
            'kebutuhan_konektivitas' => ['nullable', 'string', 'max:2000'],
        ];

        foreach (array_keys(FormOptions::DOKUMEN) as $field) {
            $rules[$field] = ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120']; // 5 MB
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            // Provinsi harus berada di wilayah regional yang dipilih
            if (! $v->errors()->hasAny(['id_provinsi', 'id_wilayah'])) {
                $provinsi = Provinsi::find($this->input('id_provinsi'));
                if ($provinsi && (int) $provinsi->id_wilayah !== (int) $this->input('id_wilayah')) {
                    $v->errors()->add('id_wilayah', 'Wilayah regional tidak sesuai dengan provinsi yang dipilih.');
                }
            }

            // Kab/Kota tidak boleh ganda dalam satu provinsi
            if (! $v->errors()->hasAny(['id_provinsi', 'nama_kab_kota'])) {
                $nama = mb_strtolower(trim((string) $this->input('nama_kab_kota')));
                $ada = KabKota::where('id_provinsi', $this->input('id_provinsi'))
                    ->whereRaw('LOWER(nama_kab_kota) = ?', [$nama])
                    ->exists();
                if ($ada) {
                    $v->errors()->add('nama_kab_kota', 'Kabupaten/Kota ini sudah terdaftar pada provinsi tersebut.');
                }
            }

            // Minimal satu potensi unggulan
            $terisi = collect($this->input('potensi', []))->filter(fn ($x) => trim((string) $x) !== '');
            if ($terisi->isEmpty() && ! $v->errors()->has('potensi')) {
                $v->errors()->add('potensi', 'Isi minimal satu potensi unggulan daerah.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'id_provinsi'            => 'Provinsi',
            'id_wilayah'             => 'Wilayah regional',
            'nama_kab_kota'          => 'Kabupaten/Kota',
            'klasifikasi_pemda'      => 'Klasifikasi wilayah',
            'id_moda'                => 'Moda transportasi',
            'kebutuhan_konektivitas' => 'Kebutuhan konektivitas',
            'potensi.*'              => 'Potensi unggulan',
            'surat_permohonan'       => 'Surat permohonan',
            'surat_pernyataan'       => 'Surat pernyataan',
            'surat_rencana_teknis'   => 'Surat rencana teknis',
        ];
    }

    public function messages(): array
    {
        return [
            'required'   => ':attribute wajib diisi.',
            'exists'     => ':attribute tidak valid.',
            'in'         => ':attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.file'   => ':attribute maksimal 5 MB.',
            'mimes'      => ':attribute harus berformat PDF, DOC, atau DOCX.',
            'uploaded'   => ':attribute gagal diunggah. Periksa batas ukuran upload pada server (upload_max_filesize).',
        ];
    }
}
