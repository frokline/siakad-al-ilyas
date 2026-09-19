@extends('layouts.pengumuman')
@section('title', 'Edit draf')
@section('content')
    <h1>Edit draf pengumuman</h1>
    @include('pengumuman._search')
    <form class="card" method="post" action="{{ route('pengumuman.update', $item) }}">
        @method('PATCH')@include('pengumuman._form')</form>
@endsection
