@extends('layout')
@section('title', 'Ubah Password')
@section('content')
<div class="page-heading"><div><p class="eyebrow">PENGATURAN AKUN</p><h1>Ubah password<span class="green">.</span></h1><p class="muted">Perbarui password untuk akun {{ auth()->user()->username }}.</p></div></div>
@if(session('success'))<div class="alert" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="card password-card"><div class="card-heading"><div><h2>Password akun</h2><p class="muted">Gunakan minimal 8 karakter untuk password baru.</p></div></div><form action="{{ route('password.update') }}" method="post" class="password-form">@csrf @method('PUT')
<label>Username<input value="{{ auth()->user()->username }}" autocomplete="username" readonly></label>
<label>Password lama<input type="password" name="current_password" autocomplete="current-password" required autofocus></label>
<label>Password baru<input type="password" name="password" minlength="8" maxlength="255" autocomplete="new-password" required></label>
<label>Konfirmasi password baru<input type="password" name="password_confirmation" minlength="8" maxlength="255" autocomplete="new-password" required></label>
<div><button class="btn">Simpan password</button></div>
</form></section>
@endsection
