<?php

namespace App\View\Components;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\Component;
use Illuminate\View\View;

class StatusPembayaran extends Component
{
    public function __construct(public Tagihan $tagihan) {}
    public function render(): View
    {
        Gate::authorize('view', $this->tagihan);

        if (! Schema::hasTable((new Pembayaran())->getTable())) {
            return view('components.status-pembayaran', [
                'aktif' => null,
                'riwayat' => collect(),
                'bolehAjukan' => false,
            ]);
        }

        $q = Pembayaran::query()->where('tagihan_id', $this->tagihan->id);
        $aktif = (clone $q)->whereIn('status', [Pembayaran::MENUNGGU, Pembayaran::DITERIMA])->first();
        return view('components.status-pembayaran', [
            'aktif' => $aktif,
            'riwayat' => (clone $q)->orderByDesc('id')->limit(5)->get(),
            'bolehAjukan' => ! $aktif && Gate::allows('create', [Pembayaran::class, $this->tagihan])
        ]);
    }
}
