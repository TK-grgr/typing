<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Result;
use App\Models\User;
use App\Support\TypingAttempt;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDOException;
use Tests\TestCase;

class TypingResultStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'typing_results_test',
            'database.connections.typing_results_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'session.driver' => 'array',
        ]);
        foreach ([
            '0001_01_01_000001_create_cache_table.php', '2026_10_30_010756_create_questions_table.php',
            '2026_10_30_014401_create_details_table.php', '2026_10_30_022130_create_results_table.php',
            '2026_10_30_024221_create_users_table.php',
        ] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        // 統合前のマイグレーションでも、メモリDBは合意済みの構造でテストする。
        if (Schema::hasColumn('questions', 'no')) {
            Schema::table('questions', fn ($table) => $table->dropColumn('no'));
        }
    }

    public function test_the_question_schema_requires_reading_and_has_no_sort_number(): void
    {
        $this->assertFalse(Schema::hasColumn('questions', 'no'));
        $reading = collect(Schema::getColumns('details'))->firstWhere('name', 'reading');
        $this->assertNotNull($reading);
        $this->assertFalse($reading['nullable']);
        $this->assertNull($reading['default']);

        $question = $this->question(1, 2);
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('NOT NULL constraint failed: details.reading');
        DB::table('details')->insert(['question_id' => $question->id, 'text' => 'テスト', 'reading' => null]);
    }

    public function test_it_recomputes_and_saves_all_four_modes(): void
    {
        $user = $this->player('owner@example.test');
        $this->actingAs($user);
        foreach ([['game', 'word', 2, 2, 1013], ['game', 'long', 2, 1, 1406],
            ['practice', 'word', 1, 2, 2000], ['practice', 'long', 1, 1, 203]] as [$mode, $type, $level, $kind, $score]) {
            $set = $this->question($level, $kind, str_repeat('あ', 125));
            $attempt = app(TypingAttempt::class)->issue($user->id, $set->id, $mode, $type);
            $this->postJson('/typing/result', ['attempt' => $attempt, 'correct' => 90, 'miss' => 30, 'elapsedMs' => 29999.5,
                'score' => 123456, 'user_id' => 999, 'minimumKeystrokes' => 1])
                ->assertOk()->assertJsonPath('score', $score)->assertJsonPath('rank', 1);
            $this->assertDatabaseHas('results', ['user_id' => $user->id, 'question_id' => $set->id,
                'score' => $score, 'correct' => 90, 'miss' => 30, 'input_time' => 29]);
        }
        $this->assertDatabaseCount('results', 4);
        $this->assertEquals(4, Result::query()->whereNotNull('created_at')->whereNotNull('updated_at')->count());
    }

    public function test_it_ranks_each_play_within_its_set_and_keeps_the_saved_rank_on_retry(): void
    {
        $user = $this->player('owner@example.test');
        $other = $this->player('other@example.test');
        $this->actingAs($user);
        $set = $this->question(1, 2);
        $otherSet = $this->question(1, 2);
        DB::table('results')->insert(['user_id' => $other->id, 'question_id' => $otherSet->id, 'score' => 10000,
            'correct' => 10, 'miss' => 0, 'input_time' => 1]);
        $plays = [];
        foreach ([[4, 1], [3, 2], [3, 2], [1, 4], [5, 1]] as [$correct, $rank]) {
            $attempt = app(TypingAttempt::class)->issue($user->id, $set->id, 'practice', 'word');
            $payload = ['attempt' => $attempt, 'correct' => $correct, 'miss' => 0, 'elapsedMs' => 1000];
            $response = $this->postJson('/typing/result', $payload)->assertOk()
                ->assertJsonPath('score', $correct * 1000)->assertJsonPath('rank', $rank);
            $plays[] = [$payload, $response->json()];
        }
        [$payload, $original] = $plays[1];
        $this->postJson('/typing/result', [...$payload, 'correct' => 9])->assertOk()->assertExactJson($original);
        $this->assertDatabaseCount('results', 6);
    }

    public function test_a_rank_query_failure_keeps_the_saved_score_and_does_not_retry_it(): void
    {
        $user = $this->player('owner@example.test');
        $set = $this->question(1, 2);
        $rankQueries = 0;
        DB::connection()->beforeExecuting(function ($sql, $bindings, $connection) use (&$rankQueries) {
            if (str_contains($sql, 'count(*)') && str_contains($sql, '"score" >')) {
                $rankQueries++;
                throw new QueryException($connection->getName(), $sql, $bindings, new PDOException('Rank query unavailable'));
            }
        });
        $attempt = app(TypingAttempt::class)->issue($user->id, $set->id, 'practice', 'word');
        $payload = ['attempt' => $attempt, 'correct' => 1, 'miss' => 2, 'elapsedMs' => 16000];
        $response = $this->actingAs($user)->postJson('/typing/result', $payload)->assertOk()
            ->assertJsonPath('score', -63)->assertJsonPath('rank', null);
        $this->postJson('/typing/result', $payload)->assertOk()->assertExactJson($response->json());
        $this->get('/typing/result?mode=practice&type=word&set='.$set->id)->assertOk();
        $this->assertSame(1, $rankQueries);
        $this->assertDatabaseCount('results', 1);
    }

    public function test_it_rejects_guests_invalid_measurements_and_unavailable_sets(): void
    {
        $player = $this->player('owner@example.test');
        $other = $this->player('other@example.test');
        $set = $this->question(1, 2);
        $attempt = app(TypingAttempt::class)->issue($player->id, $set->id, 'practice', 'word');
        $payload = ['attempt' => $attempt, 'correct' => 2, 'miss' => 1, 'elapsedMs' => 1300];
        $this->postJson('/typing/result', $payload)->assertUnauthorized();
        $this->actingAs($other)->postJson('/typing/result', $payload)->assertUnprocessable();
        $this->actingAs($player);
        foreach ([['correct' => -1], ['miss' => '0'], ['elapsedMs' => '1'], ['attempt' => 'invalid']] as $invalid) {
            $this->postJson('/typing/result', [...$payload, ...$invalid])->assertUnprocessable();
        }
        $this->postJson('/typing/result', [...$payload, 'attempt' => app(TypingAttempt::class)->issue($player->id, 999, 'practice', 'word')])->assertNotFound();
        $this->postJson('/typing/result', [...$payload, 'attempt' => app(TypingAttempt::class)->issue($player->id, $set->id, 'game', 'word')])->assertNotFound();
        $set->update(['release' => 0]);
        $this->postJson('/typing/result', $payload)->assertNotFound();
        $this->assertDatabaseCount('results', 0);
        $this->assertDatabaseCount('cache', 0);
    }

    public function test_it_requires_a_csrf_token(): void
    {
        $user = $this->player('owner@example.test');
        $set = $this->question(2, 2);
        $payload = ['attempt' => app(TypingAttempt::class)->issue($user->id, $set->id, 'game', 'word'),
            'correct' => 2, 'miss' => 0, 'elapsedMs' => 1500];
        // Laravel は通常テスト環境で CSRF を省略するので、このケースだけ通常時と同じ条件にする。
        $this->app->instance('env', 'local');
        $this->actingAs($user)->withSession(['_token' => 'typing-csrf-test']);
        $this->postJson('/typing/result', $payload)->assertStatus(419);
        $this->assertDatabaseCount('results', 0);
        $this->postJson('/typing/result', $payload, ['X-CSRF-TOKEN' => 'typing-csrf-test'])->assertOk();
        $this->assertDatabaseCount('results', 1);
    }

    public function test_the_game_page_only_issues_a_token_and_each_replay_gets_its_own_attempt(): void
    {
        $user = $this->player('owner@example.test');
        $set = $this->question(2, 2);
        $path = '/typing/game?mode=game&type=word&set='.$set->id;
        $this->actingAs($user);
        $tokens = [];
        for ($i = 0; $i < 2; $i++) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertSame(1, preg_match('/data-attempt="([^"]+)"/', $html, $match));
            $tokens[] = html_entity_decode($match[1]);
        }
        $this->assertNotSame(...$tokens);
        $this->get('/typing')->assertOk();
        $this->assertDatabaseCount('results', 0);
        foreach ($tokens as $attempt) {
            $this->postJson('/typing/result', ['attempt' => $attempt, 'correct' => 2, 'miss' => 0, 'elapsedMs' => 1500])->assertOk();
        }
        $this->assertDatabaseCount('results', 2);
    }

    public function test_resume_validates_owner_expiry_and_current_questions_without_writing_a_result(): void
    {
        $user = $this->player('owner@example.test');
        $other = $this->player('other@example.test');
        $set = $this->question(2, 2);
        $path = '/typing/game?mode=game&type=word&set='.$set->id;
        $resume = '/typing/game/resume';
        $this->postJson($resume)->assertUnauthorized();
        $this->actingAs($user);
        $readAttempt = function () use ($path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertSame(1, preg_match('/data-attempt="([^"]+)"/', $html, $match));

            return html_entity_decode($match[1]);
        };
        $attempt = $readAttempt();
        $fresh = $readAttempt();
        $this->postJson($resume, ['attempt' => $attempt, 'currentAttempt' => $fresh])->assertNoContent();
        $this->postJson($resume, ['attempt' => 'invalid', 'currentAttempt' => $fresh])->assertUnprocessable();
        $this->actingAs($other)->postJson($resume, ['attempt' => $attempt, 'currentAttempt' => $fresh])->assertUnprocessable();
        $this->actingAs($user);
        $set->details()->update(['reading' => 'あか']);
        $changed = $readAttempt();
        $this->postJson($resume, ['attempt' => $attempt, 'currentAttempt' => $changed])->assertUnprocessable();
        $this->travel(9)->hours();
        $this->postJson($resume, ['attempt' => $changed, 'currentAttempt' => $readAttempt()])->assertUnprocessable();
        $this->assertDatabaseCount('results', 0);
    }

    private function player(string $email): User
    {
        return User::create(['name' => 'Typing Test', 'email' => $email, 'password' => 'test-only-password', 'school_id' => 1, 'class_id' => 1]);
    }

    private function question(int $level, int $type, string $reading = 'あお'): Question
    {
        $question = Question::create(['title' => 'Typing Test', 'level' => $level, 'type' => $type, 'release' => 1]);
        $detail = $question->details()->make(['text' => 'テスト']);
        $detail->reading = $reading;
        $detail->save();

        return $question;
    }
}
