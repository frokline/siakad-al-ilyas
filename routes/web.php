<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureRoleManagementAccess;
use App\Http\Middleware\AuthenticatePresensi;

// Import Controllers
use App\Http\Controllers\Auth\AdminSessionController;
use App\Http\Controllers\Auth\PresensiSessionController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\PeriodeAkademikController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\RiwayatStudiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaketSemesterController;
use App\Http\Controllers\RombelController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\RegistrasiSemesterController;
use App\Http\Controllers\KelasKuliahController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\PengajarKelasController;
use App\Http\Controllers\JadwalKuliahController;
use App\Http\Controllers\PertemuanController;
use App\Http\Controllers\PresensiController;

// Halaman awal
Route::redirect('/', '/admin/roles')->name('home');

// Login admin
Route::controller(AdminSessionController::class)
    ->middleware(['guest:web', 'cache.headers:no_store;private'])
    ->group(function (): void {
        Route::get('/login', 'create')->name('login');
        Route::post('/login', 'store')->middleware('throttle:admin-login')->name('login.store');
    });

// Logout
Route::post('/logout', [AdminSessionController::class, 'destroy'])
    ->middleware(['auth:web', 'auth.session', 'throttle:30,1', 'cache.headers:no_store;private'])
    ->name('logout');


// ==========================================================
// GRUP UTAMA ADMIN
// (Semua route di dalam ini otomatis memiliki URL /admin/... 
// dan name admin....)
// ==========================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:web', 'auth.session', 'cache.headers:no_store;private'])
    ->group(function (): void {

        // Peran
        Route::controller(RoleController::class)->prefix('roles')->name('roles.')
            ->middleware(['can:kelola-peran', EnsureRoleManagementAccess::class])
            ->where(['role' => '[0-9]+'])->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/{role}', 'show')->name('show');
                Route::get('/{role}/edit', 'edit')->name('edit');
                Route::patch('/{role}', 'update')->middleware('throttle:30,1')->name('update');
            });

        // Pengguna
        Route::controller(UserController::class)->prefix('users')->name('users.')
            ->middleware('can:kelola-pengguna')->where(['user' => '[0-9]+'])->group(function (): void {
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
            ->middleware('can:kelola-program-studi')->where(['programStudi' => '[0-9]+'])->group(function (): void {
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
            ->middleware('can:kelola-periode-akademik')->where(['periodeAkademik' => '[0-9]+'])->group(function (): void {
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
            ->middleware('can:kelola-kurikulum')->where(['kurikulum' => '[0-9]+'])->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{kurikulum}', 'show')->name('show');
                Route::get('/{kurikulum}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{kurikulum}', 'update')->name('update');
                });
            });

        // Mata Kuliah
        Route::controller(MataKuliahController::class)->prefix('mata-kuliah')->name('mata-kuliah.')
            ->middleware('can:kelola-mata-kuliah')->where(['mataKuliah' => '[0-9]+'])->group(function (): void {
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
            ->middleware('can:kelola-dosen')->where(['dosen' => '[0-9]+'])->group(function (): void {
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
            ->middleware('can:kelola-riwayat-studi')->where(['riwayatStudi' => '[0-9]+'])->group(function (): void {
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
            ->middleware('can:kelola-mahasiswa')->where(['mahasiswa' => '[0-9]+'])->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{mahasiswa}', 'show')->name('show');
                Route::get('/{mahasiswa}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{mahasiswa}', 'update')->name('update');
                });
            });

        // Paket Semester (Diperbaiki: Dibuang kata 'admin' dari prefix & name karena sudah mewarisi grup induk)
        Route::controller(PaketSemesterController::class)->prefix('paket-semester')->name('paket-semester.')
            ->middleware('can:kelola-paket-semester')->where(['paketSemester' => '[0-9]+'])->group(function (): void {
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

        // Rombel (Diperbaiki)
        Route::controller(RombelController::class)->prefix('rombel')->name('rombel.')
            ->middleware('can:kelola-rombel')->where(['rombel' => '[0-9]+'])->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{rombel}', 'show')->name('show');
                Route::get('/{rombel}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{rombel}', 'update')->name('update');
                });
            });

        // Registrasi Semester (Diperbaiki)
        Route::controller(RegistrasiSemesterController::class)->prefix('registrasi-semester')->name('registrasi-semester.')
            ->middleware('can:kelola-registrasi-semester')->where(['registrasiSemester' => '[0-9]+'])->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{registrasiSemester}', 'show')->name('show');
                Route::get('/{registrasiSemester}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{registrasiSemester}', 'update')->name('update');
                });
            });

        // Kelas Kuliah (Diperbaiki)
        Route::controller(KelasKuliahController::class)->prefix('kelas-kuliah')->name('kelas-kuliah.')
            ->middleware('can:kelola-kelas-kuliah')->where(['kelasKuliah' => '[0-9]+'])->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{kelasKuliah}', 'show')->name('show');
                Route::get('/{kelasKuliah}/edit', 'edit')->name('edit');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{kelasKuliah}', 'update')->name('update');
                });
            });

        // KRS (Diperbaiki)
        Route::controller(KrsController::class)->prefix('krs')->name('krs.')
            ->middleware('can:kelola-krs')->where(['krs' => '[0-9]+'])->group(function (): void {
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

        // Pengajar Kelas (Diperbaiki)
        Route::controller(PengajarKelasController::class)->prefix('pengajar-kelas')->name('pengajar-kelas.')
            ->middleware('can:kelola-pengajar-kelas')->where(['pengajarKelas' => '[0-9]+'])->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{pengajarKelas}/edit', 'edit')->name('edit');
                Route::get('/{pengajarKelas}', 'show')->name('show');
                Route::middleware('throttle:30,1')->group(function (): void {
                    Route::post('/', 'store')->name('store');
                    Route::patch('/{pengajarKelas}', 'update')->name('update');
                });
            });

        // Jadwal Kuliah (Diperbaiki)
        Route::controller(JadwalKuliahController::class)->prefix('jadwal-kuliah')->name('jadwal-kuliah.')
            ->middleware('can:kelola-jadwal-kuliah')->where(['jadwalKuliah' => '[0-9]+'])->group(function (): void {
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

        // Pertemuan (Diperbaiki)
        Route::controller(PertemuanController::class)->prefix('pertemuan')->name('pertemuan.')
            ->middleware('can:kelola-pertemuan')->where(['pertemuan' => '[0-9]+'])->group(function (): void {
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

// ==========================================================
// GRUP PRESENSI (Di luar admin)
// ==========================================================
Route::middleware('cache.headers:no_store;private')->group(function (): void {
    Route::get('/login/dosen', [PresensiSessionController::class, 'create'])->name('presensi.login');
    Route::post('/login/dosen', [PresensiSessionController::class, 'store'])->middleware('throttle:presensi-login')->name('presensi.login.store');
});

Route::prefix('presensi')->name('presensi.')
    ->middleware([AuthenticatePresensi::class . ':web', 'auth.session', 'cache.headers:no_store;private'])
    ->group(function (): void {
        Route::post('/logout', [PresensiSessionController::class, 'destroy'])
            ->middleware('throttle:30,1')->name('logout');

        Route::controller(PresensiController::class)
            ->middleware('can:akses-presensi')->where(['pertemuan' => '[0-9]+', 'presensi' => '[0-9]+'])
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
// Tambahkan di level terluar routes/web.php, sesudah seluruh group yang sudah ada.
\Illuminate\Support\Facades\Route::middleware('cache.headers:no_store;private')->group(function (): void {
    \Illuminate\Support\Facades\Route::get('/login/berkas', [\App\Http\Controllers\Auth\BerkasSessionController::class, 'create'])->name('berkas.login');
    \Illuminate\Support\Facades\Route::post('/login/berkas', [\App\Http\Controllers\Auth\BerkasSessionController::class, 'store'])
        ->middleware('throttle:berkas-login')->name('berkas.login.store');
});
\Illuminate\Support\Facades\Route::prefix('berkas')->name('berkas.')
    ->middleware([\App\Http\Middleware\AuthenticateBerkas::class . ':web', 'auth.session', 'cache.headers:no_store;private'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::post('/logout', [\App\Http\Controllers\Auth\BerkasSessionController::class, 'destroy'])
            ->middleware('throttle:30,1')->name('logout');
        \Illuminate\Support\Facades\Route::controller(\App\Http\Controllers\BerkasController::class)
            ->middleware('can:akses-berkas')->where(['berkas' => '[0-9]+'])->group(function (): void {
                \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
                \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
                \Illuminate\Support\Facades\Route::post('/', 'store')->middleware('throttle:berkas-upload')->name('store');
                \Illuminate\Support\Facades\Route::get('/{berkas}', 'show')->name('show');
                \Illuminate\Support\Facades\Route::get('/{berkas}/edit', 'edit')->name('edit');
                \Illuminate\Support\Facades\Route::get('/{berkas}/unduh', 'unduh')->middleware(['signed', 'throttle:30,1'])->name('unduh');
                \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
                    \Illuminate\Support\Facades\Route::patch('/{berkas}', 'update')->name('update');
                    \Illuminate\Support\Facades\Route::post('/{berkas}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
                    \Illuminate\Support\Facades\Route::post('/{berkas}/pulihkan', 'pulihkan')->name('pulihkan');
                    \Illuminate\Support\Facades\Route::post('/{berkas}/tautan-unduh', 'tautan')->name('tautan');
                });
            });
    });
// Tambahkan SEKALI di routes/web.php, di luar seluruh grup admin/presensi/berkas.
\Illuminate\Support\Facades\Route::prefix('materi')->name('materi.')
    ->middleware([
        \App\Http\Middleware\AuthenticateMateri::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-materi'
    ])
    ->controller(\App\Http\Controllers\MateriController::class)
    ->where(['materi' => '[0-9]+', 'lampiran' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/kelas', 'kelas')->name('kelas');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{materi}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{materi}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::get('/{materi}/lampiran/{lampiran}/unduh', 'unduh')
            ->middleware(['signed', 'throttle:30,1'])->name('unduh');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{materi}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{materi}/terbitkan', 'terbitkan')->name('terbitkan');
            \Illuminate\Support\Facades\Route::post('/{materi}/tarik', 'tarik')->name('tarik');
            \Illuminate\Support\Facades\Route::post('/{materi}/arsipkan', 'arsipkan')->name('arsipkan');
            \Illuminate\Support\Facades\Route::post('/{materi}/pulihkan', 'pulihkan')->name('pulihkan');
            \Illuminate\Support\Facades\Route::post('/{materi}/lampiran/{lampiran}/tautan', 'tautan')->name('tautan');
        });
    });
// Tambahkan SEKALI di routes/web.php, di luar seluruh grup admin/presensi/berkas.
\Illuminate\Support\Facades\Route::prefix('kegiatan')->name('kegiatan.')
    ->middleware([
        \App\Http\Middleware\AuthenticateKegiatan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-kegiatan'
    ])
    ->controller(\App\Http\Controllers\KegiatanController::class)
    ->where(['kegiatan' => '[0-9]+', 'lampiran' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/kelas', 'kelas')->name('kelas');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{kegiatan}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{kegiatan}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::get('/{kegiatan}/lampiran/{lampiran}/unduh', 'unduh')
            ->middleware(['signed', 'throttle:30,1'])->name('unduh');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{kegiatan}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{kegiatan}/terbitkan', 'terbitkan')->name('terbitkan');
            \Illuminate\Support\Facades\Route::post('/{kegiatan}/tutup', 'tutup')->name('tutup');
            \Illuminate\Support\Facades\Route::post('/{kegiatan}/buka-kembali', 'bukaKembali')->name('bukaKembali');
            \Illuminate\Support\Facades\Route::post('/{kegiatan}/perpanjang', 'perpanjang')->name('perpanjang');
            \Illuminate\Support\Facades\Route::post('/{kegiatan}/arsipkan', 'arsipkan')->name('arsipkan');
            \Illuminate\Support\Facades\Route::post('/{kegiatan}/pulihkan', 'pulihkan')->name('pulihkan');
            \Illuminate\Support\Facades\Route::post('/{kegiatan}/lampiran/{lampiran}/tautan', 'tautan')->name('tautan');
        });
    });
// Tempel SEKALI di luar seluruh grup admin, kegiatan, berkas, dan materi.
\Illuminate\Support\Facades\Route::prefix('pengumpulan')->name('pengumpulan.')
    ->middleware([
        \App\Http\Middleware\AuthenticatePengumpulan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-pengumpulan'
    ])
    ->controller(\App\Http\Controllers\PengumpulanController::class)
    ->where(['kegiatan' => '[0-9]+', 'pengumpulan' => '[0-9]+', 'lampiran' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/kegiatan/{kegiatan}/saya', 'saya')->name('saya');
        \Illuminate\Support\Facades\Route::get('/kegiatan/{kegiatan}/rekap', 'rekap')->name('rekap');
        \Illuminate\Support\Facades\Route::get('/{pengumpulan}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{pengumpulan}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::get('/{pengumpulan}/lampiran/{lampiran}/unduh', 'unduh')
            ->middleware(['signed', 'throttle:30,1'])->name('unduh');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/kegiatan/{kegiatan}/draf', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{pengumpulan}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{pengumpulan}/kirim', 'kirim')->name('kirim');
            \Illuminate\Support\Facades\Route::post('/{pengumpulan}/lampiran/{lampiran}/tautan', 'tautan')->name('tautan');
        });
    });
