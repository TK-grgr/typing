@extends('layouts.app') 
@section('main')
<div class="wrapper">
    <div class="blank"></div>
    <div class="blank"></div>
    <div class="blank"></div>
    <table>
        <tr>
            <th>タイピングモード</th>
            @foreach($sortableColumns as $column => $label)

            <th>
                <a class="black-text"
                href="{{ route('result.index',[
                        'sort' => $column,
                        'direction' =>
                            ($sort === $column && $direction === 'asc')
                            ? 'desc'
                            : 'asc'
                ]) }}">

                    {{ $label }}

                <span class="sort-icon">
                @if($sort === $column)
                    {{ $direction === 'asc' ? '▲' : '▼' }}
                @else
                    ▽
                @endif
            </span>

                </a>
            </th>

            @endforeach
        </tr>
        @foreach ($results as $result)
        <tr class="center-item">
            <td>{{ $result->question_id }}</td>
            <td>{{ $result->score }}</td>
            <td>{{ $result->correct }}</td>
            <td>{{ $result->miss }}</td>
            <td>{{ $result->input_time }}</td>
            <td>{{ $result->created_at->format('Y/m/d') }}</td>
        </tr>
        @endforeach
    </table>
</div> 
@endsection