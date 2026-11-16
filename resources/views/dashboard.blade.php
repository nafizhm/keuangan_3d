@extends('layout')
@section('title','Dashboard')
@section('content')
<div class="page-heading"><div><p class="eyebrow">RINGKASAN USAHA</p><h1>Keuangan dalam satu pandangan<span class="green">.</span></h1><p class="muted">Pantau setiap rupiah, bangun usaha dengan lebih terarah.</p></div>@if(auth()->user()->role==='pengelola')<a class="btn" href="{{ route('transactions') }}#catat">+ Catat transaksi</a>@endif</div>
<div class="stats"><section class="stat balance"><div><span>Saldo usaha</span><span>◉</span></div><h2>Rp {{ number_format($income-$expense,0,',','.') }}</h2><small>Total pemasukan dikurangi pengeluaran</small></section><section class="stat"><div><span>Total pemasukan</span><span class="green">↙</span></div><h2>Rp {{ number_format($income,0,',','.') }}</h2><small>Seluruh pemasukan & modal usaha</small></section><section class="stat"><div><span>Total pengeluaran</span><span class="orange">↗</span></div><h2>Rp {{ number_format($expense,0,',','.') }}</h2><small>Seluruh biaya operasional usaha</small></section></div>
<section class="insight"><span class="insight-icon">✧</span><div><strong>Usaha transparan, tumbuh bersama.</strong><p>Setiap pengeluaran dilengkapi bukti pembelian yang bisa dilihat pengelola dan pemodal.</p></div><span class="tag">TERCATAT & TERPANTAU</span></section>
<section class="card"><div class="card-heading"><div><h2>Transaksi terbaru</h2><p class="muted">Lima aktivitas keuangan terakhir</p></div><a class="text-link" href="{{ route('transactions') }}">Lihat semua →</a></div>@include('partials.transactions')</section>
<p class="footer-note">Ruang3D · Ruang untuk usaha yang terus tumbuh.</p>
@endsection
