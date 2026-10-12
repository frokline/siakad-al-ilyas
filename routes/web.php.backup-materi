<?php

use Illuminate\Support\Facades\Route;

// Middleware
use App\Http\Middleware\AuthenticateBerkas;
use App\Http\Middleware\AuthenticateKegiatan;
use App\Http\Middleware\AuthenticateKeuangan;
use App\Http\Middleware\AuthenticateMateri;
use App\Http\Middleware\AuthenticatePengumpulan;
use App\Http\Middleware\AuthenticatePresensi;
use App\Http\Middleware\AuthenticateSurat;
use App\Http\Middleware\EnsureRoleManagementAccess;

// Controllers
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\PortalSessionController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\JadwalKuliahController;
use App\Http\Controllers\JenisBiayaController;
use App\Http\Controllers\JenisSuratController;
use App\Http\Controllers\KalenderAkademikController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KelasKuliahController;
use App\Http\Controllers\KeuanganDashboardController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\KurikulumMataKuliahController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PaketSemesterController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengajarKelasController;
use App\Http\Controllers\PengumpulanController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PermohonanSuratController;
use App\Http\Controllers\PeriodeAkademikController;
use App\Http\Controllers\PertemuanController;
use App\Http\Controllers\PortalJadwalController;
use App\Http\Controllers\PortalKelasDosenController;
use App\Http\Controllers\PortalKrsController;
use App\Http\Controllers\PortalMahasiswaController;
use App\Http\Controllers\PortalPesertaKelasDosenController;
use App\Http\Controllers\PortalPresensiMahasiswaController;
use App\Http\Controllers\PortalProfilMahasiswaController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\RegistrasiSemesterController;
use App\Http\Controllers\RiwayatStudiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RombelController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| 1. LOGIN & LOGOUT (pintu masuk tunggal seluruh pengguna)
|--------------------------------------------------------------------------
*/

Route::controller(PortalSessionController::class)
    ->middleware('cache.headers:no_store;private')
    ->group(function (): void {
        Route::get('/', 'home')->name('home');
        Route::get('/login', 'create')->name('login');
        Route::post('/login', 'store')
            ->middleware('throttle:portal-login')
            ->name('login.store');
    });

Route::post('/logout', [PortalSessionController::class, 'destroy'])
    ->middleware(['auth:web', 'auth.session', 'throttle:30,1', 'cache.headers:no_store;private'])
    ->name('logout');

// Kompatibilitas alamat lama. Semua autentikasi tetap diproses oleh pintu tunggal.
Route::middleware('cache.headers:no_store;private')->group(function (): void {
    Route::redirect('/login/dosen', '/login')->name('presensi.login');
    Route::post('/login/dosen', [PortalSessionController::class, 'store'])
        ->middleware('throttle:portal-login')
        ->name('presensi.login.store');

    Route::redirect('/login/berkas', '/login')->name('berkas.login');
    Route::post('/login/berkas', [PortalSessionController::class, 'store'])
        ->middleware('throttle:portal-login')
        ->name('berkas.login.store');
});

