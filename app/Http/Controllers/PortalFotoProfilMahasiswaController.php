<?php

namespace App\Http\Controllers;

use App\Http\Requests\PortalFotoProfilMahasiswaRequest;
use App\Services\AksesProfilMahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;


final class PortalFotoProfilMahasiswaController extends Controller
{
    public function __construct(
        private readonly AksesProfilMahasiswa $aksesProfil,
    ) {}

    public function show(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 401);
        abort_unless($this->aksesProfil->masuk($user), 403);

        $mahasiswa = $this->aksesProfil->profil($user);
        $path = $mahasiswa->foto_profil;

        $headers = [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ];

        if (
            is_string($path)
            && preg_match(
                '#\Afoto-profil/[0-9]+/[A-Za-z0-9]{40}\.(jpg|png|webp)\z#',
                $path
            ) === 1
            && Storage::disk('local')->exists($path)
        ) {
            $absolutePath = Storage::disk('local')->path($path);

            if (is_file($absolutePath)) {
                $mime = match (pathinfo($path, PATHINFO_EXTENSION)) {
                    'jpg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                };

                return response()->file($absolutePath, [
                    ...$headers,
                    'Content-Type' => $mime,
                ]);
            }
        }

        // Avatar SVG sederhana sebagai fallback bila belum ada foto.
        $inisial = mb_substr(
            trim((string) ($user->nama ?? 'M')) ?: 'M',
            0,
            1
        );

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" '
            . 'width="120" height="120" viewBox="0 0 120 120">'
            . '<rect width="120" height="120" rx="60" fill="#d1d5db"/>'
            . '<text x="60" y=" seventy" text-anchor="middle" '
            . 'font-family="Arial,sans-serif" font-size="54" '
            . 'font-weight="700" fill="#064e3b">'
            . e($inisial)
            . '</text></svg>';

        $svg = str_replace('y=" seventy"', 'y="75"', $svg);

        return response($svg, 200, [
            ...$headers,
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
        ]);
    }

    public function update(
        PortalFotoProfilMahasiswaRequest $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user !== null, 401);

        // Selalu ambil profil milik akun yang sedang login.
        $mahasiswa = $this->aksesProfil->profil($user);
        $file = $request->file('foto');

        $extension = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if ($extension === null) {
            return back()
                ->withErrors(['foto' => 'Format gambar tidak didukung.'])
                ->withInput();
        }

        $directory = 'foto-profil/' . $mahasiswa->getKey();
        $filename = Str::random(40) . '.' . $extension;
        $newPath = $directory . '/' . $filename;

        $disk = Storage::disk('local');
        $oldPath = $mahasiswa->foto_profil;

        // Simpan berkas baru terlebih dahulu.
        $storedPath = $file->storeAs($directory, $filename, 'local');

        if ($storedPath === false) {
            return back()->withErrors([
                'foto' => 'Foto gagal disimpan. Silakan coba kembali.',
            ]);
        }

        try {
            DB::transaction(function () use (
                $user,
                $mahasiswa,
                $newPath
            ): void {
                $affected = DB::table('mahasiswa')
                    ->where('id', $mahasiswa->getKey())
                    ->where('user_id', $user->getKey())
                    ->update([
                        'foto_profil' => $newPath,
                        'updated_at' => now(),
                    ]);

                if ($affected !== 1) {
                    throw new \RuntimeException(
                        'Profil tidak berhasil diperbarui.'
                    );
                }
            });
        } catch (Throwable $exception) {
            // Jangan tinggalkan file baru jika update database gagal.
            $disk->delete($newPath);

            throw $exception;
        }

        // Hapus foto lama hanya setelah database berhasil diperbarui.
        if (
            is_string($oldPath)
            && preg_match(
                '#\Afoto-profil/[0-9]+/[A-Za-z0-9]{40}\.(jpg|png|webp)\z#',
                $oldPath
            ) === 1
            && $oldPath !== $newPath
        ) {
            $disk->delete($oldPath);
        }

        return redirect()
            ->route('portal.profil.edit')
            ->with('info', 'Foto profil berhasil diperbarui.');
    }
}
