<?php

namespace Database\Seeders;

use App\Models\Detail;
use Illuminate\Database\Seeder;

class DetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Detail::truncate();

        $details = [

            // 実践長文①
            1 => [
                '今日はとても良い天気です。',
                '学校へ向かう途中で桜を見ました。',
                '春になると新しいことを始めたくなります。',
                'タイピング練習は継続が大切です。',
                '正確に入力することを意識しましょう。',
                '慣れてきたら入力速度も向上します。',
                '毎日十分でも大きな成果になります。',
                '集中して取り組むことが重要です。',
                'ミスを減らすことも大切です。',
                '練習を続けて上達を目指しましょう。',
            ],

            // 実践長文②
            2 => [
                'プログラミングは問題解決の手段です。',
                '設計を丁寧に行うことで品質が向上します。',
                '変数名や関数名は分かりやすくしましょう。',
                'コードレビューは大きな学びになります。',
                'エラーが発生したら原因を切り分けます。',
                'ログの確認は調査の第一歩です。',
                '小さな機能から順番に実装します。',
                '動作確認を忘れずに行います。',
                'バックアップは定期的に取得します。',
                'チーム開発ではGitが活躍します。',
            ],

            // 実践短文①
            3 => [
                'おはようございます。',
                'こんにちは。',
                'こんばんは。',
                'よろしくお願いします。',
                'ありがとうございました。',
                '失礼します。',
                'お疲れ様です。',
                '承知しました。',
                '確認しました。',
                'よろしくお願いいたします。',
            ],

            // 実践短文②
            4 => [
                '本日も頑張りましょう。',
                '資料を送付します。',
                '会議を開始します。',
                '作業を完了しました。',
                '内容を確認してください。',
                'スケジュールを共有します。',
                '修正をお願いします。',
                '準備ができました。',
                '問題ありません。',
                '引き続きよろしくお願いします。',
            ],

            // ゲーム長文①
            5 => [

                [
                    'text' => '勇者は仲間と共に魔王城へ向かった。',
                    'roman' => 'yuushahanakamatotomonimaoujouemukatta'
                ],

                [
                    'text' => '森の奥には巨大なドラゴンが眠っていた。',
                    'roman' => 'morinookunihakyodainadoragonganemutteita'
                ],

                [
                    'text' => '伝説の剣は神殿の奥深くに存在した。',
                    'roman' => 'densetsunokenhashindennookufukakunisonzaishita'
                ],

                [
                    'text' => '冒険者たちは新たな依頼を受けた。',
                    'roman' => 'boukensyatachihaaratanairaiwouketa'
                ],

                [
                    'text' => '魔法使いは強力な呪文を唱えた。',
                    'roman' => 'mahoutsukaihakyouryokunajumonwotonaeta'
                ],

                [
                    'text' => '戦士は盾を構えて攻撃を防いだ。',
                    'roman' => 'senshihatatewokamaetekougekiwofuseida'
                ],

                [
                    'text' => '王国は平和を取り戻した。',
                    'roman' => 'oukokuhaheiwawotorimodoshita'
                ],

                [
                    'text' => '宝箱の中には貴重なアイテムがあった。',
                    'roman' => 'takarabakononakanihakichounaitemugaatta'
                ],

                [
                    'text' => '敵の大軍が城へ迫っていた。',
                    'roman' => 'tekinotaigungashirohesematteita'
                ],

                [
                    'text' => '最後の決戦が始まろうとしていた。',
                    'roman' => 'saigonokessenhahajimaroutoshiteita'
                ],

            ],

            // ゲーム長文②
            6 => [

                [
                    'text' => '宇宙船は未知の惑星へ到着した。',
                    'roman' => 'uchuusenhamichinowakuseietouchakushita'
                ],

                [
                    'text' => '探索隊は周囲の調査を始めた。',
                    'roman' => 'tansakutaihashuinochousawohajimeta'
                ],

                [
                    'text' => '巨大な生物の足跡が発見された。',
                    'roman' => 'kyodainaseibutsunoashiatogahakkensareta'
                ],

                [
                    'text' => '基地との通信は良好だった。',
                    'roman' => 'kichitonotsuushinharyoukoudatta'
                ],

                [
                    'text' => 'エネルギー残量は十分にあった。',
                    'roman' => 'enerugiizanryouhajuubunniatta'
                ],

                [
                    'text' => '隊員たちは慎重に行動した。',
                    'roman' => 'taiintachihashinchounikoudoushita'
                ],

                [
                    'text' => '古代文明の遺跡を発見した。',
                    'roman' => 'kodaibunmeinosekiwihakkenshita'
                ],

                [
                    'text' => '不思議な装置が作動した。',
                    'roman' => 'fushiginasouchigasadoushita'
                ],

                [
                    'text' => '危険区域への侵入は禁止されていた。',
                    'roman' => 'kikenkuikihenoshinnyuukinshisareteita'
                ],

                [
                    'text' => 'ミッションは順調に進行した。',
                    'roman' => 'misshonhajunchounishinkoushita'
                ],

            ],

            // ゲーム短文①
            7 => [

                [
                    'text' => 'スライムを倒した。',
                    'roman' => 'suraimuwotaoshita'
                ],

                [
                    'text' => 'レベルアップした。',
                    'roman' => 'reberuappushita'
                ],

                [
                    'text' => '宝箱を発見した。',
                    'roman' => 'takarabakowohakkenshita'
                ],

                [
                    'text' => '経験値を獲得した。',
                    'roman' => 'keikenchiwokakutokushita'
                ],

                [
                    'text' => '新しい武器を入手した。',
                    'roman' => 'atarashiibukiwonyuushushita'
                ],

                [
                    'text' => 'クエストを達成した。',
                    'roman' => 'kuesutowotasseishita'
                ],

                [
                    'text' => '仲間が増えた。',
                    'roman' => 'nakamagafueta'
                ],

                [
                    'text' => 'ボスを撃破した。',
                    'roman' => 'bosuogekihashita'
                ],

                [
                    'text' => '回復薬を使用した。',
                    'roman' => 'kaifukuyakuoshiyoushita'
                ],

                [
                    'text' => 'セーブしました。',
                    'roman' => 'seebushimashita'
                ],

            ],

            // ゲーム短文②
            8 => [

                [
                    'text' => '敵を発見した。',
                    'roman' => 'tekiwohakkenshita'
                ],

                // [
                //     'text' => '攻撃を回避した。',
                //     'roman' => 'kougekiwokaihishita'
                // ],

                // [
                //     'text' => '必殺技を発動した。',
                //     'roman' => 'hissatsuwazawohatsudoushita'
                // ],

                // [
                //     'text' => '防御に成功した。',
                //     'roman' => 'bougyoniseikoushita'
                // ],

                // [
                //     'text' => 'スコアを獲得した。',
                //     'roman' => 'sukoawokakutokushita'
                // ],

                // [
                //     'text' => 'コンボが繋がった。',
                //     'roman' => 'konbogatsunagatta'
                // ],

                // [
                //     'text' => 'ゴールに到着した。',
                //     'roman' => 'goorunitouchakushita'
                // ],

                // [
                //     'text' => 'チェックポイントを通過した。',
                //     'roman' => 'chekkupointowotsuukashita'
                // ],

                // [
                //     'text' => 'ランキングに登録した。',
                //     'roman' => 'rankingunitouroku shita'
                // ],

                // [
                //     'text' => 'ゲームクリア。',
                //     'roman' => 'geemukuria'
                // ],

            ],

        ];

        foreach ($details as $questionId => $texts) {

            foreach ($texts as $text) {

                if (is_array($text)) {

                    Detail::create([
                        'question_id' => $questionId,
                        'text' => $text['text'],
                        'roman' => $text['roman'],
                    ]);

                } else {

                    Detail::create([
                        'question_id' => $questionId,
                        'text' => $text,
                        'roman' => null,
                    ]);

                }

            }

        }
    }
}