/*
|--------------------------------------------------------------------------
| 2. PORTAL MAHASISWA & DOSEN (cukup login, tanpa pengecekan peran khusus)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'auth.session', 'cache.headers:no_store;private'])
    ->group(function (): void {

        // Dashboard mahasiswa
        Route::get('/portal-mahasiswa', [PortalMahasiswaController::class, 'index'])
            ->name('portal.mahasiswa.index');

        // KRS mahasiswa
        Route::controller(PortalKrsController::class)
            ->prefix('krs-saya')
            ->name('portal.krs.')
            ->where(['krs' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/{krs}/cetak', 'cetak')->middleware('throttle:30,1')->name('cetak');
                Route::get('/{krs}', 'show')->name('show');
            });

        // Jadwal kuliah mahasiswa
        Route::controller(PortalJadwalController::class)
            ->prefix('jadwal-saya')
            ->name('portal.jadwal.')
            ->where(['jadwalKuliah' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/{jadwalKuliah}', 'show')->name('show');
            });

        // Presensi mahasiswa
        Route::controller(PortalPresensiMahasiswaController::class)
            ->prefix('presensi-saya')
            ->name('portal.presensi.')
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/{presensi}', 'show')->whereNumber('presensi')->name('show');
            });

        // Profil mahasiswa
        Route::controller(PortalProfilMahasiswaController::class)
            ->prefix('profil-saya')
            ->name('portal.profil.')
            ->group(function (): void {
                Route::get('/', 'show')->name('show');
                Route::get('/edit', 'edit')->name('edit');
                Route::patch('/', 'update')->name('update');
            });

        // Kelas & peserta kelas milik dosen
        Route::prefix('dosen/kelas-saya')
            ->name('portal.dosen.')
            ->group(function (): void {
                Route::get('/', [PortalKelasDosenController::class, 'index'])
                    ->name('kelas.index');

                Route::get('/{kelas}', [PortalKelasDosenController::class, 'show'])
                    ->whereNumber('kelas')
                    ->name('kelas.show');

                Route::get('/{kelas}/peserta', [PortalPesertaKelasDosenController::class, 'index'])
                    ->whereNumber('kelas')
                    ->name('peserta.index');

                Route::get('/{kelas}/peserta/{peserta}', [PortalPesertaKelasDosenController::class, 'show'])
                    ->whereNumber('kelas')
                    ->whereNumber('peserta')
                    ->name('peserta.show');
            });
    });

/*
|--------------------------------------------------------------------------
| 3. ADMIN AKADEMIK  (URL: /admin/...  |  nama: admin....)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:web', 'auth.session', 'cache.headers:no_store;private'])
    ->group(function (): void {

        // Dashboard  ->  /admin
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Peran
        Route::controller(RoleController::class)->prefix('roles')->name('roles.')
            ->middleware(['can:kelola-peran', EnsureRoleManagementAccess::class])
            ->where(['role' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/{role}', 'show')->name('show');
                Route::get('/{role}/edit', 'edit')->name('edit');
                Route::patch('/{role}', 'update')->middleware('throttle:30,1')->name('update');
            });

        // Pengguna
        Route::controller(UserController::class)->prefix('users')->name('users.')
            ->middleware('can:kelola-pengguna')
            ->where(['user' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{user}', 'show')->name('show');
                Route::get('/{user}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{user}', 'update')->name('update');
                    Route::delete('/{user}', 'destroy')->name('destroy');
                });
            });

        // Program Studi
        Route::controller(ProgramStudiController::class)->prefix('program-studi')->name('program-studi.')
            ->middleware('can:kelola-program-studi')
            ->where(['programStudi' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{programStudi}', 'show')->name('show');
                Route::get('/{programStudi}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{programStudi}', 'update')->name('update');
                });
            });

        // Periode Akademik
        Route::controller(PeriodeAkademikController::class)->prefix('periode-akademik')->name('periode-akademik.')
            ->middleware('can:kelola-periode-akademik')
            ->where(['periodeAkademik' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{periodeAkademik}', 'show')->name('show');
                Route::get('/{periodeAkademik}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{periodeAkademik}', 'update')->name('update');
                });
            });

        // Kurikulum
        Route::controller(KurikulumController::class)->prefix('kurikulum')->name('kurikulum.')
            ->middleware('can:kelola-kurikulum')
            ->where(['kurikulum' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{kurikulum}', 'show')->name('show');
                Route::get('/{kurikulum}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{kurikulum}', 'update')->name('update');
                });
            });

        // Susunan Mata Kuliah Kurikulum
        Route::controller(KurikulumMataKuliahController::class)
            ->prefix('kurikulum/{kurikulum}/mata-kuliah')
            ->name('kurikulum.mata-kuliah.')
            ->middleware('can:kelola-kurikulum')
            ->where(['kurikulum' => '[0-9]+', 'detail' => '[0-9]+'])
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{detail}', 'show')->name('show');
                Route::get('/{detail}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{detail}', 'update')->name('update');
                    Route::delete('/{detail}', 'destroy')->name('destroy');
                });
            });

        // Mata Kuliah
        Route::controller(MataKuliahController::class)->prefix('mata-kuliah')->name('mata-kuliah.')
            ->middleware('can:kelola-mata-kuliah')
            ->where(['mataKuliah' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{mataKuliah}', 'show')->name('show');
                Route::get('/{mataKuliah}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{mataKuliah}', 'update')->name('update');
                });
            });

        // Dosen
        Route::controller(DosenController::class)->prefix('dosen')->name('dosen.')
            ->middleware('can:kelola-dosen')
            ->where(['dosen' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{dosen}', 'show')->name('show');
                Route::get('/{dosen}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{dosen}', 'update')->name('update');
                });
            });

        // Riwayat Studi
        Route::controller(RiwayatStudiController::class)->prefix('riwayat-studi')->name('riwayat-studi.')
            ->middleware('can:kelola-riwayat-studi')
            ->where(['riwayatStudi' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{riwayatStudi}', 'show')->name('show');
                Route::get('/{riwayatStudi}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{riwayatStudi}', 'update')->name('update');
                });
            });

        // Mahasiswa
        Route::controller(MahasiswaController::class)->prefix('mahasiswa')->name('mahasiswa.')
            ->middleware('can:kelola-mahasiswa')
            ->where(['mahasiswa' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{mahasiswa}', 'show')->name('show');
                Route::get('/{mahasiswa}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{mahasiswa}', 'update')->name('update');
                });
            });

        // Paket Semester
        Route::controller(PaketSemesterController::class)->prefix('paket-semester')->name('paket-semester.')
            ->middleware('can:kelola-paket-semester')
            ->where(['paketSemester' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{paketSemester}', 'show')->name('show');
                Route::get('/{paketSemester}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{paketSemester}', 'update')->name('update');
                    Route::post('/{paketSemester}/terbitkan', 'terbitkan')->name('terbitkan');
                    Route::post('/{paketSemester}/arsipkan', 'arsipkan')->name('arsipkan');
                });
            });

        // Rombel
        Route::controller(RombelController::class)->prefix('rombel')->name('rombel.')
            ->middleware('can:kelola-rombel')
            ->where(['rombel' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{rombel}', 'show')->name('show');
                Route::get('/{rombel}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{rombel}', 'update')->name('update');
                });
            });

        // Registrasi Semester
        Route::controller(RegistrasiSemesterController::class)->prefix('registrasi-semester')->name('registrasi-semester.')
            ->middleware('can:kelola-registrasi-semester')
            ->where(['registrasiSemester' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{registrasiSemester}', 'show')->name('show');
                Route::get('/{registrasiSemester}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{registrasiSemester}', 'update')->name('update');
                });
            });

        // Kelas Kuliah
        Route::controller(KelasKuliahController::class)->prefix('kelas-kuliah')->name('kelas-kuliah.')
            ->middleware('can:kelola-kelas-kuliah')
            ->where(['kelasKuliah' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{kelasKuliah}', 'show')->name('show');
                Route::get('/{kelasKuliah}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{kelasKuliah}', 'update')->name('update');
                });
            });

        // KRS
        Route::controller(KrsController::class)->prefix('krs')->name('krs.')
            ->middleware('can:kelola-krs')
            ->where(['krs' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{krs}/cetak', 'cetak')->name('cetak');
                Route::get('/{krs}/edit', 'edit')->name('edit');
                Route::get('/{krs}', 'show')->name('show');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{krs}', 'update')->name('update');
                    Route::post('/{krs}/ajukan', 'ajukan')->name('ajukan');
                    Route::post('/{krs}/kembalikan', 'kembalikan')->name('kembalikan');
                    Route::post('/{krs}/sahkan', 'sahkan')->name('sahkan');
                    Route::post('/{krs}/revisi', 'revisi')->name('revisi');
                    Route::post('/{krs}/pulihkan', 'pulihkan')->name('pulihkan');
                    Route::post('/{krs}/batalkan', 'batalkan')->name('batalkan');
                });
            });

        // Pengajar Kelas
        Route::controller(PengajarKelasController::class)->prefix('pengajar-kelas')->name('pengajar-kelas.')
            ->middleware('can:kelola-pengajar-kelas')
            ->where(['pengajarKelas' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{pengajarKelas}/edit', 'edit')->name('edit');
                Route::get('/{pengajarKelas}', 'show')->name('show');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{pengajarKelas}', 'update')->name('update');
                });
            });

        // Jadwal Kuliah
        Route::controller(JadwalKuliahController::class)->prefix('jadwal-kuliah')->name('jadwal-kuliah.')
            ->middleware('can:kelola-jadwal-kuliah')
            ->where(['jadwalKuliah' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{jadwalKuliah}/edit', 'edit')->name('edit');
                Route::get('/{jadwalKuliah}', 'show')->name('show');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{jadwalKuliah}', 'update')->name('update');
                    Route::post('/{jadwalKuliah}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
                });
            });

        // Pertemuan
        Route::controller(PertemuanController::class)->prefix('pertemuan')->name('pertemuan.')
            ->middleware('can:kelola-pertemuan')
            ->where(['pertemuan' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{pertemuan}/edit', 'edit')->name('edit');
                Route::get('/{pertemuan}', 'show')->name('show');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{pertemuan}', 'update')->name('update');
                    Route::post('/{pertemuan}/mulai', 'mulai')->name('mulai');
                    Route::post('/{pertemuan}/selesai', 'selesai')->name('selesai');
                    Route::post('/{pertemuan}/batalkan', 'batalkan')->name('batalkan');
                    Route::post('/{pertemuan}/pulihkan', 'pulihkan')->name('pulihkan');
                });
            });
    });

/*
|--------------------------------------------------------------------------
| 4. PRESENSI (dosen/pengajar)
|--------------------------------------------------------------------------
*/
Route::prefix('presensi')->name('presensi.')
    ->middleware([AuthenticatePresensi::class . ':web', 'auth.session', 'cache.headers:no_store;private'])
    ->group(function (): void {
        Route::post('/logout', [PortalSessionController::class, 'destroy'])
            ->middleware('throttle:30,1')
            ->name('logout');

        Route::controller(PresensiController::class)
            ->middleware('can:akses-presensi')
            ->where(['pertemuan' => '[0-9]+', 'presensi' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/{pertemuan}', 'show')->name('show');
                Route::get('/{pertemuan}/peserta/{presensi}/edit', 'edit')->name('edit');
                Route::get('/{pertemuan}/peserta/{presensi}/audit', 'audit')->name('audit');

                Route::middleware('throttle:presensi-tulis')->group(function (): void {
                    Route::post('/{pertemuan}/siapkan', 'siapkan')->name('siapkan');
                    Route::patch('/{pertemuan}/peserta/{presensi}', 'catat')->name('catat');
                    Route::patch('/{pertemuan}/peserta/{presensi}/koreksi', 'koreksi')->name('koreksi');
                    Route::post('/{pertemuan}/tutup', 'tutup')->name('tutup');
                    Route::post('/{pertemuan}/mulai', 'mulai')->name('mulai');
                    Route::post('/{pertemuan}/selesai', 'selesai')->name('selesai');
                });
            });
    });