// Tempel SEKALI di routes/web.php, DI LUAR seluruh grup route lama.
\Illuminate\Support\Facades\Route::prefix('keuangan/jenis-biaya')->name('keuangan.jenis-biaya.')
    ->middleware([
        \App\Http\Middleware\AuthenticateKeuangan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-jenis-biaya'
    ])
    ->controller(\App\Http\Controllers\JenisBiayaController::class)
    ->where(['jenisBiaya' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{jenisBiaya}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{jenisBiaya}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{jenisBiaya}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{jenisBiaya}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
            \Illuminate\Support\Facades\Route::post('/{jenisBiaya}/aktifkan', 'aktifkan')->name('aktifkan');
        });
    });

// Tambahkan SEKALI, DI LUAR grup admin/keuangan lain di routes/web.php.
\Illuminate\Support\Facades\Route::prefix('tagihan')->name('tagihan.')
    ->middleware([
        \App\Http\Middleware\AuthenticateKeuangan::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-tagihan'
    ])
    ->controller(\App\Http\Controllers\TagihanController::class)
    ->where(['tagihan' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{tagihan}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{tagihan}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{tagihan}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{tagihan}/terbitkan', 'terbitkan')->name('terbitkan');
            \Illuminate\Support\Facades\Route::post('/{tagihan}/batalkan', 'batalkan')->name('batalkan');
            \Illuminate\Support\Facades\Route::post('/{tagihan}/buka-draf', 'bukaDraf')->name('buka-draf');
        });
    });

