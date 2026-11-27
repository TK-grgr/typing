<?php

use App\Models\Question;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

// メモリDBでタイピングの画面を描画し、JavaScriptテストに応答を渡す。
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$request = Request::create($argv[1]);
$app->instance('request', $request);
config([
    'database.default' => 'typing_test',
    'database.connections.typing_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    'session.driver' => 'array',
    'hashing.bcrypt.rounds' => 4,
    'app.debug' => false,
]);

foreach ([
    '2026_10_30_010756_create_questions_table.php',
    '2026_10_30_014401_create_details_table.php',
    '2026_10_30_024221_create_users_table.php',
] as $migration) {
    (require database_path('migrations/'.$migration))->up();
}

// 本番用マイグレーションを変更せず、メモリDBだけを合意済みの構造に揃える。
if (Schema::hasColumn('questions', 'no')) {
    Schema::table('questions', fn ($table) => $table->dropColumn('no'));
}

// 4条件に加え、非公開・空セット・読みが使えないセットを用意する。
$fixtures = [
    [21, 2, 1, 1, '青のセット', [['青', 'あお']]],
    [11, 2, 1, 1, 'れんしゅうのたんご', [['新聞', 'しんぶん'], ['学校', 'がっこう']]],
    [12, 1, 1, 1, 'れんしゅうのながぶん', [['明日は学校に行きます。', 'あしたはがっこうにいきます。'], ['朝、窓を開けた。', 'あさ、まどをあけた。'], ['鳥が鳴いている。', 'とりがないている。']]],
    [13, 2, 2, 1, 'げーむのたんご', [['情報処理', 'じょうほうしょり']]],
    [14, 1, 2, 1, 'げーむのながぶん', [['青い空を見上げた。', 'あおいそらをみあげた。'], ['海で遊んだ。', 'うみで あそんだ。'], ['朝は涼しかった。', 'あさはすずしかった。'], ['今日はいい天気。', 'きょうはいいてんき。']]],
    [22, 2, 1, 1, '黄色のセット', [['黄色', 'きいろ']]],
    [31, 2, 1, 0, 'ひこうかい', [['赤', 'あか']]],
    [32, 2, 1, 1, 'からのセット', []],
    [33, 2, 1, 1, 'よみがふせいなセット', [['赤', 'あか'], ['もんだい', '漢字の問題']]],
    [34, 2, 1, 1, 'よみがくうはくのセット', [['問題', '　 ']]],
    [35, 2, 1, 1, 'よみがからのセット', [['問題', '']]],
];

if (($argv[2] ?? '') === 'digit-reading') {
    $fixtures[] = [36, 2, 2, 1, '数字のセット', [['ん7あ', 'ん7あ']]];
}
if (($argv[2] ?? '') === 'game-regressions') {
    $fixtures[] = [37, 2, 2, 1, 'ゲームの回帰セット', [['新聞', 'しんぶん'], ['学校', 'がっこう']]];
    $fixtures[] = [38, 2, 2, 1, '読みのないゲームセット', [['青', '']]];
}

foreach ($fixtures as [$id, $type, $level, $release, $title, $lines]) {
    if (($argv[2] ?? '') === 'empty' && $id !== 32) {
        continue;
    }
    $set = new Question(compact('type', 'level', 'release', 'title'));
    $set->id = $id;
    $set->save();
    foreach ($lines as [$text, $reading]) {
        $detail = $set->details()->make(['text' => $text]);
        $detail->reading = $reading;
        $detail->save();
    }
}

if (($argv[2] ?? '') !== 'guest') {
    $email = 'typing-test@example.test';
    $password = 'test-only-password';
    User::create(['name' => 'Typing Test', 'email' => $email, 'password' => Hash::make($password), 'school_id' => 1, 'class_id' => 1]);
    if (($argv[2] ?? '') === 'different-user') {
        $email = 'typing-other@example.test';
        User::create(['name' => 'Other Typing Test', 'email' => $email, 'password' => Hash::make($password), 'school_id' => 1, 'class_id' => 1]);
    }
    if (!Auth::attempt(compact('email', 'password'))) {
        throw new RuntimeException('Test login failed.');
    }
}

$response = $kernel->handle($request);
echo json_encode([
    'status' => $response->getStatusCode(),
    'location' => $response->headers->get('Location'),
    'html' => $response->isSuccessful() ? $response->getContent() : '',
], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
