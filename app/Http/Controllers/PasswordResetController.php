<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(),['email' => 'required|email|exists:users,email']);

        if ($validator->fails()){
            return response()->json(['error' => '対象のメールアドレスのユーザーが存在しません'],400);
        }
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'パスワードリセットリンクメールを送信しました'],200)
        : response()->json(['message' => 'パスワードリセットリンクメールの送信に失敗しました'],400);
    }
}
