<?php

namespace App\Http\Controllers\Portal;

use App\Actions\KelolaBerkas;
use App\Http\Controllers\Controller;
use App\Http\Requests\PortalBiodataMahasiswaRequest;
use App\Models\Berkas;
use App\Models\Mahasiswa;
use App\Models\MahasiswaAlamat;
use App\Models\MahasiswaBiodata;
use App\Models\MahasiswaBiodataBerkas;
use App\Models\MahasiswaKeluarga;
use App\Models\MahasiswaPendidikanAsal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\RiwayatStudi;


class PortalBiodataMahasiswaController extends Controller
{
    /**
     * Menampilkan halaman biodata mahasiswa.
     */
    public function show(Request $request): View
    {
        $user = Auth::user();

        abort_unless($user, 401);

        $mahasiswa = Mahasiswa::query()
            ->where('user_id', $user->id)
            ->with([
                'biodata',
                'alamatBiodata',
                'pendidikanAsal',
                'dataKeluarga',
                'berkasBiodata.berkas',
            ])
            ->firstOrFail();

        $riwayatAktif = $mahasiswa->riwayatStudi()
            ->where('status', RiwayatStudi::AKTIF)
            ->with([
                'kurikulum.programStudi',
                'periodeMulai',
                'registrasiSemester.rombel',
            ])
            ->first();

        return view('portal.biodata.show', [
            'user' => $user,
            'mahasiswa' => $mahasiswa,
            'biodata' => $mahasiswa->biodata,
            'alamatBiodata' => $mahasiswa->alamatBiodata,
            'pendidikanAsal' => $mahasiswa->pendidikanAsal,
            'dataKeluarga' => $mahasiswa->dataKeluarga,
            'berkasBiodata' => $mahasiswa->berkasBiodata,
            'riwayatAktif' => $riwayatAktif,
        ]);
    }

    public function edit(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $mahasiswa = Mahasiswa::query()
            ->where('user_id', $user->id)
            ->with([
                'biodata',
                'alamatBiodata',
                'pendidikanAsal',
                'dataKeluarga',
                'berkasBiodata.berkas',
            ])
            ->firstOrFail();

        return view('portal.biodata.edit', [
            'mahasiswa' => $mahasiswa,
            'biodata' => $mahasiswa->biodata,
            'alamatBiodata' => $mahasiswa->alamatBiodata,
            'pendidikanAsal' => $mahasiswa->pendidikanAsal,
            'dataKeluarga' => $mahasiswa->dataKeluarga,
            'berkasBiodata' => $mahasiswa->berkasBiodata,
        ]);
    }

    public function update(PortalBiodataMahasiswaRequest $request, KelolaBerkas $unggahBerkas): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $mahasiswa = Mahasiswa::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $data = $request->validated();

