<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SimpanJawabanLangsungRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pengumpulan = $this->route('pengumpulan');

        if ($pengumpulan instanceof Pengumpulan) {
            return Gate::allows('update', $pengumpulan);
        }

        $kegiatan = $this->route('kegiatan');

        return $kegiatan instanceof Kegiatan
            && Gate::allows(
                'create',
                [Pengumpulan::class, $kegiatan]
            );
    }

    public function rules(): array
    {
        $sedangMengedit =
            $this->route('pengumpulan') instanceof Pengumpulan;

        return [
            'versi_form' => [
                Rule::requiredIf($sedangMengedit),
                'nullable',
                'string',
                'regex:/\A[a-f0-9]{64}\z/',
            ],

            'jawaban_teks' => [
                'nullable',
                'string',
                'max:' . max(
                    1,
                    min(
                        10000,
                        (int) config(
                            'pengumpulan.maks_karakter_jawaban',
                            10000
                        )
                    )
                ),
            ],

            'berkas_baru' => [
                'nullable',
                'array',
                'max:10',
            ],

            'berkas_baru.*' => [
                'required',
                'file',
                'min:1',
                'max:' . (int) config(
                    'berkas.maks_kib',
                    20480
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
                'string',
                'regex:/\A[1-9][0-9]{0,17}\z/',
                'distinct',
            ],

            'pemilik_id' => ['prohibited'],
            'detail_krs_id' => ['prohibited'],
            'kegiatan_id' => ['prohibited'],
            'status' => ['prohibited'],
            'revisi' => ['prohibited'],
            'dikirim_at' => ['prohibited'],
            'diubah_at' => ['prohibited'],
            'dibatalkan_at' => ['prohibited'],
            'hash_jawaban' => ['prohibited'],
            'berkas_ids' => ['prohibited'],
            'lampiran_ids' => ['prohibited'],
            'token_draf' => ['prohibited'],
            'dasar_versi' => ['prohibited'],
            'kunci_kirim' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'berkas_baru.max' =>
                'Maksimal sepuluh berkas dapat dipilih sekaligus.',

            'berkas_baru.*.file' =>
                'Salah satu unggahan bukan berkas yang valid.',

            'berkas_baru.*.max' =>
                'Ukuran setiap berkas maksimal 20 MB.',

            'berkas_baru.*.mimes' =>
                'Format yang diperbolehkan: PDF, JPG, PNG, Word, Excel, PowerPoint, dan ZIP.',

            'versi_form.required' =>
                'Versi jawaban tidak tersedia. Muat ulang halaman.',

            'versi_form.regex' =>
                'Versi jawaban tidak valid. Muat ulang halaman.',
        ];
    }
}