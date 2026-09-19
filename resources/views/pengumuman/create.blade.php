@extends('layouts.pengumuman')
@section('title', 'Buat pengumuman')
@section('content')
    <h1>Buat pengumuman</h1>
    @include('pengumuman._search')
    <form class="card" method="post" action="{{ route('pengumuman.store') }}">@include('pengumuman._form')</form>
@endsection
