@php
    $edit = isset($file) && $file->exists;
    $teks = static function (string $key, string $fallback = ''): string {
        if (!session()->hasOldInput($key)) {
            return $fallback;
        }
        return is_string(old($key)) ? old($key) : '';
    };
@endphp
<form action="{{ $edit ? route('berkas.update', $file) : route('berkas.store') }}" method="post"
    enctype="multipart/form-data" class="stack">
    @csrf
    @if ($edit)
        @method('PATCH')
        <input type="hidden" name="versi" value="{{ $teks('versi', $file->versiForm()) }}">
    @else
        <input type="hidden" name="upload_token" value="{{ $teks('upload_token', $token) }}">
    @endif
    <label for="label">Label berkas</label>
    <input id="label" name="label" required maxlength="200"
        value="{{ $teks('label', $edit ? $file->label : '') }}">
    <label for="keterangan">Keterangan <span class="muted">(opsional)</span></label>
    <textarea id="keterangan" name="keterangan" rows="4" maxlength="2000">{{ $teks('keterangan', $edit ? $file->keterangan ?? '' : '') }}</textarea>
    @if ($edit)
        <label for="alasan">Alasan perubahan</label>
        <textarea id="alasan" name="alasan" rows="3" required minlength="10" maxlength="2000">{{ $teks('alasan') }}</textarea>
        <p class="muted">Isi file dan nama asli tetap. Untuk dokumen versi baru, unggah sebagai berkas baru.</p>
    @else
        <label for="file">Pilih berkas</label>
        <input id="file" name="file" type="file" required accept=".pdf,.jpg,.jpeg,.png"
            aria-describedby="batas-file">
        <p id="batas-file" class="muted">PDF, JPG, atau PNG; maksimal 20 MB. Jika validasi gagal, pilih ulang file
            sebelum mengirim.</p>
    @endif
    <div class="buttons"><button type="submit">{{ $edit ? 'Simpan perubahan' : 'Unggah berkas' }}</button>
        <a
            href="{{ $edit ? route('berkas.edit', $file) : route('berkas.create') }}">{{ $edit ? 'Muat ulang versi terbaru' : 'Formulir baru' }}</a>
    </div>
</form>
