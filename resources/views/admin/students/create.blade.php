@extends('layouts.app')
@section('title', 'Tambah Mahasiswa')
@section('heading', 'Tambah Mahasiswa')

@section('content')
<form method="POST" action="{{ route('admin.students.store') }}">
    @csrf
    @include('admin.students._form')
</form>
@endsection
