<?php

namespace App\Exports;

use App\Models\RegistrasiSemester;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrasiSemesterExport extends DefaultValueBinder implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomValueBinder
{
    private array $filter;

    public function __construct(array $filter = [])
    {
        $this->filter = $filter;
    }

    public function query(): Builder
    {
        $query = RegistrasiSemester::query()->with([
            'riwayatStudi.mahasiswa.user:id,nama,status',
            'riwayatStudi.kurikulum.programStudi',
            'periodeAkademik',
            'rombel.paketSemester',
        ]);

        if ($q = trim((string) ($this->filter['q'] ?? ''))) {
            $query->where(function (Builder $query) use ($q): void {
                $query->whereHas(
                    'riwayatStudi.mahasiswa',
                    function (Builder $mahasiswa) use ($q): void {
                        $mahasiswa->where('nim', 'like', "%{$q}%")
                            ->orWhereHas(
                                'user',
                                fn (Builder $user) => $user->where(
                                    'nama',
                                    'like',
                                    "%{$q}%"
                                )
                            );
                    }
                )->orWhereHas(
                    'rombel',
                    fn (Builder $rombel) => $rombel->where(
                        'kode',
                        'like',
                        "%{$q}%"
                    )
                );
            });
        }

        foreach ([
            'periode_akademik_id',
            'semester_studi',
            'status',
        ] as $field) {
            if (
                isset($this->filter[$field])
                && $this->filter[$field] !== ''
            ) {
                $query->where($field, $this->filter[$field]);
            }
        }

        if (! empty($this->filter['program_studi_id'])) {
            $programStudiId = $this->filter['program_studi_id'];

            $query->whereHas(
                'riwayatStudi.kurikulum',
                fn (Builder $kurikulum) => $kurikulum->where(
                    'program_studi_id',
                    $programStudiId
                )
            );
        }

        return $query->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Periode Akademik',
            'Semester Studi',
            'Rombel',
            'Status Registrasi',
        ];
    }

    public function map(mixed $registrasi): array
    {
        return [
            (string) ($registrasi->riwayatStudi?->mahasiswa?->nim ?? ''),
            (string) (
                $registrasi->riwayatStudi?->mahasiswa?->user?->nama ?? ''
            ),
            (string) (
                $registrasi->riwayatStudi?->kurikulum?->programStudi?->nama
                ?? ''
            ),
            (string) ($registrasi->periodeAkademik?->kode ?? ''),
            (string) ($registrasi->semester_studi ?? ''),
            (string) ($registrasi->rombel?->kode ?? ''),
            (string) (
                RegistrasiSemester::STATUS[$registrasi->status]
                ?? $registrasi->status
            ),
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        // NIM harus selalu berupa teks agar formatnya tidak berubah.
        if ($cell->getColumn() === 'A' && $value !== null) {
            $cell->setValueExplicit(
                (string) $value,
                DataType::TYPE_STRING
            );

            return true;
        }

        // Hindari teks dari data pengguna diperlakukan sebagai rumus Excel.
        if (
            is_string($value)
            && preg_match('/^[=+\-@]/u', $value) === 1
        ) {
            $cell->setValueExplicit(
                $value,
                DataType::TYPE_STRING
            );

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '065F46'],
                ],
                'alignment' => [
                    'vertical' => 'center',
                ],
            ],
        ];
    }
}