// Tempel SEKALI di routes/web.php, DI LUAR semua grup route lama.
\Illuminate\Support\Facades\Route::prefix('pembayaran')->name('pembayaran.')
    ->middleware([\App\Http\Middleware\AuthenticateKeuangan::class . ':web', 'auth.session', 'cache.headers:no_store;private', 'can:akses-pembayaran'])
    ->controller(\App\Http\Controllers\PembayaranController::class)
    ->where(['tagihan' => '[0-9]+', 'pembayaran' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/tagihan/{tagihan}/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{pembayaran}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{pembayaran}/bukti', 'tautan')->middleware('throttle:30,1')->name('tautan');
        \Illuminate\Support\Facades\Route::get('/{pembayaran}/unduh', 'unduh')->middleware(['signed', 'throttle:30,1'])->name('unduh');
        \Illuminate\Support\Facades\Route::middleware('throttle:10,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/tagihan/{tagihan}', 'store')->name('store');
            \Illuminate\Support\Facades\Route::post('/{pembayaran}/batalkan', 'batalkan')->name('batalkan');
        });
    });

// Tempel SEKALI di routes/web.php, DI LUAR seluruh grup route lama.
\Illuminate\Support\Facades\Route::prefix('admin/jenis-surat')->name('admin.jenis-surat.')
    ->middleware([
        \App\Http\Middleware\AuthenticateSurat::class . ':web',
        'auth.session',
        'cache.headers:no_store;private',
        'can:akses-jenis-surat'
    ])
    ->controller(\App\Http\Controllers\JenisSuratController::class)
    ->where(['jenisSurat' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{jenisSurat}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{jenisSurat}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{jenisSurat}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{jenisSurat}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
            \Illuminate\Support\Facades\Route::post('/{jenisSurat}/aktifkan', 'aktifkan')->name('aktifkan');
        });
    });

