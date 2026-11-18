<?php

namespace App\Http\Controllers;

use App\Http\Requests\PortalFotoProfilDosenRequest;
use App\Services\AksesProfilDosen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PortalFotoProfilDosenController extends Controller
{
    public function __construct(
        private readonly AksesProfilDosen $aksesProfil
    ) {}

    /**
     * Menampilkan foto profil dosen.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 401);
        abort_unless($this->aksesProfil->masuk($user), 403);

        $dosen = $this->aksesProfil->profil($user);
        $path = $dosen->foto_profil;

        $headers = [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ];

        $prefix = 'foto-profil-dosen/' . $dosen->getKey() . '/';

        if (
            is_string($path)
            && str_starts_with($path, $prefix)
            && preg_match(
                '#\Afoto-profil-dosen/[0-9]+/[A-Za-z0-9]{40}\.(jpg|png|webp)\z#',
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

        // Avatar inisial sebagai foto cadangan.
        $inisial = mb_substr(
            trim((string) ($user->nama ?? 'D')) ?: 'D',
            0,
            1
        );

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" '
            . 'width="120" height="120" viewBox="0 0 120 120">'
            . '<rect width="120" height="120" rx="60" fill="#d1fae5"/>'
            . '<text x="60" y="75" text-anchor="middle" '
            . 'font-family="Arial,sans-serif" font-size="54" '
            . 'font-weight="700" fill="#064e3b">'
            . e($inisial)
            . '</text></svg>';

        return response($svg, 200, [
            ...$headers,
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
        ]);
    }

    /**
     * Mengganti foto profil dosen.
     */
    public function update(
        PortalFotoProfilDosenRequest $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user !== null, 401);
        abort_unless($this->aksesProfil->masuk($user), 403);

        $dosen = $this->aksesProfil->profil($user);
        $file = $request->file('foto');

        $extension = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if ($extension === null) {
            return back()
                ->withErrors([
                    'foto' => 'Format gambar tidak didukung.',
                ])
                ->withInput();
        }

        // Folder dosen dipisahkan dari folder mahasiswa.
        $directory = 'foto-profil-dosen/' . $dosen->getKey();

        $filename = Str::random(40) . '.' . $extension;
        $newPath = $directory . '/' . $filename;

        $disk = Storage::disk('local');
        $oldPath = $dosen->foto_profil;

        // Simpan foto baru terlebih dahulu.
        $storedPath = $file->storeAs(
            $directory,
            $filename,
            'local'
        );

        if ($storedPath === false) {
            return back()->withErrors([
                'foto' => 'Foto gagal disimpan. Silakan coba kembali.',
            ]);
        }

        try {
            DB::transaction(function () use (
                $user,
                $dosen,
                $newPath
            ): void {
                $affected = DB::table('dosen')
                    ->where('id', $dosen->getKey())
                    ->where('user_id', $user->getKey())
                    ->update([
                        'foto_profil' => $newPath,
                        'updated_at' => now(),
                    ]);

                if ($affected !== 1) {
                    throw new \RuntimeException(
                        'Profil dosen tidak berhasil diperbarui.'
                    );
                }
            });
        } catch (Throwable $exception) {
            // Hapus foto baru jika penyimpanan database gagal.
            $disk->delete($newPath);

            throw $exception;
        }

        // Hapus foto lama setelah database berhasil diperbarui.
        $prefix = 'foto-profil-dosen/' . $dosen->getKey() . '/';

        if (
            is_string($oldPath)
            && str_starts_with($oldPath, $prefix)
            && preg_match(
                '#\Afoto-profil-dosen/[0-9]+/[A-Za-z0-9]{40}\.(jpg|png|webp)\z#',
                $oldPath
            ) === 1
            && $oldPath !== $newPath
        ) {
            $disk->delete($oldPath);
        }

        return redirect()
            ->route('portal.dosen.profil.edit')
            ->with(
                'info',
                'Foto profil berhasil diperbarui.'
            );
    }
}