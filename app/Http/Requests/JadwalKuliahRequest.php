<?php

namespace App\Http\Requests;

use App\Models\JadwalKuliah;
use App\Rules\TautanPertemuanAman;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JadwalKuliahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-jadwal-kuliah') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $rapi = [];
        foreach (['lokasi', 'tautan_pertemuan', 'alasan', 'versi_jadwal', 'metode', 'aksi_tautan'] as $field) {
            if (is_string($this->input($field))) {
                $nilai = trim($this->input($field));
                $rapi[$field] = $nilai === '' ? null : $nilai;
            }
        }
        $this->merge($rapi);
    }

    public function rules(): array
    {
        $nonaktif = $this->routeIs('admin.jadwal-kuliah.nonaktifkan');
        $ubah = $this->route('jadwalKuliah') !== null;
        $aturan = [
            'versi_jadwal' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:2000'],
            'konfirmasi' => ['required', 'accepted'],
        ];
        $pola = [
            'kelas_kuliah_id' => $ubah ? ['prohibited'] : ['required', 'integer', 'exists:kelas_kuliah,id'],
            'hari' => ['required', 'integer', 'between:1,7'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'berlaku_mulai' => ['required', 'date_format:Y-m-d'],
            'berlaku_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:berlaku_mulai'],
            'metode' => ['required', Rule::in(array_keys(JadwalKuliah::METODE))],
            'lokasi' => ['nullable', 'string', 'max:150', 'required_if:metode,luring,campuran', 'prohibited_if:metode,daring'],
            'aksi_tautan' => ['required', Rule::in(['pertahankan', 'ganti', 'hapus'])],
            'tautan_pertemuan' => [
                'nullable',
                'required_if:aksi_tautan,ganti',
                'prohibited_unless:aksi_tautan,ganti',
                'string',
                'max:2048',
                'prohibited_if:metode,luring',
                new TautanPertemuanAman(),
            ],
            'aktif' => $ubah ? ['required', 'boolean'] : ['prohibited'],
        ];
        foreach ($pola as $field => $rules) {
            $aturan[$field] = $nonaktif ? ['prohibited'] : $rules;
        }
        foreach (
            [
                'id',
                'revisi',
                'dinonaktifkan_at',
                'created_at',
                'updated_at',
                'pelaku_id',
                'rombel_id',
                'periode_akademik_id',
                'dosen_id',
                'jadwal',
                'tim'
            ] as $field
        ) {
            $aturan[$field] = ['prohibited'];
        }
        return $aturan;
    }

    public function attributes(): array
    {
        return [
            'kelas_kuliah_id' => 'kelas kuliah',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'berlaku_mulai' => 'tanggal mulai',
            'berlaku_selesai' => 'tanggal selesai',
            'aksi_tautan' => 'tindakan tautan',
            'tautan_pertemuan' => 'tautan pertemuan',
            'versi_jadwal' => 'versi formulir',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'prohibited' => ':attribute tidak boleh dikirim pada tindakan ini.',
            'konfirmasi.accepted' => 'Centang konfirmasi sebelum menyimpan.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai pada hari yang sama.',
            'berlaku_selesai.after_or_equal' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
            'tautan_pertemuan.required_if' => 'Masukkan tautan baru jika memilih Ganti.',
            'lokasi.required_if' => 'Lokasi wajib untuk perkuliahan luring/campuran.',
            'lokasi.prohibited_if' => 'Kosongkan lokasi untuk perkuliahan daring.',
            'tautan_pertemuan.prohibited_if' => 'Jadwal luring tidak memakai tautan pertemuan.',
        ];
    }
}
