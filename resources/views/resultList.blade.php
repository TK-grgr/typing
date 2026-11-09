@extends('layouts.app') 
@section('main')
<div class="wrapper">
    <table>
        <tr>
            <th>タイピングモード</th><th>スコア</th><th>入力数</th><th>ミス数</th><th>入力時間</th><th>ランキング</th><th>入力日</th>
        </tr>
        @foreach ($results as $result)
        <tr>
            <td>{{ $result->question_id }}</td>
            <td>{{ $result->score }}</td>
            <td>{{ $result->correct }}</td>
            <td>{{ $result->miss }}</td>
            <td>{{ $result->correct }}</td>
            <td>{{ $result->input_time }}</td>
            <td>{{ $result->created_at }}</td>
        </tr>
        @endforeach
    </table>
</div> 
@endsection