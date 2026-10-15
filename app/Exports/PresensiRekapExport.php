<?php

namespace App\Exports;

use App\Models\KelasKuliah;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\PresensiPertemuan;
use App\Services\AksesPresensi;
use App\Models\User;
use Carbon\CarbonImmutable;
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

class PresensiRekapExport extends DefaultValueBinder implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomValueBinder
{
    public function __construct(
        private readonly User $user,
        private readonly AksesPresensi $akses,
        private readonly array $filter = [],
        private readonly string $zona = 'Asia/Makassar',
    ) {
    }

    public function query(): Builder
    {
        $query = Presensi::query()
            ->join(
                'presensi_pertemuan as pp',
                'pp.id',
                '=',
                'presensi.presensi_pertemuan_id'
            )
            ->join(
                'pertemuan as p',
                'p.id',
                '=',
                'pp.pertemuan_id'
            )
            ->join(
                'kelas_kuliah as kk',
                'kk.id',
                '=',
                'presensi.kelas_kuliah_id'
            )
            ->selectRaw("
                presensi.kelas_kuliah_id as kelas_id,
                presensi.mahasiswa_id as mahasiswa_id,
                MAX(JSON_UNQUOTE(JSON_EXTRACT(
                    presensi.peserta_snapshot, '$.nim'
                ))) as nim_snapshot,
                MAX(JSON_UNQUOTE(JSON_EXTRACT(
                    presensi.peserta_snapshot, '$.nama'
                ))) as nama_snapshot,
                kk.kode as kode_kelas,
                kk.nama_mk_snapshot as nama_mata_kuliah,
                COUNT(DISTINCT pp.pertemuan_id) as jumlah_pertemuan,
                SUM(CASE WHEN presensi.status = 'hadir'
                    THEN 1 ELSE 0 END) as jumlah_hadir,
                SUM(CASE WHEN presensi.status = 'izin'
                    THEN 1 ELSE 0 END) as jumlah_izin,
                SUM(CASE WHEN presensi.status = 'sakit'
                    THEN 1 ELSE 0 END) as jumlah_sakit,
                SUM(CASE WHEN presensi.status = 'alpa'
                    THEN 1 ELSE 0 END) as jumlah_alpa,
                SUM(CASE WHEN presensi.status = 'belum_dicatat'
                    THEN 1 ELSE 0 END) as jumlah_belum_dicatat
            ")
            ->where('p.status', Pertemuan::SELESAI)
            ->where('pp.status', PresensiPertemuan::DITUTUP)
            ->whereIn(
                'presensi.kelas_kuliah_id',
                $this->akses
                    ->batasiKelas(KelasKuliah::query(), $this->user)
                    ->select('kelas_kuliah.id')
            );

        // Rekap hanya menghitung pertemuan yang telah selesai.
        // Filter status lain tidak boleh memasukkan sesi yang belum selesai.
        if (
            ! empty($this->filter['status'])
            && $this->filter['status'] !== Pertemuan::SELESAI
        ) {
            $query->whereRaw('1 = 0');
        }

        if (! empty($this->filter['tanggal'])) {
            $awal = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $this->filter['tanggal'],
                $this->zona
            )->utc();

            $akhir = $awal
                ->setTimezone($this->zona)
                ->addDay()
                ->utc();

            $query->where('p.mulai_rencana', '>=', $awal)
                ->where('p.mulai_rencana', '<', $akhir);
        }

        return $query
            ->groupBy(
                'presensi.kelas_kuliah_id',
                'presensi.mahasiswa_id',
                'kk.kode',
                'kk.nama_mk_snapshot'
            )
            ->orderBy('kk.kode')
            ->orderBy('presensi.mahasiswa_id');
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Kode Kelas',
            'Mata Kuliah',
            'Jumlah Pertemuan',
            'Hadir',
            'Izin',
            'Sakit',
            'Alpa',
            'Belum Dicatat',
            'Persentase Hadir (%)',
        ];
    }

    public function map(mixed $baris): array
    {
        $jumlahPertemuan = (int) $baris->jumlah_pertemuan;
        $hadir = (int) $baris->jumlah_hadir;

        $persentase = $jumlahPertemuan > 0
            ? round(($hadir / $jumlahPertemuan) * 100, 2)
            : 0;

        return [
            (string) ($baris->nim_snapshot ?? ''),
            (string) ($baris->nama_snapshot ?? ''),
            (string) ($baris->kode_kelas ?? ''),
            (string) ($baris->nama_mata_kuliah ?? ''),
            $jumlahPertemuan,
            $hadir,
            (int) $baris->jumlah_izin,
            (int) $baris->jumlah_sakit,
            (int) $baris->jumlah_alpa,
            (int) $baris->jumlah_belum_dicatat,
            $persentase,
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        // NIM selalu teks agar format aslinya tidak berubah di Excel.
        if ($cell->getColumn() === 'A' && $value !== null) {
            $cell->setValueExplicit(
                (string) $value,
                DataType::TYPE_STRING
            );

            return true;
        }

        // Lindungi teks yang diawali karakter rumus Excel.
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
