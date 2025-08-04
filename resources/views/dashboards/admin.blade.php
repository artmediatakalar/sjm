@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard Admin</h1>
    <p>Selamat datang, {{ Auth::user()->name }}</p>
    <h1><p>Kode Referral Anda: <strong>{{ auth()->user()->referral_code }}</strong></p></h1>
    <ul>
        <li>Manajemen Produk</li>
        <li>Validasi Pendaftaran</li>
        <li>Monitoring Penjualan</li>
    </ul>
</div>
@endsection
