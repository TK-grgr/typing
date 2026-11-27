<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

// ユーザーと出題条件を紐づけた、有効期限付きのプレイトークンを扱う。
class TypingAttempt
{
    public function issue(int $userId, int $setId, string $mode, string $type, ?string $revision = null): string
    {
        return Crypt::encrypt([
            'id' => (string) Str::uuid(), 'userId' => $userId, 'setId' => $setId,
            'mode' => $mode, 'type' => $type, 'revision' => $revision, 'expires' => now()->addHours(8)->timestamp,
        ]);
    }

    // 復号後に利用者と有効期限を確認してから、再開や保存に使う。
    public function read(string $token, int $userId): array
    {
        try {
            $attempt = Crypt::decrypt($token);
        } catch (DecryptException) {
            abort(422, 'プレイ情報を確認できません。');
        }

        abort_unless(is_array($attempt) && ($attempt['userId'] ?? null) === $userId && ($attempt['expires'] ?? 0) > now()->timestamp,
            422, 'プレイ情報が無効か、有効期限を過ぎています。');

        return $attempt;
    }
}
