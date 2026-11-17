<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;

class TypingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tabs = [
            ['level' => 1,'label' => '実践タイピング'],
            ['level' => 2,'label' => 'ゲームタイピング']
            ];
        $type = $request->get('type',1);
        $level = $request->get('level',1);
        $questions_long = Question::where('release',1)->where('level',$level)->where('type',1)->get();
        $questions_short = Question::where('release',1)->where('level',$level)->where('type',2)->get();
        return view('typing.index',compact('questions_long','questions_short','tabs','level'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
            $question = Question::with('details')->findOrFail($id);
        return view('typing.show',compact('question'));
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function saveResult(Request $request)
{
    $result = Result::create([
        'user_id' => Auth::id(),
        'question_id' => $request->question_id,
        'score' => $request->score,
        'correct' => $request->correct,
        'miss' => 0,
        'input_time' => $request->input_time,
    ]);

    return response()->json([
        'result_id' => $result->id
    ]);
}
}