/*
|--------------------------------------------------------------------------
| 5. BERKAS
|--------------------------------------------------------------------------
*/
Route::prefix('berkas')->name('berkas.')
    ->middleware([AuthenticateBerkas::class . ':web', 'auth.session', 'cache.headers:no_store;private'])
    ->group(function (): void {
        Route::post('/logout', [PortalSessionController::class, 'destroy'])
            ->middleware('throttle:30,1')
            ->name('logout');

        Route::controller(BerkasController::class)
            ->middleware('can:akses-berkas')
            ->where(['berkas' => '[0-9]+'])
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->middleware('throttle:berkas-upload')->name('store');
                Route::get('/{berkas}', 'show')->name('show');
                Route::get('/{berkas}/edit', 'edit')->name('edit');
                Route::get('/{berkas}/unduh', 'unduh')->middleware(['signed', 'throttle:30,1'])->name('unduh');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::patch('/{berkas}', 'update')->name('update');
                    Route::post('/{berkas}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
                    Route::post('/{berkas}/pulihkan', 'pulihkan')->name('pulihkan');
                    Route::post('/{berkas}/tautan-unduh', 'tautan')->name('tautan');
                });
            });
    });


/*
|--------------------------------------------------------------------------
| 7. KEGIATAN (tugas, latihan, UTS, UAS)
|--------------------------------------------------------------------------
*/
Route::prefix('kegiatan')->name('kegiatan.')
    ->middleware([
        AuthenticateKegiatan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-kegiatan',
    ])
    ->controller(KegiatanController::class)
    ->where(['kegiatan' => '[0-9]+', 'lampiran' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/kelas', 'kelas')->name('kelas');
        Route::get('/create', 'create')->name('create');
        Route::get('/{kegiatan}', 'show')->name('show');
        Route::get('/{kegiatan}/edit', 'edit')->name('edit');
        Route::get('/{kegiatan}/lampiran/{lampiran}/unduh', 'unduh')
            ->middleware(['signed', 'throttle:30,1'])
            ->name('unduh');
        Route::middleware('throttle:30,1')->group(function (): void {
            Route::post('/', 'store')->name('store');
            Route::patch('/{kegiatan}', 'update')->name('update');
            Route::post('/{kegiatan}/tutup', 'tutup')->name('tutup');
            Route::post('/{kegiatan}/buka-kembali', 'bukaKembali')->name('bukaKembali');
            Route::post('/{kegiatan}/perpanjang', 'perpanjang')->name('perpanjang');
            Route::post('/{kegiatan}/arsipkan', 'arsipkan')->name('arsipkan');
            Route::post('/{kegiatan}/pulihkan', 'pulihkan')->name('pulihkan');
            Route::post('/{kegiatan}/lampiran/{lampiran}/tautan', 'tautan')->name('tautan');
        });
    });

