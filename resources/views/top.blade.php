@extends('layouts.app') 
@section('main') 
<div class="wrapper">
    <div class="flex-item">
        <a href="">タイピング</a>
        <a href="{{ route('Rank.index') }}">ランキング</a>
    </div>
    <a href="">メニュー作成</a>
    {{-- <a href="">設定管理</a> --}}
</div>
@endsection