<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Detail;
use Illuminate\Support\Facades\Auth;


class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()->role != 1){
        return redirect('top');
        }
    $questions = Question::all();

    return view(
        'questions.index',
        compact('questions')
    );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('questions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $isAdmin = Auth::check() && Auth::user()->role == 1;
    $release = $isAdmin ? $request->release : 0;

    $question = Question::create([
        'title' => $request->title,
        'type' => $request->type,
        'level' => $request->level,
        'release' => $release,
    ]);

    $textLines = preg_split("/\r\n|\n|\r/", trim($request->text ?? ''));
    $readingLines = preg_split("/\r\n|\n|\r/", trim($request->reading ?? ''));

    foreach ($textLines as $i => $line) {
        $text = trim($line);
        if ($text === '') {
            continue;
        }

        $reading = ($request->level == 2 && isset($readingLines[$i]) && trim($readingLines[$i]) !== '')
            ? trim($readingLines[$i])
            : null;

        Detail::create([
            'question_id' => $question->id,
            'text' => $text,
            'reading' => $reading !== '' ? $reading : null,
        ]);
    }

    return redirect()
        ->route('questions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $question = Question::with('details')
        ->findOrFail($id);

    $text = $question->details
        ->pluck('text')
        ->implode("\n");

    return view(
        'questions.show',
        compact(
            'question',
            'text'
        )
    );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
{
    if(Auth::user()->role != 1){
        return redirect('top');
        }

    $question = Question::with('details')->findOrFail($id);

    $text = $question->details
        ->pluck('text')
        ->implode("\n");

    $reading = $question->details
        ->pluck('reading')
        ->implode("\n");

    return view(
        'questions.edit',
        compact(
            'question',
            'text',
            'reading'
        )
    );
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    if (!Auth::check() || Auth::user()->role != 1) {
        abort(403);
    }

    $question = Question::findOrFail($id);

    $question->update([
        'title' => $request->title,
        'no' => $request->no,
        'type' => $request->type,
        'level' => $request->level,
        'release' => $request->release,
    ]);

    Detail::where('question_id', $question->id)->delete();

    $textLines = preg_split("/\r\n|\n|\r/", trim($request->text ?? ''));
    $readingLines = preg_split("/\r\n|\n|\r/", trim($request->reading ?? ''));

    foreach ($textLines as $i => $line) {
        $text = trim($line);
        if ($text === '') {
            continue;
        }

        $reading = ($request->level == 2 && isset($readingLines[$i]) && trim($readingLines[$i]) !== '')
            ? trim($readingLines[$i])
            : null;

        Detail::create([
            'question_id' => $question->id,
            'text' => $text,
            'reading' => $reading !== '' ? $reading : null,
        ]);
    }

    return redirect()
        ->route('questions.index');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $question = Question::findOrFail($id);

    $question->details()->delete();

    $question->delete();

    return redirect()
        ->route('questions.index');
}
}