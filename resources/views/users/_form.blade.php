@csrf

{{-- Input Hidden Version untuk Optimistic Locking pada Mode Edit --}}
@if (isset($user) && $user->exists && !empty($version))
    <input type="hidden" name="version" value="{{ $version }}">
@endif

<div class="space-y-6" x-data="{ showPassword: false }">

    <!-- Pesan Error Global -->
    @if ($errors->any())
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
            <p class="font-bold mb-1">Periksa kembali formulir Anda:</p>
            <ul class="list-disc pl-5 space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Nama Lengkap -->
        <div>
            <label for="nama" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $user->nama ?? '') }}" required
                maxlength="150" placeholder="Contoh: Dr. Ahmad Hidayat, M.Pd."
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nama') border-rose-500 bg-rose-50/50 @enderror">
            @error('nama')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Username -->
        <div>
            <label for="username" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Username Sistem <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="username" name="username" value="{{ old('username', $user->username ?? '') }}"
                required maxlength="100" placeholder="Contoh: ahmadhidayat"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('username') border-rose-500 bg-rose-50/50 @enderror">
            @error('username')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Alamat Email <span class="text-rose-500">*</span>
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                maxlength="190" placeholder="nama@ilyas.ac.id"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('email') border-rose-500 bg-rose-50/50 @enderror">
            @error('email')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nomor Telepon -->
        <div>
            <label for="telepon" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Nomor Telepon / WhatsApp
            </label>
            <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $user->telepon ?? '') }}"
                maxlength="30" placeholder="Contoh: 081234567890"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('telepon') border-rose-500 bg-rose-50/50 @enderror">
            @error('telepon')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Status Akun -->
        <div>
            <label for="status" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Status Akun <span class="text-rose-500">*</span>
            </label>
            <select id="status" name="status" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('status') border-rose-500 bg-rose-50/50 @enderror">
                <option value="aktif" @selected(old('status', $user->status ?? 'aktif') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $user->status ?? '') === 'nonaktif')>Nonaktif</option>
            </select>
            @error('status')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Pengguna -->
        <div>
            <label for="password" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Kata Sandi Pengguna {{ isset($user) && $user->exists ? '(Kosongkan jika tidak diubah)' : '*' }}
            </label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                    {{ isset($user) && $user->exists ? '' : 'required' }} maxlength="72"
                    placeholder="Min. 12 karakter (huruf besar/kecil, angka, simbol)"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 pr-12 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('password') border-rose-500 bg-rose-50/50 @enderror">

                <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs font-semibold focus:outline-none">
                    <span x-text="showPassword ? 'Sembunyikan' : 'Lihat'"></span>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <!-- Peran Pengguna (Roles) -->
    <div class="border-t border-slate-200 pt-6">
        <label class="block text-xs font-bold text-slate-700 mb-3 uppercase tracking-wider">
            Pilih Peran (Role) Pengguna <span class="text-rose-500">*</span>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($roles as $role)
                @php
                    $isAssigned = isset($user) && $user->roles->contains($role->id);
                @endphp
                <label
                    class="relative flex items-start p-4 rounded-xl border border-slate-200 bg-white hover:border-siakad-dark cursor-pointer transition-all shadow-sm">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, old('roles', $isAssigned ? $user->roles->pluck('id')->toArray() : [])))
                            class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark">
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="font-bold text-slate-800 block">{{ $role->nama }}</span>
                        <span class="text-[11px] text-slate-500 font-mono">{{ $role->kode }}</span>
                    </div>
                </label>
            @endforeach
        </div>
        @error('roles')
            <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Konfirmasi Kata Sandi Admin (Wajib untuk Mengesahkan Aksi Backend) -->
    <div class="border-t border-slate-200 pt-6 bg-slate-50/80 -mx-6 -mb-6 p-6 rounded-b-xl">
        <div class="max-w-md">
            <label for="current_password" class="block text-xs font-bold text-slate-800 mb-1 uppercase tracking-wider">
                Konfirmasi Kata Sandi Admin Anda <span class="text-rose-500">*</span>
            </label>
            <p class="text-xs text-slate-500 mb-3">
                Masukkan kata sandi akun admin Anda saat ini untuk mengesahkan penyimpanan/perubahan data akun pengguna.
            </p>

            <input type="password" id="current_password" name="current_password" required maxlength="72"
                autocomplete="current-password" placeholder="Masukkan kata sandi admin Anda..."
                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark @error('current_password') border-rose-500 bg-rose-50/50 @enderror">

            @error('current_password')
                <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

</div>
