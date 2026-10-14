<?php

namespace App\Exports;

use App\Models\DetailKrs;
use App\Models\Krs;
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

class KrsExport extends DefaultValueBinder implements
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
        return DetailKrs::query()
            ->with([
                'krs.registrasiSemester.riwayatStudi.mahasiswa.user',
                'krs.registrasiSemester.riwayatStudi.kurikulum.programStudi',
                'krs.registrasiSemester.periodeAkademik',
                'krs.registrasiSemester.rombel',
                'krs.details.kelasKuliah',
                'kelasKuliah.detailPaket.kurikulumMataKuliah.mataKuliah',
            ])
            ->whereHas('krs', function (Builder $query): void {
                $kata = trim((string) ($this->filter['q'] ?? ''));

                if ($kata !== '') {
                    $query->whereHas(
                        'registrasiSemester.riwayatStudi.mahasiswa',
                        function (Builder $mahasiswa) use ($kata): void {
                            $mahasiswa->where(
                                'nim',
                                'like',
                                '%' . $kata . '%'
                            )->orWhereHas(
                                'user',
                                fn (Builder $user) => $user->where(
                                    'nama',
                                    'like',
                                    '%' . $kata . '%'
                                )
                            );
                        }
                    );
                }

                if (! empty($this->filter['periode_id'])) {
                    $periodeId = $this->filter['periode_id'];

                    $query->whereHas(
                        'registrasiSemester',
                        fn (Builder $registrasi) => $registrasi->where(
                            'periode_akademik_id',
                            $periodeId
                        )
                    );
                }

                if (! empty($this->filter['program_studi_id'])) {
                    $programStudiId = $this->filter['program_studi_id'];

                    $query->whereHas(
                        'registrasiSemester.riwayatStudi.kurikulum',
                        fn (Builder $kurikulum) => $kurikulum->where(
                            'program_studi_id',
                            $programStudiId
                        )
                    );
                }

                if (! empty($this->filter['status'])) {
                    $query->where('status', $this->filter['status']);
                }
            })
            ->orderBy('krs_id')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Periode Akademik',
            'Kode Mata Kuliah',
            'Nama Mata Kuliah',
            'SKS Mata Kuliah',
            'Kode Kelas',
            'Rombel',
            'Status KRS',
            'Status Detail Mata Kuliah',
            'Total SKS KRS',
        ];
    }

    public function map(mixed $detail): array
    {
        $krs = $detail->krs;
        $registrasi = $krs?->registrasiSemester;
        $riwayat = $registrasi?->riwayatStudi;
        $mahasiswa = $riwayat?->mahasiswa;
        $kelas = $detail->kelasKuliah;
        $mataKuliah = $kelas?->detailPaket?->kurikulumMataKuliah?->mataKuliah;

        return [
            (string) ($mahasiswa?->nim ?? ''),
            (string) ($mahasiswa?->user?->nama ?? ''),
            (string) ($riwayat?->kurikulum?->programStudi?->nama ?? ''),
            (string) ($registrasi?->periodeAkademik?->kode ?? ''),
            (string) ($mataKuliah?->kode ?? ''),
            (string) ($kelas?->nama_mk_snapshot ?? $mataKuliah?->nama ?? ''),
            $kelas?->sks_snapshot !== null
                ? (float) $kelas->sks_snapshot
                : '',
            (string) ($kelas?->kode ?? ''),
            (string) ($registrasi?->rombel?->kode ?? ''),
            Krs::STATUS[$krs?->status ?? ''] ?? ($krs?->status ?? ''),
            DetailKrs::STATUS[$detail->status] ?? $detail->status,
            $krs !== null ? (float) $krs->totalSks() : '',
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        // Pertahankan NIM sebagai teks, termasuk angka nol di awal.
        if ($cell->getColumn() === 'A' && $value !== null) {
            $cell->setValueExplicit(
                (string) $value,
                DataType::TYPE_STRING
            );

            return true;
        }

        // Cegah teks pengguna diperlakukan sebagai rumus Excel.
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
