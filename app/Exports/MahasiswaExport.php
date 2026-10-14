<?php

namespace App\Exports;

use App\Models\Mahasiswa;
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

class MahasiswaExport extends DefaultValueBinder implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomValueBinder
{
    private string $kata;

    private ?string $statusAkun;

    public function __construct(array $filter = [])
    {
        $this->kata = trim((string) ($filter['q'] ?? ''));

        $status = (string) ($filter['status_akun'] ?? '');

        $this->statusAkun = in_array(
            $status,
            ['aktif', 'nonaktif'],
            true
        ) ? $status : null;
    }

    public function query(): Builder
    {
        return Mahasiswa::query()
            ->with([
                'user:id,nama,email,status',
                'riwayatAktif.kurikulum.programStudi',
            ])
            ->when($this->kata !== '', function (Builder $query): void {
                $kata = $this->kata;

                $query->where(function (Builder $pencarian) use ($kata): void {
                    $pencarian
                        ->where('nim', 'like', '%' . $kata . '%')
                        ->orWhereHas('user', function (Builder $akun) use ($kata): void {
                            $akun->where(function (Builder $identitas) use ($kata): void {
                                $identitas
                                    ->where('nama', 'like', '%' . $kata . '%')
                                    ->orWhere('email', 'like', '%' . $kata . '%');
                            });
                        });
                });
            })
            ->when($this->statusAkun !== null, function (Builder $query): void {
                $status = $this->statusAkun;

                $query->whereHas(
                    'user',
                    fn (Builder $akun) => $akun->where('status', $status)
                );
            })
            ->orderBy('nim')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Email',
            'Program Studi Aktif',
            'Status Akun',
        ];
    }

    public function map(mixed $mahasiswa): array
    {
        return [
            (string) $mahasiswa->nim,
            (string) ($mahasiswa->user?->nama ?? ''),
            (string) ($mahasiswa->user?->email ?? ''),
            (string) (
                $mahasiswa->riwayatAktif?->kurikulum?->programStudi?->nama
                ?? 'Belum ada riwayat aktif'
            ),
            $mahasiswa->user?->status === 'aktif'
                ? 'Aktif'
                : 'Nonaktif',
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        // Paksa NIM menjadi teks agar tidak berubah menjadi angka.
        if ($cell->getColumn() === 'A' && $value !== null) {
            $cell->setValueExplicit(
                (string) $value,
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
