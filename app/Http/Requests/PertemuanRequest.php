<?php

namespace App\Http\Requests;

use App\Models\JadwalKuliah;
use App\Models\Pertemuan;
use App\Rules\TautanPertemuanAman;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PertemuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-pertemuan') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $nilai = [];
        foreach (['topik', 'rencana', 'realisasi', 'lokasi', 'tautan_pertemuan', 'alasan', 'versi_pertemuan'] as $key) {
            if (is_string($this->input($key))) {
                $teks = trim($this->input($key));
                $nilai[$key] = $teks === '' ? null : $teks;
            }
        }
        $this->merge($nilai);
    }

    public function rules(): array
    {
        $rencana = $this->routeIs('admin.pertemuan.store', 'admin.pertemuan.update');
        $baru = $this->routeIs('admin.pertemuan.store');
        $aturan = [
            'versi_pertemuan' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:2000'],
            'konfirmasi' => ['required', 'accepted'],
            'realisasi' => $this->routeIs('admin.pertemuan.selesai')
                ? ['required', 'string', 'min:10', 'max:20000'] : ['prohibited'],
        ];
        $kolom = [
            'kelas_kuliah_id' => $baru ? ['required', 'integer', 'exists:kelas_kuliah,id'] : ['prohibited'],
            'nomor' => $baru ? ['required', 'integer', 'between:1,65535'] : ['prohibited'],
            'jadwal_kuliah_id' => ['nullable', 'integer', 'exists:jadwal_kuliah,id'],
            'pengajar_kelas_id' => ['required', 'integer', 'exists:pengajar_kelas,id'],
            'jenis' => ['required', Rule::in(array_keys(Pertemuan::JENIS))],
            'topik' => ['required', 'string', 'max:200'],
            'rencana' => ['nullable', 'string', 'max:10000'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'metode' => ['required', Rule::in(array_keys(JadwalKuliah::METODE))],
            'lokasi' => ['nullable', 'string', 'max:150', 'required_if:metode,luring,campuran', 'prohibited_if:metode,daring'],
            'aksi_tautan' => ['required', Rule::in(['pertahankan', 'ganti', 'hapus', 'salin_jadwal'])],
            'tautan_pertemuan' => [
                'nullable',
                'required_if:aksi_tautan,ganti',
                'prohibited_unless:aksi_tautan,ganti',
                'string',
                'max:2048',
                'prohibited_if:metode,luring',
                new TautanPertemuanAman()
            ],
        ];
        foreach ($kolom as $key => $rules) {
            $aturan[$key] = $rencana ? $rules : ['prohibited'];
        }
        foreach (
            [
                'id',
                'status',
                'revisi',
                'mulai_rencana',
                'selesai_rencana',
                'mulai_aktual',
                'selesai_aktual',
                'dibatalkan_at',
                'jadwal_snapshot',
                'pengajar_snapshot',
                'created_at',
                'updated_at',
                'pelaku_id',
                'aksi'
            ] as $key
        ) {
            $aturan[$key] = ['prohibited'];
        }
        return $aturan;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'prohibited' => ':attribute tidak boleh dikirim pada tindakan ini.',
            'konfirmasi.accepted' => 'Centang konfirmasi sebelum menyimpan.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai dalam hari yang sama.',
            'tautan_pertemuan.required_if' => 'Isi tautan baru jika memilih Ganti.',
            'lokasi.required_if' => 'Lokasi wajib untuk luring/campuran.',
            'lokasi.prohibited_if' => 'Kosongkan lokasi untuk daring.',
        ];
    }

    public function attributes(): array
    {
        return [
            'pengajar_kelas_id' => 'penanggung jawab',
            'jadwal_kuliah_id' => 'pola sumber',
            'kelas_kuliah_id' => 'kelas',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'versi_pertemuan' => 'versi formulir',
            'tautan_pertemuan' => 'tautan pertemuan'
        ];
    }
}