// Tempel SEKALI di routes/web.php, DI LUAR grup route sebelumnya.
\Illuminate\Support\Facades\Route::prefix('surat')->name('surat.')
    ->middleware([\App\Http\Middleware\AuthenticateSurat::class . ':web', 'auth.session', 'cache.headers:no_store;private', 'can:akses-surat'])
    ->controller(\App\Http\Controllers\PermohonanSuratController::class)
    ->where(['permohonanSurat' => '[0-9]+', 'bagian' => 'lampiran|hasil'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::post('/', 'store')->middleware('throttle:10,1')->name('store');
        \Illuminate\Support\Facades\Route::get('/{permohonanSurat}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::post('/{permohonanSurat}/tindakan', 'tindakan')->middleware('throttle:10,1')->name('tindakan');
        \Illuminate\Support\Facades\Route::get('/{permohonanSurat}/dokumen/{bagian}', 'tautan')->middleware('throttle:30,1')->name('tautan');
        \Illuminate\Support\Facades\Route::get('/{permohonanSurat}/unduh/{bagian}', 'unduh')->middleware(['signed', 'throttle:30,1'])->name('unduh');
    });


// Tempel SEKALI di routes/web.php, DI LUAR semua grup route lama.
\Illuminate\Support\Facades\Route::prefix('kalender-akademik')->name('kalender.')
    ->middleware([\App\Http\Middleware\AuthenticateSurat::class . ':web', 'auth.session', 'cache.headers:no_store;private', 'can:akses-kalender'])
    ->controller(\App\Http\Controllers\KalenderAkademikController::class)
    ->where(['kalenderAkademik' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{kalenderAkademik}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{kalenderAkademik}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{kalenderAkademik}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{kalenderAkademik}/tindakan', 'tindakan')->name('tindakan');
        });
    });


