@php
    $editing = $dosen->exists;

    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version ?? '') }}">

    @error('version')
        <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
            {{ $message }}
        </div>
    @enderror

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
            <a href="{{ route('admin.dosen.edit', $dosen) }}" class="font-semibold underline hover:text-amber-900">
                Muat ulang formulir dari data terbaru
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nama Dosen</span>
            <span class="mt-1 font-bold text-slate-800 block">{{ $dosen->user->nama }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Akun Terhubung</span>
            <a href="{{ route('admin.users.show', $dosen->user) }}"
                class="mt-1 font-mono font-semibold text-siakad-dark hover:underline block">
                {{ $dosen->user->username }}
            </a>
        </div>
    </div>

    <p class="text-xs text-slate-500 mb-6">Nama, email, telepon, dan akses akun dikelola melalui menu Pengguna.</p>
@else
    <div class="mb-6">
        <label for="user_id" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Akun Dosen
            <span class="text-rose-500">*</span></label>
        <select id="user_id" name="user_id" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('user_id') border-rose-500 bg-rose-50/50 @enderror">
            <option value="">Pilih akun dosen</option>

            @foreach ($daftarPengguna as $pengguna)
                <option value="{{ $pengguna->id }}" @selected($value('user_id') === (string) $pengguna->id)>
                    {{ $pengguna->nama }} — {{ $pengguna->username }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Pilih akun yang benar. Akun terhubung ditetapkan saat pembuatan.</p>

        @error('user_id')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
@endif

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="kode_dosen" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kode
                Dosen <span class="text-rose-500">*</span></label>
            <input id="kode_dosen" name="kode_dosen" type="text"
                value="{{ $value('kode_dosen', $dosen->kode_dosen) }}" maxlength="40" placeholder="Contoh: DSN-001"
                autocomplete="off" spellcheck="false" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kode_dosen') border-rose-500 bg-rose-50/50 @enderror">

            @error('kode_dosen')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nidn" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">NIDN
                <span class="text-slate-400 font-normal">— opsional</span></label>
            <input id="nidn" name="nidn" type="text" inputmode="numeric"
                value="{{ $value('nidn', $dosen->nidn) }}" maxlength="40" autocomplete="off" spellcheck="false"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nidn') border-rose-500 bg-rose-50/50 @enderror">
            <p class="mt-1 text-xs text-slate-500">Isi angka tanpa spasi. Kosongkan jika belum memiliki NIDN.</p>

            @error('nidn')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="gelar" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Gelar <span
                class="text-slate-400 font-normal">— opsional</span></label>
        <input id="gelar" name="gelar" type="text" value="{{ $value('gelar', $dosen->gelar) }}"
            maxlength="100" placeholder="Contoh: Lc., M.A."
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('gelar') border-rose-500 bg-rose-50/50 @enderror">

        @error('gelar')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status Dosen
            <span class="text-rose-500">*</span></label>
        <select id="status" name="status" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('status') border-rose-500 bg-rose-50/50 @enderror">
            @foreach ($statusOptions as $kodeStatus => $labelStatus)
                <option value="{{ $kodeStatus }}" @selected($value('status', $dosen->status) === $kodeStatus)>
                    {{ $labelStatus }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Pengaktifan kembali memerlukan akun aktif dengan peran Dosen. Status
            akses akun dikelola melalui menu Pengguna.</p>

        @error('status')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
