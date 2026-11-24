@extends('layouts.sub-app') 
@section('main') 
<div class="wrapper">
    <div class="blank"></div>
    <div class="blank"></div>
        <div class="login-form">
            <div class="right-item">
                <a href="{{ route('login') }}"><img class="signup-img" src="{{ asset('images/TypeUpロゴ.png') }}"></a>
            </div>
            <h1 class="center-item">新規登録</h1>
            <form action="{{ route('signup.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>ユーザー名</label><br>
                    <input class="form-control" type="text" name="name"  placeholder="ユーザー名を入力">
                    @error('name')
                    <div style="color:red">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>所属校</label><br>
                    <select class="form-control" name="school_id" id="school_id">
                        <option value="">--所属校を選択--</option>
                        {{-- テストデータです --}}
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                    @error('school_id')
                    <div style="color:red">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>クラス</label><br>
                    <select class="form-control" name="class_id" id="class_id" disabled>
                        <option value="">--クラスを選択--</option>
                        {{-- テストデータです --}}
                        @foreach ($classes as $class)
                        <option value="{{ $class->id }}" data-school="{{ $class->school_id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                    <div style="color:red">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>メールアドレス</label><br>
                    <input class="form-control" type="email" name="email"  placeholder="メールアドレスを入力">
                    @error('email')
                    <div style="color:red">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>パスワード</label><br>
                    <input class="form-control" type="password" name="password"  placeholder="パスワードを入力">
                    @error('password')
                    <div style="color:red">{{ $message }}</div>
                    @enderror
                </div>
                <div class="blank"></div>
                <button type="submit" class="form-group login-btn">
                    新規登録
                </button>
            </form>
            <div class="sign-up">
                <div class="blank"></div>アカウントをお持ちの方&emsp;<a href="{{ route('login') }}">ログイン →</a><div class="blank"></div>
            </div>
        </div>
    </div>
    <script>

const schoolSelect =
    document.getElementById('school_id');

const classSelect =
    document.getElementById('class_id');

schoolSelect.addEventListener('change', () => {

    const schoolId =
        schoolSelect.value;

    classSelect.disabled = false;

    const options =
        classSelect.querySelectorAll('option');

    options.forEach(option => {

        if(option.value === ''){
            option.hidden = false;
            return;
        }

        if(option.dataset.school === schoolId){
            option.hidden = false;
        }else{
            option.hidden = true;
        }

    });

    classSelect.value = '';

});


</script>
@endsection