/*
|--------------------------------------------------------------------------
| 8. PENGUMPULAN (jawaban mahasiswa atas kegiatan)
|--------------------------------------------------------------------------
*/
Route::prefix('pengumpulan')->name('pengumpulan.')
    ->middleware([
        AuthenticatePengumpulan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-pengumpulan',
    ])
    ->controller(PengumpulanController::class)
    ->where(['kegiatan' => '[0-9]+', 'pengumpulan' => '[0-9]+', 'lampiran' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/kegiatan/{kegiatan}/saya', 'saya')->name('saya');
        Route::get('/kegiatan/{kegiatan}/rekap', 'rekap')->name('rekap');
        Route::post('/kegiatan/{kegiatan}/jawaban', 'store')
            ->middleware('throttle:20,1')
            ->name('store');

        Route::get('/{pengumpulan}', 'show')->name('show');
        Route::get('/{pengumpulan}/edit', 'edit')->name('edit');
        Route::patch('/{pengumpulan}', 'update')->middleware('throttle:20,1')->name('update');
        Route::delete('/{pengumpulan}', 'destroy')->middleware('throttle:10,1')->name('destroy');

        Route::post('/{pengumpulan}/lampiran/{lampiran}/tautan', 'tautan')
            ->middleware('throttle:30,1')
            ->name('tautan');
        Route::get('/{pengumpulan}/lampiran/{lampiran}/unduh', 'unduh')
            ->middleware(['signed', 'throttle:30,1'])
            ->name('unduh');
    });

