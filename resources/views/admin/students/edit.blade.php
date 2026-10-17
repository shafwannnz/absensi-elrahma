@extends('layouts.app')
@section('title', 'Ubah Mahasiswa')
@section('heading', 'Ubah Mahasiswa')
@section('subheading', $student->name.' · NIM '.$student->nim)

@section('content')
<form method="POST" action="{{ route('admin.students.update', $student) }}">
    @csrf @method('PUT')
    @include('admin.students._form', ['student' => $student])
</form>
@endsection
