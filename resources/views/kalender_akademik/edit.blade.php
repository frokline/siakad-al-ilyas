@extends('layouts.kalender_akademik')
@section('title', 'Edit Agenda')
@section('content')
    <h1>Edit agenda</h1>
    <p class="notice">Periksa tanggal dan jam sebelum menyimpan. Agenda kalender tidak otomatis mengubah jadwal kelas,
        presensi, atau batas KRS.</p>
    <form class="card form-card" method="post" action="{{ route('kalender.update', $agenda) }}">@include('kalender_akademik._form', ['baru' => false])
    </form>
@endsection
