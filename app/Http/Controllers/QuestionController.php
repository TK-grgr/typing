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
        abort(403);
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
        $question = Question::create([

        'title' => $request->title,

        'no' => $request->no,

        'type' => $request->type,

        'level' => $request->level,

        'release' => $request->release,

    ]);

    $lines = preg_split(
        "/\r\n|\n|\r/",
        trim($request->text)
    );

    foreach ($lines as $line) {

        if (trim($line) === '') {
            continue;
        }

        Detail::create([

            'question_id' => $question->id,

            'text' => trim($line),

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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,string $id)
{
    $question = Question::findOrFail($id);

    $question->update([

        'title' => $request->title,

        'no' => $request->no,

        'type' => $request->type,

        'level' => $request->level,

        'release' => $request->release,

    ]);

    Detail::where(
        'question_id',
        $question->id
    )->delete();

    $lines = preg_split(
        "/\r\n|\n|\r/",
        trim($request->text)
    );

    foreach ($lines as $line) {

        if(trim($line) === ''){
            continue;
        }

        Detail::create([

            'question_id' => $question->id,

            'text' => trim($line),

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