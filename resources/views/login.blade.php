@extends('layout')
@section('title','Masuk · Berkah 3D')
@section('content')
<main class="berkah-login">
    <section class="berkah-login-card" aria-labelledby="login-title">
        <img class="berkah-login-logo" src="{{ asset('images/logo-berkah-3d.png') }}" alt="Berkah 3D" width="152" height="152">
        <h1 id="login-title">Selamat datang</h1>
        <p class="berkah-login-description">Masuk untuk mengelola keuangan Berkah 3D.</p>
        @if($errors->any())
            <div class="alert error" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('login') }}">
            @csrf
            <label>Username<input type="text" name="username" value="{{ old('username') }}" placeholder="Masukkan username" maxlength="100" autocomplete="username" required autofocus></label>
            <label>Kata sandi<input type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required></label>
            <button type="submit" class="btn">Masuk</button>
        </form>
        <p class="berkah-login-foot">Akses pengelola dan pemodal</p>
    </section>
</main>
@endsection