/*
|--------------------------------------------------------------------------
| 9. KEUANGAN
|--------------------------------------------------------------------------
*/

// Dashboard Admin Keuangan
Route::get('/keuangan', [KeuanganDashboardController::class, 'index'])
    ->middleware(['auth:web', 'auth.session', 'cache.headers:no_store;private'])
    ->name('keuangan.dashboard');

// Jenis Biaya
Route::prefix('keuangan/jenis-biaya')->name('keuangan.jenis-biaya.')
    ->middleware([
        AuthenticateKeuangan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-jenis-biaya',
    ])
    ->controller(JenisBiayaController::class)
    ->where(['jenisBiaya' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{jenisBiaya}', 'show')->name('show');
        Route::get('/{jenisBiaya}/edit', 'edit')->name('edit');
        Route::middleware('throttle:30,1')->group(function (): void {
            Route::post('/', 'store')->name('store');
            Route::patch('/{jenisBiaya}', 'update')->name('update');
            Route::post('/{jenisBiaya}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
            Route::post('/{jenisBiaya}/aktifkan', 'aktifkan')->name('aktifkan');
        });
    });

// Tagihan
Route::prefix('tagihan')->name('tagihan.')
    ->middleware([
        AuthenticateKeuangan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-tagihan',
    ])
    ->controller(TagihanController::class)
    ->where(['tagihan' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{tagihan}', 'show')->name('show');
        Route::get('/{tagihan}/edit', 'edit')->name('edit');
        Route::middleware('throttle:30,1')->group(function (): void {
            Route::post('/', 'store')->name('store');
            Route::patch('/{tagihan}', 'update')->name('update');
            Route::post('/{tagihan}/terbitkan', 'terbitkan')->name('terbitkan');
            Route::post('/{tagihan}/batalkan', 'batalkan')->name('batalkan');
            Route::post('/{tagihan}/buka-draf', 'bukaDraf')->name('buka-draf');
        });
    });

// Pembayaran
Route::prefix('pembayaran')->name('pembayaran.')
    ->middleware([
        AuthenticateKeuangan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-pembayaran',
    ])
    ->controller(PembayaranController::class)
    ->where(['tagihan' => '[0-9]+', 'pembayaran' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/tagihan/{tagihan}/create', 'create')->name('create');
        Route::get('/{pembayaran}', 'show')->name('show');
        Route::get('/{pembayaran}/bukti', 'tautan')->middleware('throttle:30,1')->name('tautan');
        Route::get('/{pembayaran}/unduh', 'unduh')->middleware(['signed', 'throttle:30,1'])->name('unduh');
        Route::middleware('throttle:10,1')->group(function (): void {
            Route::post('/tagihan/{tagihan}', 'store')->name('store');
            Route::post('/{pembayaran}/batalkan', 'batalkan')->name('batalkan');
            Route::post('/{pembayaran}/terima', 'terima')->name('terima');
            Route::post('/{pembayaran}/tolak', 'tolak')->name('tolak');
        });
    });

/*
|--------------------------------------------------------------------------
| 10. LAYANAN SURAT, KALENDER, PENGUMUMAN, NOTIFIKASI
|--------------------------------------------------------------------------
*/

// Jenis Surat (admin)
Route::prefix('admin/jenis-surat')->name('admin.jenis-surat.')
    ->middleware([
        AuthenticateSurat::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-jenis-surat',
    ])
    ->controller(JenisSuratController::class)
    ->where(['jenisSurat' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{jenisSurat}', 'show')->name('show');
        Route::get('/{jenisSurat}/edit', 'edit')->name('edit');
        Route::middleware('throttle:30,1')->group(function (): void {
            Route::post('/', 'store')->name('store');
            Route::patch('/{jenisSurat}', 'update')->name('update');
            Route::post('/{jenisSurat}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
            Route::post('/{jenisSurat}/aktifkan', 'aktifkan')->name('aktifkan');
        });
    });

// Permohonan Surat
Route::prefix('surat')->name('surat.')
    ->middleware([
        AuthenticateSurat::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-surat',
    ])
    ->controller(PermohonanSuratController::class)
    ->where(['permohonanSurat' => '[0-9]+', 'bagian' => 'lampiran|hasil'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->middleware('throttle:10,1')->name('store');
        Route::get('/{permohonanSurat}', 'show')->name('show');
        Route::post('/{permohonanSurat}/tindakan', 'tindakan')->middleware('throttle:10,1')->name('tindakan');
        Route::get('/{permohonanSurat}/dokumen/{bagian}', 'tautan')->middleware('throttle:30,1')->name('tautan');
        Route::get('/{permohonanSurat}/unduh/{bagian}', 'unduh')->middleware(['signed', 'throttle:30,1'])->name('unduh');
    });

// Kalender Akademik
Route::prefix('kalender-akademik')->name('kalender.')
    ->middleware([
        AuthenticateSurat::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-kalender',
    ])
    ->controller(KalenderAkademikController::class)
    ->where(['kalenderAkademik' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{kalenderAkademik}', 'show')->name('show');
        Route::get('/{kalenderAkademik}/edit', 'edit')->name('edit');
        Route::middleware('throttle:30,1')->group(function (): void {
            Route::post('/', 'store')->name('store');
            Route::patch('/{kalenderAkademik}', 'update')->name('update');
            Route::post('/{kalenderAkademik}/tindakan', 'tindakan')->name('tindakan');
        });
    });

// Pengumuman
Route::prefix('pengumuman')->name('pengumuman.')
    ->middleware([
        AuthenticateSurat::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-pengumuman',
    ])
    ->controller(PengumumanController::class)
    ->where(['pengumuman' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{pengumuman}', 'show')->name('show');
        Route::get('/{pengumuman}/edit', 'edit')->name('edit');
        Route::middleware('throttle:30,1')->group(function (): void {
            Route::post('/', 'store')->name('store');
            Route::patch('/{pengumuman}', 'update')->name('update');
            Route::post('/{pengumuman}/tindakan', 'tindakan')->name('tindakan');
        });
    });

// Notifikasi
Route::prefix('notifikasi')->name('notifikasi.')
    ->middleware([
        AuthenticateSurat::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-notifikasi',
    ])
    ->controller(NotifikasiController::class)
    ->where(['notifikasi' => '[0-9]+'])
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::middleware('throttle:60,1')->group(function (): void {
            Route::post('/baca-halaman', 'bacaHalaman')->name('baca-halaman');
            Route::post('/{notifikasi}/baca', 'baca')->name('baca');
            Route::post('/{notifikasi}/buka', 'buka')->name('buka');
        });
    });
