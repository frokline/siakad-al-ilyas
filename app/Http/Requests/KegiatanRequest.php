<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use App\Services\WaktuKegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class KegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kegiatan = $this->route('kegiatan');

        if ($kegiatan instanceof Kegiatan) {
            return Gate::allows(
                'update',
                $kegiatan
            );
        }

        $kelasId = $this->input(
            'kelas_kuliah_id'
        );

        if (
            ! is_numeric($kelasId)
            || (int) $kelasId < 1
        ) {
            return false;
        }

        $kelas = \App\Models\KelasKuliah::query()
            ->find($kelasId);

        return $kelas !== null
            && Gate::allows(
                'create',
                [
                    Kegiatan::class,
                    $kelas,
                ]
            );
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'judul' => $this->rapikan(
                $this->input('judul')
            ),

            'instruksi' => $this->rapikan(
                $this->input('instruksi')
            ),

            'tautan_eksternal' => $this->rapikan(
                $this->input('tautan_eksternal')
            ),

            'alasan' => $this->rapikan(
                $this->input('alasan')
            ),
        ]);
    }

    public function rules(): array
    {
        $edit = $this->isMethod('PATCH')
            || $this->isMethod('PUT');

        $menggunakanPengumpulan =
            $this->input('jenis') !== Kegiatan::MATERI;

        return [
            'kelas_kuliah_id' => $edit
                ? [
                    'prohibited',
                ]
                : [
                    'required',
                    'integer',
                    'min:1',
                    'exists:kelas_kuliah,id',
                ],

            'form_token' => $edit
                ? [
                    'prohibited',
                ]
                : [
                    'required',
                    'string',
                    'uuid',
                ],

            'versi' => $edit
                ? [
                    'required',
                    'string',
                    'size:64',
                    'regex:/\A[a-f0-9]{64}\z/',
                ]
                : [
                    'prohibited',
                ],

            'jenis' => [
                'required',
                'string',
                Rule::in(array_keys(Kegiatan::JENIS)),
            ],

            'pertemuan_id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'judul' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'instruksi' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'tautan_eksternal' => [
                'nullable',
                'url',
                'max:2000',
            ],

            'buka_lokal' => [
                Rule::requiredIf(
                    $menggunakanPengumpulan
                ),
                'nullable',
                'string',
                'size:16',
            ],

            'tenggat_lokal' => [
                Rule::requiredIf(
                    $menggunakanPengumpulan
                ),
                'nullable',
                'string',
                'size:16',
            ],

            'maksimal_berkas' => [
                Rule::requiredIf(
                    $menggunakanPengumpulan
                ),
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],

            'maksimal_mb_per_berkas' => [
                Rule::requiredIf(
                    $menggunakanPengumpulan
                ),
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],

            'ekstensi_jawaban' => [
                Rule::requiredIf(
                    $menggunakanPengumpulan
                ),
                'nullable',
                'array',
                'min:1',
            ],

            'ekstensi_jawaban.*' => [
                'required',
                'string',
                'distinct',
                Rule::in(
                    Kegiatan::EKSTENSI_JAWABAN
                ),
            ],

            'lampiran_baru' => [
                'nullable',
                'array',
                'max:10',
            ],

            'lampiran_baru.*' => [
                'required',
                'file',
                'max:' . (int) config(
                    'berkas.maks_kib',
                    51200
                ),
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,zip',
            ],

            'lampiran_dihapus' => [
                'nullable',
                'array',
                'max:10',
            ],

            'lampiran_dihapus.*' => [
                'required',
                'integer',
                'distinct',
                'min:1',
            ],

            'alasan' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'prohibited',
            ],

            'terbit_at' => [
                'prohibited',
            ],

            'ditutup_at' => [
                'prohibited',
            ],

            'diarsipkan_at' => [
                'prohibited',
            ],

            'revisi' => [
                'prohibited',
            ],

            'pembuat_id' => [
                'prohibited',
            ],

            'hash_permohonan' => [
                'prohibited',
            ],

            'berkas_ids' => [
                'prohibited',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $validator
                    ->errors()
                    ->isNotEmpty()
                    || $this->input('jenis')
                    === Kegiatan::MATERI
                ) {
                    return;
                }

                try {
                    $buka = app(WaktuKegiatan::class)
                        ->dariForm(
                            (string) $this->input('buka_lokal'),
                            'buka_lokal'
                        );

                    $tenggat = app(WaktuKegiatan::class)
                        ->dariForm(
                            (string) $this->input('tenggat_lokal'),
                            'tenggat_lokal'
                        );
                } catch (\Throwable) {
                    $validator
                        ->errors()
                        ->add(
                            'buka_lokal',
                            'Format waktu kegiatan tidak valid.'
                        );

                    return;
                }

                if (
                    $tenggat->lteTo(
                        $buka
                    )
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'tenggat_lokal',
                            'Batas pengumpulan harus setelah waktu mulai.'
                        );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'kelas_kuliah_id.required' =>
            'Kelas kegiatan tidak ditemukan.',

            'kelas_kuliah_id.exists' =>
            'Kelas kegiatan tidak tersedia.',

            'form_token.required' =>
            'Token formulir tidak ditemukan. Muat ulang halaman.',

            'form_token.uuid' =>
            'Token formulir tidak valid. Muat ulang halaman.',

            'versi.required' =>
            'Versi kegiatan tidak ditemukan. Muat ulang halaman.',

            'versi.regex' =>
            'Versi kegiatan tidak valid. Muat ulang halaman.',

            'jenis.required' =>
            'Jenis pembelajaran wajib dipilih.',

            'jenis.in' =>
            'Jenis pembelajaran tidak dikenali.',

            'judul.required' =>
            'Judul pembelajaran wajib diisi.',

            'buka_lokal.required' =>
            'Waktu mulai pengerjaan wajib diisi.',

            'tenggat_lokal.required' =>
            'Batas pengumpulan wajib diisi.',

            'maksimal_berkas.required' =>
            'Jumlah maksimal berkas jawaban wajib diisi.',

            'maksimal_mb_per_berkas.required' =>
            'Ukuran maksimal berkas jawaban wajib diisi.',

            'ekstensi_jawaban.required' =>
            'Pilih minimal satu jenis berkas jawaban.',

            'lampiran_baru.max' =>
            'Lampiran pembelajaran maksimal 10 berkas.',

            'lampiran_baru.*.max' =>
            'Ukuran salah satu lampiran terlalu besar.',

            'lampiran_baru.*.mimes' =>
            'Lampiran hanya boleh berupa PDF, gambar, Word, Excel, PowerPoint, atau ZIP.',
        ];
    }

    private function rapikan(
        mixed $nilai
    ): ?string {
        if (! is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return $nilai === ''
            ? null
            : $nilai;
    }
}
