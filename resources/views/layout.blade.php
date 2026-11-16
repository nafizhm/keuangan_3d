<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title','Dashboard') · Ruang3D</title><link rel="stylesheet" href="{{ asset('app.css') }}"></head>
<body>
@auth
<aside class="sidebar"><a class="brand" href="{{ route('dashboard') }}"><span class="logo">3D</span> Ruang3D<span class="brand-dot">.</span></a><p class="eyebrow">KEUANGAN USAHA</p><nav><a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">▦ <span>Dashboard</span></a><a class="{{ request()->routeIs('transactions*')?'active':'' }}" href="{{ route('transactions') }}">⇄ <span>Transaksi</span></a><a class="{{ request()->routeIs('password.*') ? 'active' : null }}" href="{{ route('password.edit') }}">⚿ <span>Ubah Password</span></a></nav><div class="sidebar-note">Dari ide menjadi nyata.<br><strong>Keuangan tetap tertata.</strong></div><div class="profile"><span class="avatar">{{ mb_substr(auth()->user()->name,0,1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small></div><form method="post" action="{{ route('logout') }}">@csrf<button class="logout" title="Keluar" aria-label="Keluar">↪</button></form></div></aside>
<main class="main"><header class="topbar"><span>Workspace <span class="muted">/</span> <strong>@yield('title','Dashboard')</strong></span><span class="date">{{ now()->translatedFormat('d F Y') }}</span></header><div class="content">@yield('content')</div></main>
@include('partials.attachment-modal')
@else @yield('content') @endauth
</body></html>
