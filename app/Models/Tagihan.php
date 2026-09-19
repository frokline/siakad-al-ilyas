<?php

namespace App\Models;

use App\Services\UangTagihan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Tagihan extends Model
{
    public const DRAF = 'draf';
    public const TERBIT = 'terbit';
    public const DIBATALKAN = 'dibatalkan';
    public const STATUS = ['draf' => 'Draf', 'terbit' => 'Terbit', 'dibatalkan' => 'Dibatalkan'];
    protected $table = 'tagihan';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan'];

    protected function casts(): array
    {
        return [
            'mahasiswa_id' => 'integer',
            'registrasi_semester_id' => 'integer',
            'jenis_biaya_id' => 'integer',
            'pembuat_id' => 'integer',
            'tahun_tagihan' => 'integer',
            'bulan_tagihan' => 'integer',
            'revisi' => 'integer',
            'nominal' => 'decimal:2',
            'snapshot' => 'array',
            'jatuh_tempo' => 'immutable_date',
            'diterbitkan_at' => 'immutable_datetime',
            'dibatalkan_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $t): void {
            if (
                $t->getConnection()->transactionLevel() < 1 || ! isset(self::STATUS[$t->status])
                || $t->tahun_tagihan < 2000 || $t->tahun_tagihan > 2199 || $t->bulan_tagihan < 1 || $t->bulan_tagihan > 12
                || $t->revisi < 1 || $t->revisi > 4294967295 || ! $t->jatuh_tempo
                || ! \Illuminate\Support\Str::isUuid((string) $t->form_token)
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $t->hash_permohonan)
            ) {
                throw new LogicException('Tagihan harus valid dan ditulis melalui KelolaTagihan.');
            }
            UangTagihan::normal((string) $t->nominal);
            if (($t->status === self::DRAF && ($t->diterbitkan_at !== null || $t->dibatalkan_at !== null || $t->snapshot !== null))
                || ($t->status === self::TERBIT && ($t->diterbitkan_at === null || $t->dibatalkan_at !== null || ! is_array($t->snapshot)))
                || ($t->status === self::DIBATALKAN && $t->dibatalkan_at === null)
            ) {
                throw new LogicException('Status, waktu, dan snapshot tidak konsisten.');
            }
            if (! $t->exists) {
                if ($t->status !== self::DRAF || $t->revisi !== 1) {
                    throw new LogicException('Tagihan baru harus draf revisi satu.');
                }
                return;
            }
            if ($t->isDirty([
                'mahasiswa_id',
                'registrasi_semester_id',
                'jenis_biaya_id',
                'tahun_tagihan',
                'bulan_tagihan',
                'nomor',
                'pembuat_id',
                'form_token',
                'hash_permohonan'
            ]) || $t->revisi !== (int) $t->getRawOriginal('revisi') + 1) {
                throw new LogicException('Identitas tagihan tetap dan revisi harus bertambah satu.');
            }
            $asal = $t->getRawOriginal('status');
            $boleh = [
                self::DRAF => [self::DRAF, self::TERBIT, self::DIBATALKAN],
                self::TERBIT => [self::DIBATALKAN],
                self::DIBATALKAN => [self::DRAF]
            ];
            if (
                ! in_array($t->status, $boleh[$asal] ?? [], true)
                || ($t->isDirty(['nominal', 'jatuh_tempo', 'catatan']) && ! ($asal === self::DRAF && $t->status === self::DRAF))
            ) {
                throw new LogicException('Koreksi isi hanya melalui draf; tagihan terbit dibekukan.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Tagihan tidak boleh dihapus. Gunakan pembatalan tercatat.');
        });
    }
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
    public function registrasiSemester(): BelongsTo
    {
        return $this->belongsTo(RegistrasiSemester::class, 'registrasi_semester_id');
    }
    public function jenisBiaya(): BelongsTo
    {
        return $this->belongsTo(JenisBiaya::class, 'jenis_biaya_id');
    }
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'tagihan');
    }
    // Baru dipanggil setelah modul Pembayaran dipasang.
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'tagihan:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function nominalRupiah(): string
    {
        return UangTagihan::rupiah((string) $this->nominal);
    }
    public function ringkasanAudit(): array
    {
        return $this->only([
            'nomor',
            'mahasiswa_id',
            'registrasi_semester_id',
            'jenis_biaya_id',
            'tahun_tagihan',
            'bulan_tagihan',
            'nominal',
            'jatuh_tempo',
            'status',
            'snapshot',
            'catatan',
            'diterbitkan_at',
            'dibatalkan_at',
            'revisi'
        ]);
    }
}
