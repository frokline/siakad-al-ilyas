<?php

namespace Tests\Unit;

use App\Models\Materi;
use App\Rules\TautanMateri;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MateriAturanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['materi.host_tautan' => ['docs.google.com', 'youtu.be']]);
    }
    public function test_https_host_yang_diizinkan_diterima(): void
    {
        foreach (['https://docs.google.com/document/d/contoh', 'https://youtu.be/contoh'] as $url) {
            $this->assertTrue(Validator::make(['url' => $url], ['url' => new TautanMateri()])->passes());
        }
    }
    public function test_skema_host_samaran_dan_kredensial_ditolak(): void
    {
        foreach (
            [
                'javascript:alert(1)',
                'http://youtu.be/x',
                'https://youtu.be.evil.example/x',
                'https://evil.example/?next=https://youtu.be',
                'https://user:pass@youtu.be/x',
                'https://127.0.0.1/x',
                '//youtu.be/x',
                'https://youtu.be:444/x',
                'https://youtu.be\\@evil.example/x'
            ] as $url
        ) {
            $this->assertFalse(Validator::make(['url' => $url], ['url' => new TautanMateri()])->passes(), $url);
        }
    }
    public function test_token_form_berubah_bersama_revisi_dan_identitas(): void
    {
        $m = new Materi();
        $m->id = 10;
        $m->revisi = 1;
        $awal = $m->versiForm();
        $this->assertSame(64, strlen($awal));
        $m->revisi = 2;
        $this->assertNotSame($awal, $m->versiForm());
        $m->id = 11;
        $m->revisi = 1;
        $this->assertNotSame($awal, $m->versiForm());
    }
    public function test_materi_draf_arsip_dan_jadwal_masa_depan_belum_terbit(): void
    {
        $m = new Materi();
        foreach ([Materi::DRAF, Materi::ARSIP] as $status) {
            $m->status = $status;
            $m->terbit_at = now('UTC')->subMinute();
            $this->assertFalse($m->sudahTerbit());
        }
        $m->status = Materi::TERBIT;
        $m->terbit_at = now('UTC')->addDay();
        $this->assertFalse($m->sudahTerbit());
        $m->terbit_at = now('UTC')->subMinute();
        $this->assertTrue($m->sudahTerbit());
    }
}
