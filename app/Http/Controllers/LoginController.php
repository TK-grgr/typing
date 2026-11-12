<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function Login()
    {
        return view('login');
    }
    public function LoginCheck(Request $request)
    {
        //viewからemailとpasswordを受け取り
        $credentials = $request->only('email','password');
        if(Auth::attempt($credentials)){
            $request->session()->regenerate();
            //ログイン後のページを入れる
            return redirect('/top');
            //ログイン後ページの認証機能は未設定
            
        }

            return back()->withErrors([
                'email' => 'メールアドレスまたはパスワードが違います。',
            ]);
    }
    public function Logout()
    {
        return view('login');
    }
    public function PassChange(){
        return view('passChange');
    }
}
