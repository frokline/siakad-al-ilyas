<?php

namespace App\Http\Controllers;

use App\Actions\KelolaBerkas;
use App\Exports\MahasiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\MahasiswaRequest;
use App\Models\Berkas;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBiodataBerkas;
use App\Models\Role;
use App\Models\User;
use App\Services\PenyimpananBerkas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola-mahasiswa');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status_akun' => [
                'nullable',
                Rule::in(['aktif', 'nonaktif']),
            ],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $kata = trim($filter['q'] ?? '');

        $daftar = Mahasiswa::query()
            ->with([
                'user:id,nama,email,status',
                'riwayatAktif.kurikulum.programStudi',
            ])
            ->when($kata !== '', function (Builder $query) use ($kata): void {
                $query->where(function (Builder $pencarian) use ($kata): void {
                    $pencarian->where('nim', 'like', '%' . $kata . '%')
                        ->orWhereHas('user', function (Builder $akun) use ($kata): void {
                            $akun->where(function (Builder $identitas) use ($kata): void {
                                $identitas->where('nama', 'like', '%' . $kata . '%')
                                    ->orWhere('email', 'like', '%' . $kata . '%');
                            });
                        });
                });
            })
            ->when(
                $filter['status_akun'] ?? null,
                function (Builder $query, string $status): void {
                    $query->whereHas(
                        'user',
                        fn(Builder $akun) => $akun->where('status', $status)
                    );
                }
            )
            ->orderBy('nim')
            ->orderBy('id')
            ->paginate(20)
            ->appends(Arr::only($filter, ['q', 'status_akun']));

        return view('mahasiswa.index', compact('daftar', 'filter'));
    }
    public function export(Request $request)
    {
        Gate::authorize('kelola-mahasiswa');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status_akun' => [
                'nullable',
                Rule::in(['aktif', 'nonaktif']),
            ],
        ]);

        $namaFile = 'rekap-mahasiswa-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new MahasiswaExport($filter),
            $namaFile
        );
    }

    public function create(Request $request): View
    {
        Gate::authorize('kelola-mahasiswa');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $akun = null;

        if (! empty($filter['user_id'])) {
            $akun = $this->akunTersedia()
                ->whereKey($filter['user_id'])
                ->first();
        }

        $akunDaftar = null;

        if ($akun === null) {
            $kata = trim($filter['q'] ?? '');

            $akunDaftar = $this->akunTersedia()
                ->when($kata !== '', function (Builder $query) use ($kata): void {
                    $query->where(function (Builder $pencarian) use ($kata): void {
                        $pencarian->where('nama', 'like', '%' . $kata . '%')
                            ->orWhere('email', 'like', '%' . $kata . '%')
                            ->orWhere('username', 'like', '%' . $kata . '%');
                    });
                })
                ->orderBy('nama')
                ->orderBy('id')
                ->paginate(10)
                ->appends(Arr::only($filter, ['q']));
        }

        return view('mahasiswa.create', [
            'mahasiswa' => new Mahasiswa(),
            'akun' => $akun,
            'akunDaftar' => $akunDaftar,
            'filter' => $filter,
        ]);
    }

    public function store(MahasiswaRequest $request): RedirectResponse
    {
        $mahasiswa = $this->simpan($request);

        return to_route('admin.mahasiswa.show', $mahasiswa)
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function show(Mahasiswa $mahasiswa): View
    {
        Gate::authorize('kelola-mahasiswa');

        $mahasiswa->load([
            'user',
            'biodata',
            'alamatBiodata',
            'pendidikanAsal',
            'dataKeluarga',
            'berkasBiodata.berkas',
        ]);

        $riwayatDaftar = $mahasiswa->riwayatStudi()
            ->with('kurikulum.programStudi')
            ->orderByDesc('angkatan')
            ->orderByDesc('id')
            ->paginate(10);

        return view('mahasiswa.show', [
            'mahasiswa' => $mahasiswa,
            'riwayatDaftar' => $riwayatDaftar,
            'biodata' => $mahasiswa->biodata,
            'alamatBiodata' => $mahasiswa->alamatBiodata,
            'pendidikanAsal' => $mahasiswa->pendidikanAsal,
            'dataKeluarga' => $mahasiswa->dataKeluarga,
            'berkasBiodata' => $mahasiswa->berkasBiodata,
        ]);
    }

    public function edit(Request $request, Mahasiswa $mahasiswa): View
    {
        Gate::authorize('kelola-mahasiswa');

        $mahasiswa->load([
            'user',
            'biodata',
            'alamatBiodata',
            'pendidikanAsal',
            'dataKeluarga',
            'berkasBiodata.berkas',
        ]);

        abort_if($mahasiswa->user === null, 404);

        $tabDiperbolehkan = [
            'identitas',
            'biodata',
            'alamat',
            'pendidikan',
            'keluarga',
            'berkas',
        ];

        $tab = $request->query('tab');

        return view('mahasiswa.edit', [
            'mahasiswa' => $mahasiswa,
            'akun' => $mahasiswa->user,
            'biodata' => $mahasiswa->biodata,
            'alamatBiodata' => $mahasiswa->alamatBiodata,
            'pendidikanAsal' => $mahasiswa->pendidikanAsal,
            'dataKeluarga' => $mahasiswa->dataKeluarga,
            'berkasBiodata' => $mahasiswa->berkasBiodata,
            'tab' => in_array($tab, $tabDiperbolehkan, true)
                ? $tab
                : 'identitas',
        ]);
    }

    public function update(
        MahasiswaRequest $request,
        Mahasiswa $mahasiswa,
        KelolaBerkas $unggahBerkas
    ): RedirectResponse {
        $hasil = $this->simpan($request, $mahasiswa);

        $this->simpanBagianBiodata($request, $hasil, $unggahBerkas);

        return to_route('admin.mahasiswa.show', $hasil)
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function lihatBerkas(
        Mahasiswa $mahasiswa,
        int $berkas,
        PenyimpananBerkas $penyimpanan
    ): StreamedResponse {
        Gate::authorize('kelola-mahasiswa');

        $tautan = $mahasiswa->berkasBiodata()
            ->where('berkas_id', $berkas)
            ->with('berkas')
            ->firstOrFail();

        $file = $tautan->berkas;
        abort_if($file === null || $file->status !== Berkas::TERSEDIA, 404);

        try {
            $stream = $penyimpanan->buka($file);
        } catch (\Throwable $e) {
            Log::warning('Pratinjau berkas mahasiswa gagal.', [
                'mahasiswa_id' => $mahasiswa->id,
                'berkas_id' => $file->id,
                'jenis' => $e::class,
            ]);
            abort(503, 'Berkas belum dapat ditampilkan. Coba lagi atau hubungi pengelola.');
        }

        $nama = Str::slug(mb_substr($file->label, 0, 100)) ?: 'berkas-' . $file->id;
        $namaFile = $nama . '.' . $file->ekstensi;

        return response()->stream(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $namaFile . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Content-Length' => (string) $file->ukuran_byte,
        ]);
    }

    private function akunTersedia(): Builder
    {
        return User::query()
            ->select(['id', 'nama', 'email', 'username', 'status'])
            ->where('status', 'aktif')
            ->whereHas(
                'roles',
                fn(Builder $role) => $role->where(
                    'roles.kode',
                    Role::MAHASISWA
                )
            )
            ->whereDoesntHave('mahasiswa');
    }

    private function simpan(
        MahasiswaRequest $request,
        ?Mahasiswa $mahasiswa = null
    ): Mahasiswa {
        $data = $request->validated();
        $pelakuId = (int) $request->user()->getAuthIdentifier();

        $pemilikId = $mahasiswa !== null
            ? (int) $mahasiswa->user_id
            : (int) $data['user_id'];

        try {
            return DB::transaction(function () use (
                $data,
                $pelakuId,
                $pemilikId,
                $mahasiswa
            ): Mahasiswa {
                // Urutan penguncian konsisten untuk pengguna yang terlibat.
                $pengguna = User::query()
                    ->whereIn('id', array_unique([$pelakuId, $pemilikId]))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $pelaku = $pengguna->get($pelakuId);

                abort_if($pelaku === null, 403);

                Gate::forUser($pelaku)->authorize('kelola-mahasiswa');

                $akun = $pengguna->get($pemilikId);

                if ($akun === null) {
                    throw ValidationException::withMessages([
                        'user_id' => 'Akun pengguna tidak lagi tersedia.',
                    ]);
                }

                if ($mahasiswa !== null) {
                    $record = Mahasiswa::query()
                        ->whereKey($mahasiswa->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        (int) $record->user_id !== $pemilikId
                        || ! hash_equals(
                            $record->versiForm(),
                            $data['versi']
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'versi' => 'Data telah berubah sejak formulir dibuka. Muat ulang formulir sebelum menyimpan.',
                        ]);
                    }
                } else {
                    $memilikiPeran = $akun->roles()
                        ->where('roles.kode', Role::MAHASISWA)
                        ->exists();

                    if ($akun->status !== 'aktif' || ! $memilikiPeran) {
                        throw ValidationException::withMessages([
                            'user_id' => 'Pilih akun aktif yang memiliki peran mahasiswa.',
                        ]);
                    }

                    if (Mahasiswa::query()
                        ->where('user_id', $akun->id)
                        ->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'user_id' => 'Akun ini sudah memiliki data mahasiswa.',
                        ]);
                    }

                    $record = new Mahasiswa();
                    $record->user()->associate($akun);
                }

                $record->fill(Arr::only($data, [
                    'nim',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'alamat',
                    'konsentrasi',
                    'sistem_kuliah',
                    'jenis_pendaftaran',
                    'jalur_pendaftaran',
                    'gelombang',
                    'tanggal_awal_masuk',
                    'tanggal_daftar_ulang',
                    'kebutuhan_khusus',
                ]));
                $record->save();

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (
                $mahasiswa === null
                && Mahasiswa::query()->where('user_id', $pemilikId)->exists()
            ) {
                throw ValidationException::withMessages([
                    'user_id' => 'Akun ini sudah didaftarkan sebagai mahasiswa. Pilih akun lain.',
                ]);
            }

            throw ValidationException::withMessages([
                'nim' => 'NIM sudah digunakan mahasiswa lain.',
            ]);
        }
    }

    private function simpanBagianBiodata(
        MahasiswaRequest $request,
        Mahasiswa $mahasiswa,
        KelolaBerkas $unggahBerkas
    ): void {
        $data = Arr::map(
            $request->validated(),
            static fn(mixed $nilai): mixed =>
                is_string($nilai) && trim($nilai) === '' ? null : $nilai
        );

        DB::transaction(function () use ($mahasiswa, $data): void {
            $mahasiswa->biodata()->updateOrCreate(
                [],
                Arr::only($data, [
                    'agama',
                    'suku',
                    'kewarganegaraan',
                    'nik',
                    'nomor_kk',
                    'npwp',
                    'nomor_kps',
                    'golongan_darah',
                    'status_nikah',
                    'ukuran_jas_almamater',
                    'nomor_hp',
                    'email_pribadi',
                    'pekerjaan',
                    'instansi_pekerjaan',
                    'penghasilan',
                    'nomor_rekening',
                    'nama_rekening',
                    'nama_bank',
                    'berat_badan',
                    'tinggi_badan',
                    'transportasi',
                ])
            );

            $mahasiswa->alamatBiodata()->updateOrCreate(
                [],
                Arr::only($data, [
                    'alamat_ktp',
                    'rt_ktp',
                    'rw_ktp',
                    'dusun_ktp',
                    'desa_kelurahan_ktp',
                    'provinsi_ktp',
                    'kota_kabupaten_ktp',
                    'kecamatan_ktp',
                    'kode_pos_ktp',
                    'domisili_sama_dengan_ktp',
                    'alamat_domisili',
                    'rt_domisili',
                    'rw_domisili',
                    'dusun_domisili',
                    'desa_kelurahan_domisili',
                    'provinsi_domisili',
                    'kota_kabupaten_domisili',
                    'kecamatan_domisili',
                    'kode_pos_domisili',
                    'status_tinggal',
                ])
            );

            $mahasiswa->pendidikanAsal()->updateOrCreate(
                [],
                Arr::only($data, [
                    'jenis_pendidikan_asal',
                    'perguruan_tinggi',
                    'program_studi_asal',
                    'ipk',
                    'sks_diakui',
                    'nomor_ijazah',
                    'nisn',
                ])
            );

            $keluarga = $data['keluarga'] ?? [];

            foreach (['ayah', 'ibu', 'wali'] as $jenis) {
                if (
                    ! is_array($keluarga[$jenis] ?? null)
                ) {
                    continue;
                }

                $mahasiswa->dataKeluarga()->updateOrCreate(
                    [
                        'jenis_hubungan' => $jenis,
                    ],
                    Arr::only($keluarga[$jenis], [
                        'nama_lengkap',
                        'nik',
                        'tanggal_lahir',
                        'status_hidup',
                        'status_kekerabatan',
                        'pendidikan_terakhir',
                        'pekerjaan',
                        'penghasilan',
                        'alamat',
                        'nomor_telepon',
                        'email',
                    ])
                );
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

        foreach ($jenisBerkas as $jenis => $judul) {
            $file = $request->file('berkas_' . $jenis);

            if (
                ! $file instanceof \Illuminate\Http\UploadedFile
                || ! $file->isValid()
            ) {
                continue;
            }

            $berkas = $unggahBerkas->unggah(
                (int) $mahasiswa->user_id,
                $file,
                [
                    'upload_token' => (string) Str::uuid(),
                    'label' => $judul . ' - ' . $mahasiswa->nim,
                    'keterangan' =>
                        'Berkas biodata mahasiswa ' . $mahasiswa->nim,
                ]
            );

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
        }
    }
}
