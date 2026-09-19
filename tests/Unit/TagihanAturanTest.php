<?php

namespace Tests\Unit;

use App\Services\UangTagihan;
use App\Services\AturanTagihan;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TagihanAturanTest extends TestCase
{
    public function test_nominal_tanpa_float_dan_format_rupiah(): void
    {
        $this->assertSame('250000.00', UangTagihan::normal('250000'));
        $this->assertSame('0.01', UangTagihan::normal('0.01'));
        $this->assertSame('999999999999.99', UangTagihan::normal('999999999999.99'));
        $this->assertSame('Rp 250.000,50', UangTagihan::rupiah('250000.50'));
    }
    public function test_nominal_invalid_ditolak(): void
    {
        foreach (['0', '-1', '1e3', '1,50', '250.000', '1000000000000', '1.001', 'NaN'] as $value) {
            try {
                UangTagihan::normal($value);
                $this->fail('Seharusnya ditolak: ' . $value);
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('nominal', $e->errors());
            }
        }
    }
    public function test_identitas_tidak_dapat_disuntikkan(): void
    {
        $this->expectException(ValidationException::class);
        AturanTagihan::isi(['nominal' => '100', 'jatuh_tempo' => '2026-09-30', 'mahasiswa_id' => 99], false);
    }
    public function test_field_terlarang_kosong_tetap_tidak_diteruskan(): void
    {
        $v = AturanTagihan::isi(['nominal' => '100', 'jatuh_tempo' => '2026-09-30', 'mahasiswa_id' => null, 'status' => ''], false);
        $this->assertArrayNotHasKey('mahasiswa_id', $v);
        $this->assertArrayNotHasKey('status', $v);
    }
}
