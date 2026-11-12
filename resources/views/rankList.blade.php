@extends('layouts.app') 
@section('main')
<div class="wrapper">
    <div class="blank"></div>
    <div class="blank"></div>
    <div class="blank"></div>
    <div class="rank-tabs">
    @foreach($tabs as $tab)
        <a
        class="rank-tab
        {{ ($type == $tab['type']
        && $level == $tab['level'])
        ? 'active'
        : '' }}"
        href="{{ route('Rank.index',[
            'type' => $tab['type'],
            'level' => $tab['level']
        ]) }}">

            {{ $tab['label'] }} 
        </a>

        @endforeach
    </div>
    <div class="rank-area">
    <table>
        <tr>
            <th>順位</th><th>タイピング名</th><th>ユーザー名</th><th>スコア</th><th>入力日</th>
        </tr>
        @foreach ($results as $index => $result)
        <tr class="center-item">
            <td>{{ $index+  1 }}位</td>
            <td>{{ $result->question?->title ?? '削除済み問題' }}</td>
            <td>{{ $result->user?->name ?? '削除済みユーザー' }}</td>
            <td>{{ $result->score }}</td>
            <td>{{ $result->created_at->format('Y/m/d') }}</td>
        </tr>
        @endforeach
<tr class="center-item my-rank">

@if($myResult)
    <td>{{ $myRank }}位</td>
    <td>{{ $myResult->question?->title ?? '削除済み問題' }}</td>
    <td>{{ $myResult->user->name }}</td>
    <td>{{ $myResult->score }}</td>
    <td>{{ $myResult->created_at->format('Y/m/d') }}</td>
</tr>
    @else
        <td colspan = 5>このモードの記録はありません</td>
    @endif
    </tr>
    </table>
    </div>
</div>

@endsection