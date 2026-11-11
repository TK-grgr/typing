@php
use Illuminate\Support\Facades\Auth;
@endphp
@extends('layouts.app') 
@section('main') 
<div class="wrapper">
    <div class="flex-item">
        <a href="">タイピング</a>
        <a href="{{ route('Rank.index') }}">ランキング</a>
    </div>
    <a href="">メニュー作成</a>
    @if (Auth::check() && Auth::user()->role == 1)
        <a href="{{ route('admin.index') }}">設定管理</a>
    @endif
    
</div>
@endsection