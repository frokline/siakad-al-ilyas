<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Role;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define(
            'kelola-peran',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK),
        );

        Gate::define(
            'kelola-pengguna',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK),
        );

        Gate::define(
            'kelola-program-studi',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK),
        );

        Gate::define(
            'kelola-periode-akademik',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK),
        );

        Gate::define(
            'kelola-kurikulum',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK)
        );

        Gate::define(
            'kelola-mata-kuliah',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK)
        );

        Gate::define(
            'kelola-dosen',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK)
        );

        Gate::define(
            'kelola-riwayat-studi',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK)
        );

        Gate::define(
            'kelola-paket-semester',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK)
        );

        Gate::define(
            'kelola-rombel',
            fn(User $user): bool => $user->hasRole(Role::ADMIN_AKADEMIK)
        );

        \Illuminate\Support\Facades\Gate::define(
            'kelola-registrasi-semester',
            static fn(\App\Models\User $user): bool =>
            $user->hasRole(\App\Models\Role::ADMIN_AKADEMIK)
        );

        \Illuminate\Support\Facades\Gate::define(
            'kelola-kelas-kuliah',
            static fn(\App\Models\User $user): bool =>
            $user->hasRole(\App\Models\Role::ADMIN_AKADEMIK)
        );

        \Illuminate\Support\Facades\Gate::define(
            'kelola-krs',
            fn(\App\Models\User $user): bool => $user->hasRole(\App\Models\Role::ADMIN_AKADEMIK)
        );

        \App\Models\RegistrasiSemester::observe(\App\Observers\KrsIntegrityObserver::class);
        \App\Models\RiwayatStudi::observe(\App\Observers\KrsIntegrityObserver::class);
        \App\Models\PeriodeAkademik::observe(\App\Observers\KrsIntegrityObserver::class);
        \App\Models\KelasKuliah::observe(\App\Observers\KrsIntegrityObserver::class);

        \Illuminate\Support\Facades\Gate::define(
            'kelola-mahasiswa',
            static fn(\App\Models\User $user): bool =>
            \App\Models\User::query()
                ->whereKey($user->getAuthIdentifier())
                ->where('status', 'aktif')
                ->whereHas(
                    'roles',
                    fn(\Illuminate\Database\Eloquent\Builder $role) =>
                    $role->where(
                        'roles.kode',
                        \App\Models\Role::ADMIN_AKADEMIK
                    )
                )
                ->exists()
        );

        \Illuminate\Support\Facades\Gate::define(
            'kelola-pengajar-kelas',
            fn(\App\Models\User $user): bool => $user->hasRole(\App\Models\Role::ADMIN_AKADEMIK)
        );

        \Illuminate\Support\Facades\Gate::define(
            'mengajar-kelas',
            function (\App\Models\User $user, \App\Models\KelasKuliah $kelas): bool {
                if (! $user->hasRole(\App\Models\Role::DOSEN)) {
                    return false;
                }

                return \App\Models\PengajarKelas::query()
                    ->where('kelas_kuliah_id', $kelas->id)
                    ->whereHas('dosen', fn(\Illuminate\Database\Eloquent\Builder $dosen) => $dosen->where('user_id', $user->id))
                    ->bolehMengajar()->exists();
            }
        );

        \Illuminate\Support\Facades\Gate::define(
            'kelola-pertemuan',
            fn(\App\Models\User $user): bool => $user->hasRole(\App\Models\Role::ADMIN_AKADEMIK)
        );

        \App\Models\Pertemuan::observe(\App\Observers\PertemuanIntegrityObserver::class);
        \App\Models\JadwalKuliah::observe(\App\Observers\PertemuanIntegrityObserver::class);
        \App\Models\PengajarKelas::observe(\App\Observers\PertemuanIntegrityObserver::class);
        \App\Models\KelasKuliah::observe(\App\Observers\PertemuanIntegrityObserver::class);
        \App\Models\PeriodeAkademik::observe(\App\Observers\PertemuanIntegrityObserver::class);



        \Illuminate\Support\Facades\Gate::define(
            'kelola-jadwal-kuliah',
            fn(\App\Models\User $user): bool => $user->hasRole(\App\Models\Role::ADMIN_AKADEMIK)
        );

        \App\Models\PengajarKelas::observe(\App\Observers\JadwalKuliahIntegrityObserver::class);
        \App\Models\PeriodeAkademik::observe(\App\Observers\JadwalKuliahIntegrityObserver::class);

        \App\Models\KelasKuliah::observe(\App\Observers\PengajarKelasIntegrityObserver::class);



        RateLimiter::for('admin-login', function (Request $request): array {
            $input = $request->input('login');

            $login = is_string($input)
                ? Str::lower(trim($input))
                : '';

            return [
                Limit::perMinute(30)->by(
                    'admin-login:ip:' . hash('sha256', (string) $request->ip()),
                ),

                Limit::perMinute(5)->by(
                    'admin-login:identity:' . hash('sha256', $login),
                ),
            ];
        });

        RateLimiter::for('portal-login', function (Request $request): array {
            $input = $request->input('login', $request->input('username'));

            $identitas = is_string($input)
                ? Str::lower(trim(Str::limit($input, 190, '')))
                : '';

            $ipHash = hash('sha256', (string) $request->ip());
            $identitasHash = hash('sha256', $identitas . '|' . $ipHash);

            return [
                Limit::perMinute(30)->by('portal-login:ip:' . $ipHash),
                Limit::perMinute(5)->by('portal-login:akun:' . $identitasHash),
            ];
        });

        \Illuminate\Support\Facades\Gate::define(
            'akses-presensi',
            fn(\App\Models\User $user): bool => app(\App\Services\AksesPresensi::class)->masuk($user)
        );
        \Illuminate\Support\Facades\Gate::define(
            'lihat-presensi',
            fn(\App\Models\User $user, \App\Models\Pertemuan $sesi): bool => app(\App\Services\AksesPresensi::class)->lihat($user, $sesi)
        );
        \Illuminate\Support\Facades\Gate::define(
            'jalankan-pertemuan-presensi',
            fn(\App\Models\User $user, \App\Models\Pertemuan $sesi): bool => app(\App\Services\AksesPresensi::class)->jalankan($user, $sesi)
        );

        \App\Models\Pertemuan::observe(\App\Observers\PresensiIntegrityObserver::class);

        \Illuminate\Support\Facades\RateLimiter::for('presensi-login', function (\Illuminate\Http\Request $request): array {
            $username = $request->input('username');
            $identitas = is_string($username) ? mb_strtolower(trim(mb_substr($username, 0, 100))) : '';
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('presensi-ip:' . $request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by('presensi-akun:' . hash('sha256', $identitas . '|' . $request->ip())),
            ];
        });
        \Illuminate\Support\Facades\RateLimiter::for(
            'presensi-tulis',
            fn(\Illuminate\Http\Request $request) => \Illuminate\Cache\RateLimiting\Limit::perMinute(120)
                ->by('presensi-user:' . $request->user()->id)
        );

        // Tambahkan di dalam boot() AppServiceProvider. Pertahankan seluruh isi lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Berkas::class, \App\Policies\BerkasPolicy::class);
        \Illuminate\Support\Facades\Gate::define(
            'akses-berkas',
            fn(\App\Models\User $user): bool => app(\App\Services\AksesBerkas::class)->masuk($user)
        );
        \Illuminate\Support\Facades\RateLimiter::for('berkas-login', function (\Illuminate\Http\Request $request): array {
            $nama = $request->input('username');
            $nama = is_string($nama) ? mb_strtolower(trim(mb_substr($nama, 0, 100))) : '';
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('berkas-ip:' . $request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by('berkas-akun:' . hash('sha256', $nama . '|' . $request->ip()))
            ];
        });
        \Illuminate\Support\Facades\RateLimiter::for(
            'berkas-upload',
            fn(\Illuminate\Http\Request $request) => \Illuminate\Cache\RateLimiting\Limit::perMinute(5)
                ->by('berkas-upload:' . $request->user()->id)
        );

        \Illuminate\Support\Facades\Gate::policy(\App\Models\Materi::class, \App\Policies\MateriPolicy::class);
        \Illuminate\Support\Facades\Gate::define(
            'akses-materi',
            fn(\App\Models\User $user): bool => app(\App\Services\AksesMateri::class)->masuk($user)
        );

        // Tambahkan DI DALAM boot() AppServiceProvider; pertahankan kode lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Kegiatan::class, \App\Policies\KegiatanPolicy::class);
        \Illuminate\Support\Facades\Gate::define(
            'akses-kegiatan',
            fn(\App\Models\User $user): bool => app(\App\Services\AksesKegiatan::class)->masuk($user)
        );

        // Tambahkan di dalam AppServiceProvider::boot(), pertahankan kode lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Pengumpulan::class, \App\Policies\PengumpulanPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-pengumpulan', fn(\App\Models\User $user): bool =>
        app(\App\Services\AksesPengumpulan::class)->masuk($user));


        // Tambahkan SEKALI di dalam AppServiceProvider::boot(). Kode lama tetap dipertahankan.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\JenisBiaya::class, \App\Policies\JenisBiayaPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-jenis-biaya', fn(\App\Models\User $user): bool =>
        app(\App\Services\AksesKeuangan::class)->lihatMaster($user));

        // Di dalam boot() AppServiceProvider, pertahankan kode lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Tagihan::class, \App\Policies\TagihanPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-tagihan', fn(\App\Models\User $u): bool => app(\App\Services\AksesTagihan::class)->masuk($u));


        // Tambahkan di dalam boot() AppServiceProvider. Pertahankan semua registrasi lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Pembayaran::class, \App\Policies\PembayaranPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-pembayaran', fn(\App\Models\User $u): bool => app(\App\Services\AksesPembayaran::class)->masuk($u));

        // Tambahkan SEKALI di dalam AppServiceProvider::boot(). Kode lama tetap dipertahankan.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\JenisSurat::class, \App\Policies\JenisSuratPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-jenis-surat', fn(\App\Models\User $user): bool =>
        app(\App\Services\AksesJenisSurat::class)->lihatMaster($user));

        // Tambahkan SEKALI DI DALAM AppServiceProvider::boot(). Pertahankan semua kode lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\PermohonanSurat::class, \App\Policies\PermohonanSuratPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-surat', fn(\App\Models\User $u): bool => app(\App\Services\AksesSurat::class)->masuk($u));

        // Tambahkan di dalam AppServiceProvider::boot(), tanpa menghapus Gate/policy sebelumnya.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\KalenderAkademik::class, \App\Policies\KalenderAkademikPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-kalender', fn(\App\Models\User $u): bool => app(\App\Services\AksesKalender::class)->masuk($u));


        // DI DALAM boot() AppServiceProvider, tanpa menghapus registrasi lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Pengumuman::class, \App\Policies\PengumumanPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-pengumuman', fn(\App\Models\User $u): bool => app(\App\Services\AksesPengumuman::class)->masuk($u));

        // DI DALAM boot() AppServiceProvider, pertahankan registrasi lama.
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Notifikasi::class, \App\Policies\NotifikasiPolicy::class);
        \Illuminate\Support\Facades\Gate::define('akses-notifikasi', fn(\App\Models\User $u): bool => app(\App\Services\AksesNotifikasi::class)->masuk($u));
    }
}
