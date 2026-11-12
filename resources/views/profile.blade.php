@extends('layouts.app') 
@section('main') 
<div class="wrapper">
    <div class="blank"></div>
    <div class="blank"></div>
    <div class="blank"></div>
        <div class="profile">
            <div class="profile-top">
            <h2>ユーザー名:{{ $profiles->name }}</h2>
            <a class="profile-btn" href="{{ route('result.index') }}">過去の記録</a>
            </div>
            <div class="blank"></div>
    <div class="blank"></div>
    <div class="blank"></div>
        </div>
</div>
@endsection