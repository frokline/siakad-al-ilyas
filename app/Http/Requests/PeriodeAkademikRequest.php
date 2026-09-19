<?php

namespace App\Http\Requests;

use App\Models\PeriodeAkademik;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PeriodeAkademikRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user('web');

        return $actor instanceof User
            && Gate::forUser($actor)->allows('kelola-periode-akademik');
    }

    protected function prepareForValidation(): void
    {
        foreach (['jenis', 'status'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $this->merge([
                    $field => Str::lower(trim($value)),
                ]);
            }
        }
    }

    public function rules(): array
    {
        $editing = $this->route('periodeAkademik') instanceof PeriodeAkademik;

        return [
            'tahun_mulai' => [
                'bail',
                'required',
                'integer',
                'between:1900,9998',
            ],

            'jenis' => [
                'bail',
                'required',
                'string',
                Rule::in(array_keys(PeriodeAkademik::JENIS)),
            ],

            'mulai' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:1900-01-01',
            ],

            'selesai' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:1900-01-01',
            ],

            'krs_mulai' => [
                'bail',
                'nullable',
                'required_with:krs_selesai',
                'date_format:Y-m-d\TH:i',
                'after_or_equal:1900-01-01T00:00',
            ],

            'krs_selesai' => [
                'bail',
                'nullable',
                'required_with:krs_mulai',
                'date_format:Y-m-d\TH:i',
                'after_or_equal:1900-01-01T00:00',
            ],

            'status' => [
                'bail',
                'required',
                'string',
                Rule::in(array_keys(PeriodeAkademik::STATUS)),
            ],

            'version' => $editing
                ? ['bail', 'required', 'string', 'regex:/\A[a-f0-9]{64}\z/']
                : ['prohibited'],

            'kode' => ['prohibited'],
            'id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // Format tanggal sudah diperiksa sebelum dibandingkan.
                if ($this->input('selesai') < $this->input('mulai')) {
                    $validator->errors()->add(
                        'selesai',
                        'Tanggal selesai tidak boleh mendahului tanggal mulai.',
                    );
                }

                if ($this->filled('krs_mulai')) {
                    if (
                        $this->input('krs_selesai')
                        < $this->input('krs_mulai')
                    ) {
                        $validator->errors()->add(
                            'krs_selesai',
                            'Batas akhir KRS tidak boleh mendahului awal KRS.',
                        );
                    }

                    if (
                        substr($this->input('krs_selesai'), 0, 10)
                        > $this->input('selesai')
                    ) {
                        $validator->errors()->add(
                            'krs_selesai',
                            'Batas akhir KRS tidak boleh melewati tanggal selesai periode.',
                        );
                    }
                }

                $query = PeriodeAkademik::query()
                    ->where('tahun_mulai', (int) $this->input('tahun_mulai'))
                    ->where('jenis', $this->input('jenis'));

                $current = $this->route('periodeAkademik');

                if ($current instanceof PeriodeAkademik) {
                    $query->where('id', '<>', $current->getKey());
                }

                if ($query->exists()) {
                    $validator->errors()->add(
                        'jenis',
                        'Periode dengan tahun ajaran dan jenis semester tersebut sudah ada.',
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_with' => ':attribute harus diisi bersama :values.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'date_format' => 'Format :attribute tidak valid.',
            'after_or_equal' => ':attribute minimal 1 Januari 1900.',
            'tahun_mulai.between' => 'Tahun awal harus berada antara 1900 dan 9998.',
            'jenis.in' => 'Jenis semester tidak valid.',
            'status.in' => 'Status periode tidak valid.',
            'version.required' => 'Muat ulang halaman sebelum menyimpan.',
            'version.regex' => 'Formulir tidak valid. Muat ulang halaman.',
            'prohibited' => ':attribute tidak boleh dikirim.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tahun_mulai' => 'Tahun awal akademik',
            'jenis' => 'Jenis semester',
            'mulai' => 'Tanggal mulai perkuliahan',
            'selesai' => 'Tanggal selesai perkuliahan',
            'krs_mulai' => 'Awal pengisian KRS',
            'krs_selesai' => 'Batas akhir pengisian KRS',
            'status' => 'Status periode',
        ];
    }
}