// Tambahkan sekali DI LUAR grup admin yang sudah ada.
\Illuminate\Support\Facades\Route::prefix('pengumuman')->name('pengumuman.')
    ->middleware([\App\Http\Middleware\AuthenticateSurat::class . ':web', 'auth.session', 'cache.headers:no_store;private', 'can:akses-pengumuman'])
    ->controller(\App\Http\Controllers\PengumumanController::class)
    ->where(['pengumuman' => '[0-9]+'])->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::get('/create', 'create')->name('create');
        \Illuminate\Support\Facades\Route::get('/{pengumuman}', 'show')->name('show');
        \Illuminate\Support\Facades\Route::get('/{pengumuman}/edit', 'edit')->name('edit');
        \Illuminate\Support\Facades\Route::middleware('throttle:30,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/', 'store')->name('store');
            \Illuminate\Support\Facades\Route::patch('/{pengumuman}', 'update')->name('update');
            \Illuminate\Support\Facades\Route::post('/{pengumuman}/tindakan', 'tindakan')->name('tindakan');
        });
    });

// Tambahkan sekali DI LUAR semua grup route sebelumnya.
\Illuminate\Support\Facades\Route::prefix('notifikasi')->name('notifikasi.')
    ->middleware([\App\Http\Middleware\AuthenticateSurat::class . ':web', 'auth.session', 'cache.headers:no_store;private', 'can:akses-notifikasi'])
    ->controller(\App\Http\Controllers\NotifikasiController::class)->where(['notifikasi' => '[0-9]+'])
    ->group(function (): void {
        \Illuminate\Support\Facades\Route::get('/', 'index')->name('index');
        \Illuminate\Support\Facades\Route::middleware('throttle:60,1')->group(function (): void {
            \Illuminate\Support\Facades\Route::post('/baca-halaman', 'bacaHalaman')->name('baca-halaman');
            \Illuminate\Support\Facades\Route::post('/{notifikasi}/baca', 'baca')->name('baca');
            \Illuminate\Support\Facades\Route::post('/{notifikasi}/buka', 'buka')->name('buka');
        });
    });
