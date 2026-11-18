<?php

namespace Tests\Feature;

use App\Actions\KelolaPertemuan;
use App\Models\AuditLog;
use App\Models\KelasKuliah;
use App\Models\Pertemuan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class KoreksiRencanaPertemuanSelesaiTest extends TestCase
{
    private string $connection = 'koreksi_rencana_uji';

    private const ID_SESI = 53;

    private const ID_KELAS = 1;

    private const ID_ADMIN = 1;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Aktifkan ekstensi pdo_sqlite untuk pengujian terisolasi.');
        }

        $this->app->detectEnvironment(fn (): string => 'testing');

        config([
            'database.default' => $this->connection,
            'database.connections.'.$this->connection => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge($this->connection);

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge($this->connection);
        } finally {
            parent::tearDown();
        }
    }

    public function test_admin_dapat_mengoreksi_tanggal_rencana_sesi_selesai_tanpa_mengubah_aktual_dan_presensi(): void
    {
        $presensiSebelum = DB::table('presensi')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $daftarSebelum = (array) DB::table('presensi_pertemuan')->first();
        $auditLama = DB::table('audit_log')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();

        $hasil = $this->ubahRencana([
            'tanggal' => '2026-11-06',
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
            'alasan' => 'Koreksi kesalahan input tanggal perencanaan. Perkuliahan sebenarnya telah dilaksanakan pada tanggal 6 November 2026, tetapi sesi dan presensi baru dimasukkan ke sistem pada tanggal 12 November 2026.',
        ]);

        $this->assertSame(Pertemuan::SELESAI, $hasil->status);
        $this->assertSame(4, $hasil->revisi);

        $sesi = Pertemuan::query()->findOrFail(self::ID_SESI);

        $this->assertSame('2026-11-06 13:30', $sesi->mulai_rencana->setTimezone('Asia/Makassar')->format('Y-m-d H:i'));
        $this->assertSame('2026-11-06 15:00', $sesi->selesai_rencana->setTimezone('Asia/Makassar')->format('Y-m-d H:i'));

        // Rencana boleh berbeda hari dengan waktu aktual (tidak wajib mencakup aktual).
        $this->assertTrue($sesi->mulai_rencana->lt($sesi->mulai_aktual));

        // Waktu aktual tidak berubah sama sekali.
        $this->assertSame('2026-11-12 11:39:04', $sesi->mulai_aktual->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-12 11:41:36', $sesi->selesai_aktual->format('Y-m-d H:i:s'));

        // Presensi tidak berubah (jumlah, status, waktu pencatatan).
        $presensiSesudah = DB::table('presensi')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $this->assertSame($presensiSebelum, $presensiSesudah);
        $this->assertCount(8, $presensiSesudah);
        $this->assertSame($daftarSebelum, (array) DB::table('presensi_pertemuan')->first());

        // Riwayat audit lama tetap utuh.
        $auditLamaSesudah = DB::table('audit_log')->where('versi_entitas', '<=', 3)->orderBy('id')
            ->get()->map(fn ($row) => (array) $row)->all();
        $this->assertSame($auditLama, $auditLamaSesudah);

        // Audit koreksi baru tercatat lengkap.
        $audit = AuditLog::query()
            ->where('entitas', 'pertemuan')->where('entitas_id', self::ID_SESI)
            ->where('aksi', 'ubah')->orderByDesc('id')->first();

        $this->assertNotNull($audit);
        $this->assertSame(self::ID_ADMIN, $audit->pelaku_id);
        $this->assertSame(4, $audit->versi_entitas);
        $this->assertSame('2026-11-12 11:10:00', $audit->sebelum['mulai_rencana']);
        $this->assertSame('2026-11-06 05:30:00', $audit->sesudah['mulai_rencana']);
        $this->assertSame('2026-11-12 11:39:04', $audit->sesudah['mulai_aktual']);
        $this->assertStringContainsString('Koreksi kesalahan input tanggal', (string) $audit->alasan);
        $this->assertNotNull($audit->waktu);
    }

    public function test_edit_pertemuan_terjadwal_tetap_berjalan(): void
    {
        $this->jadikanStatus('terjadwal');

        $hasil = $this->ubahRencana([
            'tanggal' => '2026-10-11',
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
        ]);

        $this->assertSame(Pertemuan::TERJADWAL, $hasil->status);
        $this->assertSame('2026-10-11 13:30', $hasil->mulai_rencana->setTimezone('Asia/Makassar')->format('Y-m-d H:i'));
        $this->assertNull($hasil->mulai_aktual);
        $this->assertNull($hasil->selesai_aktual);
    }

    public function test_edit_pertemuan_berlangsung_tetap_ditolak(): void
    {
        $this->jadikanStatus('berlangsung');

        $this->harapkanGagal('Tindakan tidak sesuai dengan status pertemuan saat ini.', 'pertemuan');
    }

    public function test_edit_pertemuan_batal_ditolak_sampai_dipulihkan(): void
    {
        $this->jadikanStatus('batal');

        $this->harapkanGagal('Tindakan tidak sesuai dengan status pertemuan saat ini.', 'pertemuan');

        $pulih = app(KelolaPertemuan::class)->execute('pulihkan', self::ID_ADMIN, [
            'versi_pertemuan' => $this->token(),
            'alasan' => 'Pemulihan sesi setelah pembatalan keliru.',
            'konfirmasi' => '1',
        ], Pertemuan::query()->findOrFail(self::ID_SESI));

        $this->assertSame(Pertemuan::TERJADWAL, $pulih->status);

        $ubah = $this->ubahRencana([
            'tanggal' => '2026-10-11',
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
        ]);

        $this->assertSame(Pertemuan::TERJADWAL, $ubah->status);
    }

    public function test_kelas_atau_periode_ditutup_tetap_mengunci_edit_rencana(): void
    {
        DB::table('periode_akademik')->where('id', 1)->update(['status' => 'arsip']);

        $sesi = Pertemuan::query()->with('kelasKuliah.rombel.periodeAkademik')->findOrFail(self::ID_SESI);
        $this->assertFalse($sesi->dapatDiubah());

        $this->harapkanGagal('Kelas/periode harus terbuka', 'pertemuan');
    }

    public function test_validasi_jam_dan_periode_tetap_berlaku(): void
    {
        $this->harapkanGagal('Tanggal/jam tidak valid', 'tanggal', [
            'jam_mulai' => '15:00',
            'jam_selesai' => '13:30',
        ]);

        $this->harapkanGagal('harus berada di dalam periode akademik', 'tanggal', [
            'tanggal' => '2027-06-01',
        ]);
    }

    private function ubahRencana(array $override = []): Pertemuan
    {
        $data = array_merge([
            'versi_pertemuan' => $this->token(),
            'alasan' => 'Koreksi kesalahan input tanggal perencanaan.',
            'konfirmasi' => '1',
            'pengajar_kelas_id' => 1,
            'jenis' => 'kuliah',
            'topik' => 'pengertian undang undang',
            'rencana' => null,
            'tanggal' => '2026-11-06',
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
            'metode' => 'daring',
            'lokasi' => null,
            'jadwal_kuliah_id' => null,
            'aksi_tautan' => 'pertahankan',
        ], $override);

        return app(KelolaPertemuan::class)->execute(
            'ubah',
            self::ID_ADMIN,
            $data,
            Pertemuan::query()->findOrFail(self::ID_SESI)
        );
    }

    private function harapkanGagal(string $pesan, string $field, array $override = []): void
    {
        try {
            $this->ubahRencana($override);
            $this->fail('Perubahan seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
            $this->assertStringContainsString($pesan, implode(' ', $exception->errors()[$field]));
        }
    }

    private function token(): string
    {
        return KelasKuliah::query()
            ->with(['rombel.periodeAkademik', 'pengajarKelas', 'jadwalKuliah', 'pertemuan'])
            ->findOrFail(self::ID_KELAS)
            ->versiPertemuan();
    }

    private function jadikanStatus(string $status): void
    {
        $kolom = match ($status) {
            'terjadwal' => [
                'status' => 'terjadwal',
                'mulai_aktual' => null,
                'selesai_aktual' => null,
                'dibatalkan_at' => null,
                'realisasi' => null,
            ],
            'berlangsung' => [
                'status' => 'berlangsung',
                'mulai_aktual' => '2026-11-12 11:39:04',
                'selesai_aktual' => null,
                'dibatalkan_at' => null,
                'realisasi' => null,
            ],
            'batal' => [
                'status' => 'batal',
                'mulai_aktual' => null,
                'selesai_aktual' => null,
                'dibatalkan_at' => '2026-11-12 11:42:00',
                'realisasi' => null,
            ],
        };

        DB::table('pertemuan')->where('id', self::ID_SESI)->update($kolom);
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
            $table->string('status')->default('aktif');
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
        });

        Schema::create('dosen', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('kode_dosen')->unique();
            $table->string('nidn')->nullable();
            $table->string('gelar')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });

        Schema::create('program_studi', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('jenjang');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('periode_akademik', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->unsignedSmallInteger('tahun_mulai');
            $table->string('jenis');
            $table->date('mulai');
            $table->date('selesai');
            $table->dateTime('krs_mulai')->nullable();
            $table->dateTime('krs_selesai')->nullable();
            $table->string('status')->default('persiapan');
            $table->timestamps();
        });

        Schema::create('kurikulum', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_studi_id');
            $table->string('kode');
            $table->string('nama');
            $table->unsignedSmallInteger('tahun_berlaku');
            $table->string('status')->default('draf');
            $table->timestamps();
        });

        Schema::create('paket_semester', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kurikulum_id');
            $table->smallInteger('semester_studi');
            $table->smallInteger('versi');
            $table->string('nama');
            $table->string('status')->default('draf');
            $table->timestamps();
        });

        Schema::create('rombel', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('periode_akademik_id');
            $table->unsignedBigInteger('paket_semester_id');
            $table->string('kode');
            $table->smallInteger('kapasitas')->nullable();
            $table->timestamps();
        });

        Schema::create('kelas_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('rombel_id');
            $table->unsignedBigInteger('detail_paket_id');
            $table->string('kode');
            $table->string('nama_mk_snapshot');
            $table->decimal('sks_snapshot', 4, 1);
            $table->string('status')->default('persiapan');
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamp('diaktifkan_at')->nullable();
            $table->timestamp('diselesaikan_at')->nullable();
            $table->timestamp('diarsipkan_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajar_kelas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('dosen_id');
            $table->string('peran');
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('diaktifkan_at');
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
            $table->date('berlaku_mulai');
            $table->date('berlaku_selesai');
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
            $table->unsignedBigInteger('pengajar_kelas_id');
            $table->unsignedSmallInteger('nomor');
            $table->string('jenis');
            $table->string('topik');
            $table->text('rencana')->nullable();
            $table->text('realisasi')->nullable();
            $table->dateTime('mulai_rencana');
            $table->dateTime('selesai_rencana');
            $table->dateTime('mulai_aktual')->nullable();
            $table->dateTime('selesai_aktual')->nullable();
            $table->string('metode');
            $table->string('lokasi')->nullable();
            $table->text('tautan_pertemuan')->nullable();
            $table->json('jadwal_snapshot')->nullable();
            $table->json('pengajar_snapshot');
            $table->string('status')->default('terjadwal');
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('dibatalkan_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pelaku_id')->nullable();
            $table->string('entitas');
            $table->unsignedBigInteger('entitas_id');
            $table->unsignedInteger('versi_entitas')->nullable();
            $table->string('aksi');
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->text('alasan')->nullable();
            $table->dateTime('waktu');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('presensi_pertemuan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pertemuan_id')->unique();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->string('status')->default('terbuka');
            $table->unsignedInteger('jumlah_peserta');
            $table->unsignedBigInteger('dibuka_oleh');
            $table->dateTime('dibuka_at');
            $table->unsignedBigInteger('ditutup_oleh')->nullable();
            $table->dateTime('ditutup_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
        });

        Schema::create('presensi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('presensi_pertemuan_id');
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('detail_krs_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->json('peserta_snapshot');
            $table->string('status')->default('belum_dicatat');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->dateTime('dicatat_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
        });
    }

    private function buatData(): void
    {
        $now = '2026-11-12 11:14:16';

        DB::table('roles')->insert([
            ['id' => 1, 'kode' => 'admin_akademik', 'nama' => 'Admin Akademik', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'kode' => 'dosen', 'nama' => 'Dosen', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('users')->insert([
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@contoh.test', 'password_hash' => 'hash', 'nama' => 'Admin Akademik', 'telepon' => null, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'username' => 'dosen', 'email' => 'dosen@contoh.test', 'password_hash' => 'hash', 'nama' => 'Muhammad Rafly', 'telepon' => null, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('user_roles')->insert([
            ['user_id' => 1, 'role_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 2, 'role_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('dosen')->insert([
            'id' => 11, 'user_id' => 2, 'kode_dosen' => 'DSN-002', 'nidn' => null,
            'gelar' => 'S.Pd.I., M.M.', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('program_studi')->insert([
            'id' => 1, 'kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam', 'jenjang' => 'S1',
            'aktif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('periode_akademik')->insert([
            'id' => 1, 'kode' => '2026-GANJIL', 'tahun_mulai' => 2026, 'jenis' => 'ganjil',
            'mulai' => '2026-09-01', 'selesai' => '2027-01-31', 'krs_mulai' => null, 'krs_selesai' => null,
            'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('kurikulum')->insert([
            'id' => 1, 'program_studi_id' => 1, 'kode' => 'KUR-2026', 'nama' => 'Kurikulum 2026',
            'tahun_berlaku' => 2026, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('paket_semester')->insert([
            'id' => 1, 'kurikulum_id' => 1, 'semester_studi' => 1, 'versi' => 1,
            'nama' => 'Paket Semester 1', 'status' => 'diterbitkan', 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('rombel')->insert([
            'id' => 1, 'periode_akademik_id' => 1, 'paket_semester_id' => 1, 'kode' => 'PAI111-A',
            'kapasitas' => 30, 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('kelas_kuliah')->insert([
            'id' => self::ID_KELAS, 'rombel_id' => 1, 'detail_paket_id' => 1, 'kode' => 'PAI111-A',
            'nama_mk_snapshot' => 'Pendidikan Kewarganegaraan', 'sks_snapshot' => 2.0,
            'status' => 'aktif', 'revisi' => 1, 'diaktifkan_at' => '2026-09-01 00:00:00',
            'diselesaikan_at' => null, 'diarsipkan_at' => null, 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('pengajar_kelas')->insert([
            'id' => 1, 'kelas_kuliah_id' => self::ID_KELAS, 'dosen_id' => 11, 'peran' => 'koordinator',
            'aktif' => true, 'revisi' => 1, 'diaktifkan_at' => '2026-09-01 00:00:00',
            'dinonaktifkan_at' => null, 'koordinator_aktif' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('pertemuan')->insert([
            'id' => self::ID_SESI,
            'kelas_kuliah_id' => self::ID_KELAS,
            'jadwal_kuliah_id' => null,
            'pengajar_kelas_id' => 1,
            'nomor' => 6,
            'jenis' => 'kuliah',
            'topik' => 'pengertian undang undang',
            'rencana' => null,
            'realisasi' => 'pngertian kewarganegaraan',
            'mulai_rencana' => '2026-11-12 11:10:00',
            'selesai_rencana' => '2026-11-12 14:15:00',
            'mulai_aktual' => '2026-11-12 11:39:04',
            'selesai_aktual' => '2026-11-12 11:41:36',
            'metode' => 'daring',
            'lokasi' => null,
            'tautan_pertemuan' => null,
            'jadwal_snapshot' => null,
            'pengajar_snapshot' => json_encode([
                'pengajar_kelas_id' => 1,
                'revisi_penugasan' => 1,
                'dosen_id' => 11,
                'kode_dosen' => 'DSN-002',
                'nama' => 'Muhammad Rafly',
                'gelar' => 'S.Pd.I., M.M.',
                'peran' => 'koordinator',
            ]),
            'status' => 'selesai',
            'revisi' => 3,
            'dibatalkan_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Riwayat audit lama yang harus tetap utuh.
        DB::table('audit_log')->insert([
            'pelaku_id' => 1,
            'entitas' => 'pertemuan',
            'entitas_id' => self::ID_SESI,
            'versi_entitas' => 1,
            'aksi' => 'buat',
            'sebelum' => null,
            'sesudah' => json_encode(['mulai_rencana' => '2026-11-12 11:10:00', 'status' => 'terjadwal']),
            'alasan' => 'pengertian undang undang',
            'waktu' => '2026-11-12 11:14:16',
            'created_at' => '2026-11-12 11:14:16',
        ]);

        DB::table('presensi_pertemuan')->insert([
            'id' => 100,
            'pertemuan_id' => self::ID_SESI,
            'kelas_kuliah_id' => self::ID_KELAS,
            'status' => 'ditutup',
            'jumlah_peserta' => 8,
            'dibuka_oleh' => 2,
            'dibuka_at' => '2026-11-12 11:39:14',
            'ditutup_oleh' => 2,
            'ditutup_at' => '2026-11-12 11:39:52',
            'revisi' => 2,
            'created_at' => '2026-11-12 11:39:14',
            'updated_at' => '2026-11-12 11:39:52',
        ]);

        $baris = [];
        for ($i = 0; $i < 8; $i++) {
            $baris[] = [
                'id' => 200 + $i,
                'presensi_pertemuan_id' => 100,
                'kelas_kuliah_id' => self::ID_KELAS,
                'detail_krs_id' => 12 + ($i * 12),
                'mahasiswa_id' => $i + 1,
                'peserta_snapshot' => json_encode(['nim' => '260101000'.$i, 'nama' => 'Mahasiswa '.$i]),
                'status' => 'hadir',
                'catatan' => null,
                'dicatat_oleh' => 2,
                'dicatat_at' => '2026-11-12 11:39:52',
                'revisi' => 2,
                'created_at' => '2026-11-12 11:39:14',
                'updated_at' => '2026-11-12 11:39:53',
            ];
        }
        DB::table('presensi')->insert($baris);
    }
}