        DB::transaction(function () use ($mahasiswa, $data): void {
            $mahasiswa->biodata()->updateOrCreate(
                [],
                [
                    'agama' => $data['agama'] ?? null,
                    'suku' => $data['suku'] ?? null,
                    'kewarganegaraan' => $data['kewarganegaraan'] ?? null,
                    'nik' => $data['nik'] ?? null,
                    'nomor_kk' => $data['nomor_kk'] ?? null,
                    'npwp' => $data['npwp'] ?? null,
                    'nomor_kps' => $data['nomor_kps'] ?? null,
                    'golongan_darah' => $data['golongan_darah'] ?? null,
                    'status_nikah' => $data['status_nikah'] ?? null,
                    'ukuran_jas_almamater' => $data['ukuran_jas_almamater'] ?? null,
                    'nomor_hp' => $data['nomor_hp'] ?? null,
                    'email_pribadi' => $data['email_pribadi'] ?? null,
                    'pekerjaan' => $data['pekerjaan'] ?? null,
                    'instansi_pekerjaan' => $data['instansi_pekerjaan'] ?? null,
                    'nomor_rekening' => $data['nomor_rekening'] ?? null,
                    'nama_rekening' => $data['nama_rekening'] ?? null,
                    'nama_bank' => $data['nama_bank'] ?? null,
                    'berat_badan' => $data['berat_badan'] ?? null,
                    'tinggi_badan' => $data['tinggi_badan'] ?? null,
                    'penghasilan' => $data['penghasilan'] ?? null,
                    'transportasi' => $data['transportasi'] ?? null,
                ]
            );

            $mahasiswa->alamatBiodata()->updateOrCreate(
                [],
                [
                    'alamat_ktp' => $data['alamat_ktp'] ?? null,
                    'rt_ktp' => $data['rt_ktp'] ?? null,
                    'rw_ktp' => $data['rw_ktp'] ?? null,
                    'dusun_ktp' => $data['dusun_ktp'] ?? null,
                    'desa_kelurahan_ktp' => $data['desa_kelurahan_ktp'] ?? null,
                    'provinsi_ktp' => $data['provinsi_ktp'] ?? null,
                    'kota_kabupaten_ktp' => $data['kota_kabupaten_ktp'] ?? null,
                    'kecamatan_ktp' => $data['kecamatan_ktp'] ?? null,
                    'kode_pos_ktp' => $data['kode_pos_ktp'] ?? null,
                    'domisili_sama_dengan_ktp' => (bool) ($data['domisili_sama_dengan_ktp'] ?? false),
                    'alamat_domisili' => $data['alamat_domisili'] ?? null,
                    'rt_domisili' => $data['rt_domisili'] ?? null,
                    'rw_domisili' => $data['rw_domisili'] ?? null,
                    'dusun_domisili' => $data['dusun_domisili'] ?? null,
                    'desa_kelurahan_domisili' => $data['desa_kelurahan_domisili'] ?? null,
                    'provinsi_domisili' => $data['provinsi_domisili'] ?? null,
                    'kota_kabupaten_domisili' => $data['kota_kabupaten_domisili'] ?? null,
                    'kecamatan_domisili' => $data['kecamatan_domisili'] ?? null,
                    'kode_pos_domisili' => $data['kode_pos_domisili'] ?? null,
                    'status_tinggal' => $data['status_tinggal'] ?? null,
                ]
            );

            $mahasiswa->pendidikanAsal()->updateOrCreate(
                [],
                [
                    'jenis_pendidikan_asal' => $data['jenis_pendidikan_asal'] ?? null,
                    'perguruan_tinggi' => $data['perguruan_tinggi'] ?? null,
                    'program_studi_asal' => $data['program_studi_asal'] ?? null,
                    'ipk' => $data['ipk'] ?? null,
                    'sks_diakui' => $data['sks_diakui'] ?? null,
                    'nomor_ijazah' => $data['nomor_ijazah'] ?? null,
                    'nisn' => $data['nisn'] ?? null,
                ]
            );

            $keluarga = $data['keluarga'] ?? [];

            foreach (['ayah', 'ibu', 'wali'] as $jenis) {
                if (array_key_exists($jenis, $keluarga) && is_array($keluarga[$jenis])) {
                    $row = $keluarga[$jenis];

                    $payload = [
                        'nama_lengkap' => $row['nama_lengkap'] ?? null,
                        'nik' => $row['nik'] ?? null,
                        'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                        'status_hidup' => $row['status_hidup'] ?? null,
                        'status_kekerabatan' => $row['status_kekerabatan'] ?? null,
                        'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? null,
                        'pekerjaan' => $row['pekerjaan'] ?? null,
                        'penghasilan' => $row['penghasilan'] ?? null,
                        'alamat' => $row['alamat'] ?? null,
                        'nomor_telepon' => $row['nomor_telepon'] ?? null,
                        'email' => $row['email'] ?? null,
                    ];

                    $mahasiswa->dataKeluarga()->updateOrCreate(
                        [
                            'jenis_hubungan' => $jenis,
                        ],
                        $payload
                    );
                }
            }
        });

        $jenisBerkas = [
            'ijazah_terakhir' => 'Scan Ijazah Asli',
            'transkrip' => 'Scan Transkrip Nilai',
            'ktp' => 'Scan KTP',
            'kartu_keluarga' => 'Scan Kartu Keluarga (KK)',
            'akta_kelahiran' => 'Scan Akta Kelahiran',
            'pas_foto_3x4' => 'Pas Foto 3x4',
        ];

        $jumlahBerkas = 0;

        foreach ($jenisBerkas as $jenis => $judul) {
            $file = $request->file('berkas_' . $jenis);

            if (! $file instanceof \Illuminate\Http\UploadedFile || ! $file->isValid()) {
                continue;
            }

            $berkas = $unggahBerkas->unggah($user->id, $file, [
                'upload_token' => (string) Str::uuid(),
                'label' => $judul . ' - ' . $mahasiswa->nim,
                'keterangan' => 'Berkas biodata mahasiswa ' . $mahasiswa->nim,
            ]);

            if ($berkas->status !== Berkas::TERSEDIA) {
                continue;
            }

            MahasiswaBiodataBerkas::query()->updateOrCreate(
                [
                    'mahasiswa_id' => $mahasiswa->id,
                    'jenis_berkas' => $jenis,
                    'aktif' => true,
                ],
                [
                    'berkas_id' => $berkas->id,
                ]
            );

            $jumlahBerkas++;
        }

        $pesan = 'Biodata berhasil diperbarui.';

        if ($jumlahBerkas > 0) {
            $pesan = 'Biodata berhasil diperbarui dan ' . $jumlahBerkas . ' berkas berhasil diunggah.';
        }

        return redirect()
            ->route('portal.biodata.show')
            ->with('info', $pesan);
    }
}
