@extends('layout')
@section('title','Dashboard')
@section('content')
<div class="page-heading"><div><p class="eyebrow">RINGKASAN USAHA</p><h1>Keuangan dalam satu pandangan<span class="green">.</span></h1><p class="muted">Pantau setiap rupiah, bangun usaha dengan lebih terarah.</p></div>@if(auth()->user()->role==='pengelola')<a class="btn" href="{{ route('transactions') }}#catat">+ Catat transaksi</a>@endif</div>
<div class="stats"><section class="stat balance"><div><span>Saldo usaha</span><span>◉</span></div><h2>Rp {{ number_format($income-$expense,0,',','.') }}</h2><small>Total pemasukan dikurangi pengeluaran</small></section><a class="stat stat-link" href="{{ route('transactions', ['type' => 'pemasukan']) }}"><div><span>Total pemasukan</span><span class="green">↙</span></div><h2>Rp {{ number_format($income,0,',','.') }}</h2><small>Lihat detail pemasukan &rarr;</small></a><a class="stat stat-link" href="{{ route('transactions', ['type' => 'pengeluaran']) }}"><div><span>Total pengeluaran</span><span class="orange">↗</span></div><h2>Rp {{ number_format($expense,0,',','.') }}</h2><small>Lihat detail pengeluaran &rarr;</small></a></div>
<section class="card"><div class="card-heading"><div><h2>Transaksi terbaru</h2><p class="muted">Lima aktivitas keuangan terakhir</p></div><a class="text-link" href="{{ route('transactions') }}">Lihat semua →</a></div>@include('partials.transactions')</section>
<p class="footer-note">Ruang3D · Ruang untuk usaha yang terus tumbuh.</p>
@endsection
