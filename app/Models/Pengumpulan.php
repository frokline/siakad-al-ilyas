<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class Pengumpulan extends Model
{
    public const DRAF = 'draf';
    public const DIKIRIM = 'dikirim';
    public const STATUS = ['draf' => 'Draf — belum dikumpulkan', 'dikirim' => 'Sudah dikumpulkan'];
    protected $table = 'pengumpulan';
    protected $guarded = ['*'];
    protected $hidden = ['token_draf', 'kunci_kirim', 'jawaban_teks'];
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'kegiatan_id' => 'integer',
            'detail_krs_id' => 'integer',
            'pemilik_id' => 'integer',
            'versi' => 'integer',
            'penanda_draf' => 'integer',
            'revisi' => 'integer',
            'revisi_kegiatan' => 'integer',
            'dikirim_at' => 'immutable_datetime',
            'tenggat_snapshot' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $p): void {
            if (
                $p->getConnection()->transactionLevel() < 1 || ! isset(self::STATUS[$p->status])
                || $p->versi < 1 || $p->versi > 4294967295 || $p->revisi < 1 || $p->revisi > 4294967295
                || $p->kegiatan_id < 1 || $p->detail_krs_id < 1 || $p->pemilik_id < 1
                || ! Str::isUuid((string) $p->token_draf) || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $p->kunci_kirim)
                || mb_strlen((string) $p->jawaban_teks) > 10000
            ) {
                throw new LogicException('Pengumpulan harus valid dan ditulis melalui KelolaPengumpulan.');
            }
            if ($p->status === self::DRAF) {
                if (
                    $p->penanda_draf !== 1 || $p->dikirim_at !== null || $p->tenggat_snapshot !== null
                    || $p->revisi_kegiatan !== null || $p->hash_jawaban !== null
                ) {
                    throw new LogicException('Draf tidak mempunyai metadata kiriman final.');
                }
            } elseif (
                $p->penanda_draf !== null || $p->dikirim_at === null || $p->tenggat_snapshot === null
                || ! $p->dikirim_at->lt($p->tenggat_snapshot) || $p->revisi_kegiatan < 1
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $p->hash_jawaban)
            ) {
                throw new LogicException('Metadata kiriman final tidak lengkap.');
            }
            if (! $p->exists) {
                if ($p->status !== self::DRAF || $p->revisi !== 1) {
                    throw new LogicException('Versi baru harus draf, revisi satu.');
                }
                return;
            }
            if (
                $p->getRawOriginal('status') !== self::DRAF
                || $p->isDirty(['kegiatan_id', 'detail_krs_id', 'pemilik_id', 'versi', 'token_draf', 'kunci_kirim'])
                || $p->revisi !== (int) $p->getRawOriginal('revisi') + 1
            ) {
                throw new LogicException('Identitas dan jawaban yang dikirim tidak boleh diubah.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Riwayat pengumpulan tidak dihapus.');
        });
    }
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
    public function detailKrs(): BelongsTo
    {
        return $this->belongsTo(DetailKrs::class, 'detail_krs_id');
    }
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }
    public function lampiran(): HasMany
    {
        return $this->hasMany(PengumpulanBerkas::class, 'pengumpulan_id')->where('aktif', true);
    }
    public function semuaLampiran(): HasMany
    {
        return $this->hasMany(PengumpulanBerkas::class, 'pengumpulan_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'pengumpulan');
    }
    public function scopeBerlaku(Builder $q): Builder
    {
        return $q->where('pengumpulan.status', self::DIKIRIM)->whereNotExists(function ($sub): void {
            $sub->selectRaw('1')->from('pengumpulan as lebih_baru')->whereColumn('lebih_baru.kegiatan_id', 'pengumpulan.kegiatan_id')
                ->whereColumn('lebih_baru.detail_krs_id', 'pengumpulan.detail_krs_id')->where('lebih_baru.status', self::DIKIRIM)
                ->whereColumn('lebih_baru.versi', '>', 'pengumpulan.versi');
        });
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'pengumpulan:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function hitungHash(): string
    {
        $files = $this->lampiran()->orderBy('berkas_id')->get()->map(fn(PengumpulanBerkas $b) =>
        $b->only(['berkas_id', 'nama_asli', 'mime_type', 'ekstensi', 'ukuran_byte', 'sha256']))->all();
        return hash('sha256', json_encode([
            'kegiatan' => $this->kegiatan_id,
            'peserta' => $this->detail_krs_id,
            'versi' => $this->versi,
            'jawaban' => $this->jawaban_teks,
            'berkas' => $files
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
    public function ringkasanAudit(): array
    {
        // Jangan menyalin teks jawaban, kunci kirim, atau lokasi cloud ke audit umum.
        return [
            ...$this->only([
                'kegiatan_id',
                'detail_krs_id',
                'pemilik_id',
                'versi',
                'status',
                'revisi',
                'dikirim_at',
                'tenggat_snapshot',
                'revisi_kegiatan',
                'hash_jawaban'
            ]),
            'panjang_teks' => mb_strlen((string) $this->jawaban_teks),
            'hash_isi' => $this->hitungHash(),
            'berkas_ids' => $this->lampiran()->orderBy('berkas_id')->pluck('berkas_id')->all()
        ];
    }
}
