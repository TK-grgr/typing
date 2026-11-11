@extends('layouts.app') 
@section('main') 
<div class="wrapper">
    

    <div class="admin-tabs">

        <a class="admin-tab active">
            ユーザー管理
        </a>

        <a class="admin-tab" href="{{ route('admin.menu') }}">
            タイピングメニュー管理
        </a>

    </div>

    <h2>ユーザー管理</h2>

    <table class="admin-table">

        <tr>
            <th>ユーザー名</th>
            <th>メールアドレス</th>
            <th>所属校</th>
            <th>クラス</th>
            <th>権限</th>
            <th>操作</th>
        </tr>

        @foreach($users as $user)

        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->school->name ?? '' }}</td>
            <td>{{ $user->classroom->name ?? '' }}</td>

            <td>

                <span class="
                    role-badge
                    {{ $user->role == 1 ? 'admin-role' : 'normal-role' }}
                ">
                    {{ $user->role == 1 ? '管理者' : '一般' }}
                </span>

            </td>

            <td>

                <a
                    href="{{ route('admin.show',$user->id) }}"
                    class="edit-btn"
                >
                    編集
                </a>

            </td>

        </tr>

        @endforeach

    </table>

</div>
@endsection