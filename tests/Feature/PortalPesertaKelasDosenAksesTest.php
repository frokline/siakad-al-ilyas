<?php

namespace Tests\Feature;

use App\Models\DetailKrs;
use App\Models\KelasKuliah;
use App\Models\User;
use App\Services\AksesPesertaKelasDosen;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalPesertaKelasDosenAksesTest extends TestCase
{
    private string $koneksiLama;

    private string $koneksiUji = 'portal_peserta_dosen_uji';

    protected function setUp(): void
    {
        parent::setUp();

        $this->koneksiLama = (string) config('database.default');

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped(
                'Aktifkan pdo_sqlite untuk menjalankan pengujian.'
            );
        }

        config([
            'database.default' => $this->koneksiUji,
            'database.connections.' . $this->koneksiUji => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge($this->koneksiUji);
        DB::setDefaultConnection($this->koneksiUji);
        DB::connection($this->koneksiUji)->getPdo();

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        if (isset($this->koneksiLama)) {
            DB::disconnect($this->koneksiUji);

            config([
                'database.default' => $this->koneksiLama,
            ]);

            DB::setDefaultConnection($this->koneksiLama);
            DB::purge($this->koneksiUji);
        }

        parent::tearDown();
    }

    /**
     * Menyimpan data satu per satu agar setiap record
     * boleh mempunyai kolom nullable yang berbeda.
     */


    private function masukkan(string $tabel, array $data): void
    {
        foreach ($data as $record) {
            DB::table($tabel)->insert($record);
        }
    }

    private function buatTabel(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password_hash');
            $table->string('nama');
            $table->string('telepon')->nullable();
            $table->string('status');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('dosen', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('kode_dosen')->unique();
            $table->string('nidn')->nullable();
            $table->string('gelar')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create(
            'periode_akademik',
            function (Blueprint $table): void {
                $table->id();
                $table->string('kode')->unique();
                $table->unsignedSmallInteger('tahun_mulai');
                $table->string('jenis');
                $table->date('mulai');
                $table->date('selesai');
                $table->dateTime('krs_mulai');
                $table->dateTime('krs_selesai');
                $table->string('status');
                $table->timestamps();
            }
        );

        Schema::create('rombel', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('periode_akademik_id');
            $table->unsignedBigInteger('paket_semester_id');
            $table->string('kode');
            $table->unsignedInteger('kapasitas');
            $table->timestamps();
        });

        Schema::create('kelas_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('rombel_id');
            $table->unsignedBigInteger('detail_paket_id');
            $table->string('kode');
            $table->string('nama_mk_snapshot');
            $table->unsignedTinyInteger('sks_snapshot');
            $table->string('status');
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('diaktifkan_at')->nullable();
            $table->dateTime('diselesaikan_at')->nullable();
            $table->dateTime('diarsipkan_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajar_kelas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('dosen_id');
            $table->string('peran');
            $table->boolean('aktif');
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('diaktifkan_at')->nullable();
            $table->dateTime('dinonaktifkan_at')->nullable();
            $table->unsignedTinyInteger('koordinator_aktif')->nullable();
            $table->timestamps();
        });

        Schema::create('jadwal_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedTinyInteger('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->date('berlaku_mulai')->nullable();
            $table->date('berlaku_selesai')->nullable();
            $table->string('metode');
            $table->string('lokasi')->nullable();
            $table->text('tautan_pertemuan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('dinonaktifkan_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pertemuan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('jadwal_kuliah_id')->nullable();
            $table->unsignedBigInteger('pengajar_kelas_id')->nullable();
            $table->unsignedInteger('nomor');
            $table->string('jenis')->default('kuliah');
            $table->string('topik')->nullable();
            $table->text('rencana')->nullable();
            $table->text('realisasi')->nullable();
            $table->dateTime('mulai_rencana')->nullable();
            $table->dateTime('selesai_rencana')->nullable();
            $table->dateTime('mulai_aktual')->nullable();
            $table->dateTime('selesai_aktual')->nullable();
            $table->string('metode')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('tautan_pertemuan')->nullable();
            $table->text('jadwal_snapshot')->nullable();
            $table->text('pengajar_snapshot')->nullable();
            $table->string('status')->default('terjadwal');
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('dibatalkan_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mahasiswa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('nim')->unique();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('riwayat_studi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('kurikulum_id');
            $table->unsignedSmallInteger('angkatan');
            $table->unsignedBigInteger('periode_mulai_id');
            $table->unsignedBigInteger('periode_akhir_id')->nullable();
            $table->unsignedBigInteger('dosen_pa_id')->nullable();
            $table->string('status');
            $table->unsignedTinyInteger('aktif_guard')->nullable();
            $table->timestamps();
        });

        Schema::create(
            'registrasi_semester',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('riwayat_studi_id');
                $table->unsignedBigInteger('rombel_id');
                $table->unsignedBigInteger('periode_akademik_id');
                $table->unsignedTinyInteger('semester_studi');
                $table->string('status');
                $table->text('alasan_status')->nullable();
                $table->dateTime('penempatan_dikunci_at')->nullable();
                $table->unsignedInteger('revisi')->default(1);
                $table->timestamps();
            }
        );

        Schema::create('krs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('registrasi_semester_id');
            $table->string('status');
            $table->unsignedInteger('versi')->default(1);
            $table->dateTime('diajukan_at')->nullable();
            $table->unsignedBigInteger('disahkan_oleh')->nullable();
            $table->dateTime('disahkan_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_krs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('krs_id');
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->string('status');
            $table->dateTime('aktif_at')->nullable();
            $table->dateTime('batal_at')->nullable();
            $table->timestamps();
        });
        Schema::create(
            'presensi_pertemuan',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('pertemuan_id');
                $table->unsignedBigInteger('kelas_kuliah_id');
                $table->string('status');
                $table->unsignedInteger('jumlah_peserta');
                $table->unsignedBigInteger('dibuka_oleh');
                $table->dateTime('dibuka_at');
                $table->unsignedBigInteger(
                    'ditutup_oleh'
                )->nullable();
                $table->dateTime('ditutup_at')->nullable();
                $table->unsignedInteger('revisi')->default(1);
                $table->timestamps();
            }
        );

        Schema::create('presensi', function (
            Blueprint $table
        ): void {
            $table->id();
            $table->unsignedBigInteger(
                'presensi_pertemuan_id'
            );
            $table->unsignedBigInteger(
                'kelas_kuliah_id'
            );
            $table->unsignedBigInteger('detail_krs_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->json('peserta_snapshot');
            $table->string('status');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger(
                'dicatat_oleh'
            )->nullable();
            $table->dateTime('dicatat_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
        });
    }

    private function buatData(): void
    {
        $sekarang = now();

        $this->masukkan('roles', [
            [
                'id' => 1,
                'kode' => 'dosen',
                'nama' => 'Dosen',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 2,
                'kode' => 'mahasiswa',
                'nama' => 'Mahasiswa',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('users', [
            [
                'id' => 1,
                'username' => 'dosen_satu',
                'email' => 'dosen1@example.test',
                'password_hash' => 'bukan-password-asli',
                'nama' => 'Dosen Satu',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 2,
                'username' => 'dosen_dua',
                'email' => 'dosen2@example.test',
                'password_hash' => 'bukan-password-asli',
                'nama' => 'Dosen Dua',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 101,
                'username' => 'mhs_aktif',
                'email' => 'aktif@example.test',
                'password_hash' => 'bukan-password-asli',
                'nama' => 'Ahmad Peserta Aktif',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 102,
                'username' => 'mhs_riwayat',
                'email' => 'riwayat@example.test',
                'password_hash' => 'bukan-password-asli',
                'nama' => 'Budi Riwayat Peserta',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 103,
                'username' => 'mhs_lain',
                'email' => 'lain@example.test',
                'password_hash' => 'bukan-password-asli',
                'nama' => 'Citra Kelas Lain',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 104,
                'username' => 'mhs_menunggu',
                'email' => 'menunggu@example.test',
                'password_hash' => 'bukan-password-asli',
                'nama' => '<script>alert("x")</script>',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('user_roles', [
            [
                'user_id' => 1,
                'role_id' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'user_id' => 2,
                'role_id' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'user_id' => 101,
                'role_id' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'user_id' => 102,
                'role_id' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'user_id' => 103,
                'role_id' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'user_id' => 104,
                'role_id' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('dosen', [
            [
                'id' => 11,
                'user_id' => 1,
                'kode_dosen' => 'D001',
                'nidn' => '1100000001',
                'gelar' => 'M.Kom.',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 12,
                'user_id' => 2,
                'kode_dosen' => 'D002',
                'nidn' => '1100000002',
                'gelar' => 'M.Kom.',
                'status' => 'aktif',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        DB::table('periode_akademik')->insert([
            'id' => 21,
            'kode' => '2026-GANJIL',
            'tahun_mulai' => 2026,
            'jenis' => 'ganjil',
            'mulai' => '2026-09-01',
            'selesai' => '2027-01-31',
            'krs_mulai' => '2026-08-01 00:00:00',
            'krs_selesai' => '2026-08-31 23:59:59',
            'status' => 'aktif',
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ]);

        $this->masukkan('rombel', [
            [
                'id' => 31,
                'periode_akademik_id' => 21,
                'paket_semester_id' => 1,
                'kode' => 'PAI-1A',
                'kapasitas' => 30,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 32,
                'periode_akademik_id' => 21,
                'paket_semester_id' => 2,
                'kode' => 'PAI-1B',
                'kapasitas' => 30,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('kelas_kuliah', [
            [
                'id' => 41,
                'rombel_id' => 31,
                'detail_paket_id' => 1,
                'kode' => 'PAI-1A-MK01',
                'nama_mk_snapshot' => 'Mata Kuliah Satu',
                'sks_snapshot' => 3,
                'status' => 'aktif',
                'revisi' => 1,
                'diaktifkan_at' => $sekarang,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 42,
                'rombel_id' => 32,
                'detail_paket_id' => 2,
                'kode' => 'PAI-1B-MK02',
                'nama_mk_snapshot' => 'Kelas Dosen Lain',
                'sks_snapshot' => 2,
                'status' => 'aktif',
                'revisi' => 1,
                'diaktifkan_at' => $sekarang,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('pengajar_kelas', [
            [
                'id' => 51,
                'kelas_kuliah_id' => 41,
                'dosen_id' => 11,
                'peran' => 'koordinator',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => $sekarang,
                'koordinator_aktif' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 52,
                'kelas_kuliah_id' => 42,
                'dosen_id' => 12,
                'peran' => 'koordinator',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => $sekarang,
                'koordinator_aktif' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('mahasiswa', [
            [
                'id' => 201,
                'user_id' => 101,
                'nim' => '20260001',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 202,
                'user_id' => 102,
                'nim' => '20260002',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 203,
                'user_id' => 103,
                'nim' => '20260003',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 204,
                'user_id' => 104,
                'nim' => '20260004',
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('riwayat_studi', [
            [
                'id' => 301,
                'mahasiswa_id' => 201,
                'kurikulum_id' => 1,
                'angkatan' => 2026,
                'periode_mulai_id' => 21,
                'periode_akhir_id' => null,
                'dosen_pa_id' => 11,
                'status' => 'aktif',
                'aktif_guard' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 302,
                'mahasiswa_id' => 202,
                'kurikulum_id' => 1,
                'angkatan' => 2026,
                'periode_mulai_id' => 21,
                'periode_akhir_id' => 21,
                'dosen_pa_id' => 11,
                'status' => 'keluar',
                'aktif_guard' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 303,
                'mahasiswa_id' => 203,
                'kurikulum_id' => 1,
                'angkatan' => 2026,
                'periode_mulai_id' => 21,
                'periode_akhir_id' => null,
                'dosen_pa_id' => 12,
                'status' => 'aktif',
                'aktif_guard' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 304,
                'mahasiswa_id' => 204,
                'kurikulum_id' => 1,
                'angkatan' => 2026,
                'periode_mulai_id' => 21,
                'periode_akhir_id' => null,
                'dosen_pa_id' => 11,
                'status' => 'aktif',
                'aktif_guard' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('registrasi_semester', [
            [
                'id' => 401,
                'riwayat_studi_id' => 301,
                'rombel_id' => 31,
                'periode_akademik_id' => 21,
                'semester_studi' => 1,
                'status' => 'aktif',
                'revisi' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 402,
                'riwayat_studi_id' => 302,
                'rombel_id' => 31,
                'periode_akademik_id' => 21,
                'semester_studi' => 1,
                'status' => 'batal',
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 403,
                'riwayat_studi_id' => 303,
                'rombel_id' => 32,
                'periode_akademik_id' => 21,
                'semester_studi' => 1,
                'status' => 'aktif',
                'revisi' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 404,
                'riwayat_studi_id' => 304,
                'rombel_id' => 31,
                'periode_akademik_id' => 21,
                'semester_studi' => 1,
                'status' => 'aktif',
                'revisi' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('krs', [
            [
                'id' => 501,
                'registrasi_semester_id' => 401,
                'status' => 'disahkan',
                'versi' => 1,
                'diajukan_at' => $sekarang,
                'disahkan_oleh' => 1,
                'disahkan_at' => $sekarang,
                'catatan' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 502,
                'registrasi_semester_id' => 402,
                'status' => 'disahkan',
                'versi' => 1,
                'diajukan_at' => $sekarang,
                'disahkan_oleh' => 1,
                'disahkan_at' => $sekarang,
                'catatan' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 503,
                'registrasi_semester_id' => 403,
                'status' => 'disahkan',
                'versi' => 1,
                'diajukan_at' => $sekarang,
                'disahkan_oleh' => 2,
                'disahkan_at' => $sekarang,
                'catatan' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 504,
                'registrasi_semester_id' => 404,
                'status' => 'diajukan',
                'versi' => 1,
                'diajukan_at' => $sekarang,
                'disahkan_oleh' => null,
                'disahkan_at' => null,
                'catatan' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        $this->masukkan('detail_krs', [
            [
                'id' => 601,
                'krs_id' => 501,
                'kelas_kuliah_id' => 41,
                'status' => 'aktif',
                'aktif_at' => $sekarang,
                'batal_at' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 602,
                'krs_id' => 502,
                'kelas_kuliah_id' => 41,
                'status' => 'aktif',
                'aktif_at' => $sekarang,
                'batal_at' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 603,
                'krs_id' => 503,
                'kelas_kuliah_id' => 42,
                'status' => 'aktif',
                'aktif_at' => $sekarang,
                'batal_at' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 604,
                'krs_id' => 504,
                'kelas_kuliah_id' => 41,
                'status' => 'terdaftar',
                'aktif_at' => null,
                'batal_at' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);
    }

    public function test_dosen_hanya_melihat_peserta_kelasnya(): void
    {
        $user = User::findOrFail(1);
        $kelas = KelasKuliah::findOrFail(41);

        $ids = app(AksesPesertaKelasDosen::class)
            ->batasi(DetailKrs::query(), $user, $kelas)
            ->orderBy('detail_krs.id')
            ->pluck('detail_krs.id')
            ->all();

        $this->assertSame([601, 602], $ids);
    }

    public function test_jumlah_aktif_dan_riwayat_dipisahkan(): void
    {
        $user = User::findOrFail(1);
        $kelas = KelasKuliah::findOrFail(41);
        $akses = app(AksesPesertaKelasDosen::class);

        $this->assertSame(
            2,
            $akses->jumlahRiwayat($user, $kelas)
        );

        $this->assertSame(
            1,
            $akses->jumlahAktif($user, $kelas)
        );
    }

    public function test_dosen_tidak_melihat_peserta_kelas_lain(): void
    {
        $user = User::findOrFail(1);
        $kelas = KelasKuliah::findOrFail(42);

        $jumlah = app(AksesPesertaKelasDosen::class)
            ->batasi(DetailKrs::query(), $user, $kelas)
            ->count();

        $this->assertSame(0, $jumlah);
    }

    public function test_http_daftar_peserta_dapat_dicari(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.index', [
                'kelas' => 41,
                'q' => '20260001',
            ]))
            ->assertOk()
            ->assertSee('Ahmad Peserta Aktif')
            ->assertSee('20260001')
            ->assertDontSee('Budi Riwayat Peserta')
            ->assertDontSee('Citra Kelas Lain');
    }

    public function test_http_kelas_dosen_lain_ditolak(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.index', 42))
            ->assertNotFound();
    }

    public function test_pencabutan_peran_menghentikan_akses(): void
    {
        $user = User::findOrFail(1);

        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->delete();

        $this->actingAs($user)
            ->get(route('portal.dosen.peserta.index', 41))
            ->assertForbidden();
    }

    public function test_penugasan_nonaktif_menghentikan_akses(): void
    {
        DB::table('pengajar_kelas')
            ->where('id', 51)
            ->update([
                'aktif' => false,
                'dinonaktifkan_at' => now(),
                'koordinator_aktif' => null,
                'updated_at' => now(),
            ]);

        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.index', 41))
            ->assertNotFound();
    }

    public function test_nama_mahasiswa_diamankan_dari_xss(): void
    {
        DB::table('krs')
            ->where('id', 504)
            ->update([
                'status' => 'disahkan',
                'disahkan_oleh' => 1,
                'disahkan_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('detail_krs')
            ->where('id', 604)
            ->update([
                'status' => 'aktif',
                'aktif_at' => now(),
                'updated_at' => now(),
            ]);

        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.index', 41))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>', false);
    }

    public function test_http_dosen_dapat_membuka_detail_peserta_kelasnya(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 41,
                'peserta' => 601,
            ]))
            ->assertOk()
            ->assertSee('Detail peserta')
            ->assertSee('Ahmad Peserta Aktif')
            ->assertSee('20260001')
            ->assertSee('PAI-1A-MK01')
            ->assertDontSee('aktif@example.test')
            ->assertDontSee('1100000001');
    }

    public function test_http_peserta_kelas_dosen_lain_tidak_dapat_dibuka(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 41,
                'peserta' => 603,
            ]))
            ->assertNotFound();

        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 42,
                'peserta' => 603,
            ]))
            ->assertNotFound();
    }

    public function test_http_peserta_yang_belum_disahkan_tidak_dapat_dibuka(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 41,
                'peserta' => 604,
            ]))
            ->assertNotFound();
    }

    public function test_penugasan_dicabut_memblokir_detail_peserta(): void
    {
        $user = User::findOrFail(1);

        $this->actingAs($user)
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 41,
                'peserta' => 601,
            ]))
            ->assertOk();

        DB::table('pengajar_kelas')
            ->where('id', 51)
            ->update([
                'aktif' => false,
                'dinonaktifkan_at' => now(),
                'koordinator_aktif' => null,
                'updated_at' => now(),
            ]);

        $this->actingAs($user)
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 41,
                'peserta' => 601,
            ]))
            ->assertNotFound();
    }

    private function buatDataRekapPresensi(): void
    {
        $sekarang = now();

        $pertemuan = [];

        for ($nomor = 1; $nomor <= 6; $nomor++) {
            $mulai = $sekarang->copy()
                ->subDays(7 - $nomor)
                ->setTime(8, 0);

            $pertemuan[] = [
                'id' => 700 + $nomor,
                'kelas_kuliah_id' => 41,
                'jadwal_kuliah_id' => null,
                'pengajar_kelas_id' => 51,
                'nomor' => $nomor,
                'jenis' => 'kuliah',
                'topik' => 'Topik pertemuan ' . $nomor,
                'rencana' => null,
                'realisasi' => null,
                'mulai_rencana' => $mulai,
                'selesai_rencana' => $mulai
                    ->copy()
                    ->addMinutes(100),
                'mulai_aktual' => $mulai,
                'selesai_aktual' => $mulai
                    ->copy()
                    ->addMinutes(100),
                'metode' => 'luring',
                'lokasi' => 'Ruang 1',
                'tautan_pertemuan' => null,
                'jadwal_snapshot' => null,
                'pengajar_snapshot' => null,
                'status' => 'selesai',
                'revisi' => 2,
                'dibatalkan_at' => null,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        $pertemuan[] = [
            'id' => 707,
            'kelas_kuliah_id' => 42,
            'jadwal_kuliah_id' => null,
            'pengajar_kelas_id' => 52,
            'nomor' => 1,
            'jenis' => 'kuliah',
            'topik' => 'RAHASIA KELAS LAIN',
            'rencana' => null,
            'realisasi' => null,
            'mulai_rencana' => $sekarang
                ->copy()
                ->subDay()
                ->setTime(10, 0),
            'selesai_rencana' => $sekarang
                ->copy()
                ->subDay()
                ->setTime(11, 40),
            'mulai_aktual' => null,
            'selesai_aktual' => null,
            'metode' => 'luring',
            'lokasi' => 'Ruang 2',
            'tautan_pertemuan' => null,
            'jadwal_snapshot' => null,
            'pengajar_snapshot' => null,
            'status' => 'selesai',
            'revisi' => 2,
            'dibatalkan_at' => null,
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ];

        $this->masukkan('pertemuan', $pertemuan);

        $daftar = [];

        for ($nomor = 1; $nomor <= 6; $nomor++) {
            $daftar[] = [
                'id' => 800 + $nomor,
                'pertemuan_id' => 700 + $nomor,
                'kelas_kuliah_id' => 41,
                'status' => 'ditutup',
                'jumlah_peserta' => 1,
                'dibuka_oleh' => 1,
                'dibuka_at' => $sekarang->copy()->subHour(),
                'ditutup_oleh' => 1,
                'ditutup_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        $daftar[] = [
            'id' => 807,
            'pertemuan_id' => 707,
            'kelas_kuliah_id' => 42,
            'status' => 'ditutup',
            'jumlah_peserta' => 1,
            'dibuka_oleh' => 2,
            'dibuka_at' => $sekarang->copy()->subHour(),
            'ditutup_oleh' => 2,
            'ditutup_at' => $sekarang,
            'revisi' => 2,
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ];

        $this->masukkan('presensi_pertemuan', $daftar);

        $status = [
            1 => 'hadir',
            2 => 'hadir',
            3 => 'izin',
            4 => 'sakit',
            5 => 'alpa',
            6 => 'belum_dicatat',
        ];

        $presensi = [];

        foreach ($status as $nomor => $nilaiStatus) {
            $belumDicatat = $nilaiStatus === 'belum_dicatat';

            $presensi[] = [
                'id' => 900 + $nomor,
                'presensi_pertemuan_id' => 800 + $nomor,
                'kelas_kuliah_id' => 41,
                'detail_krs_id' => 601,
                'mahasiswa_id' => 201,
                'peserta_snapshot' => json_encode([
                    'nim' => '20260001',
                    'nama' => 'Ahmad Peserta Aktif',
                ], JSON_THROW_ON_ERROR),
                'status' => $nilaiStatus,
                'catatan' => $nomor === 3
                    ? '<script>alert("rekap")</script>'
                    : null,
                'dicatat_oleh' => $belumDicatat ? null : 1,
                'dicatat_at' => $belumDicatat
                    ? null
                    : $sekarang,
                'revisi' => $belumDicatat ? 1 : 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        $presensi[] = [
            'id' => 907,
            'presensi_pertemuan_id' => 807,
            'kelas_kuliah_id' => 42,
            'detail_krs_id' => 603,
            'mahasiswa_id' => 203,
            'peserta_snapshot' => json_encode([
                'nim' => '20260003',
                'nama' => 'Citra Kelas Lain',
            ], JSON_THROW_ON_ERROR),
            'status' => 'hadir',
            'catatan' => 'CATATAN RAHASIA KELAS LAIN',
            'dicatat_oleh' => 2,
            'dicatat_at' => $sekarang,
            'revisi' => 2,
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ];

        $this->masukkan('presensi', $presensi);
    }

    public function test_rekap_presensi_menghitung_semua_status(): void
    {
        $this->buatDataRekapPresensi();

        $user = User::findOrFail(1);
        $kelas = KelasKuliah::findOrFail(41);
        $peserta = DetailKrs::findOrFail(601);

        $rekap = app(
            \App\Services\RekapPresensiPesertaDosen::class
        )->ambil($user, $kelas, $peserta);

        $this->assertSame(6, $rekap['total']);
        $this->assertSame(5, $rekap['total_tercatat']);
        $this->assertSame(2, $rekap['jumlah']['hadir']);
        $this->assertSame(1, $rekap['jumlah']['izin']);
        $this->assertSame(1, $rekap['jumlah']['sakit']);
        $this->assertSame(1, $rekap['jumlah']['alpa']);
        $this->assertSame(
            1,
            $rekap['jumlah']['belum_dicatat']
        );
        $this->assertSame(
            33.33,
            $rekap['persentase_hadir']
        );
    }

    public function test_rekap_tidak_membaca_kelas_lain(): void
    {
        $this->buatDataRekapPresensi();

        $rekap = app(
            \App\Services\RekapPresensiPesertaDosen::class
        )->ambil(
            User::findOrFail(1),
            KelasKuliah::findOrFail(41),
            DetailKrs::findOrFail(601)
        );

        $this->assertSame(6, $rekap['riwayat']->count());

        $this->assertFalse(
            $rekap['riwayat']->contains(
                fn ($baris): bool =>
                    $baris->kelas_kuliah_id === 42
            )
        );
    }

    public function test_http_detail_menampilkan_rekap_presensi(): void
    {
        $this->buatDataRekapPresensi();

        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 41,
                'peserta' => 601,
            ]))
            ->assertOk()
            ->assertSee('Ringkasan presensi')
            ->assertSee('Riwayat presensi')
            ->assertSee('33,33%')
            ->assertSeeInOrder([
                'Topik pertemuan 1',
                'Topik pertemuan 2',
                'Topik pertemuan 3',
                'Topik pertemuan 4',
                'Topik pertemuan 5',
                'Topik pertemuan 6',
            ])
            ->assertDontSee('RAHASIA KELAS LAIN')
            ->assertDontSee('CATATAN RAHASIA KELAS LAIN');
    }

    public function test_catatan_presensi_diamankan_dari_xss(): void
    {
        $this->buatDataRekapPresensi();

        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.dosen.peserta.show', [
                'kelas' => 41,
                'peserta' => 601,
            ]))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>', false);
    }
}