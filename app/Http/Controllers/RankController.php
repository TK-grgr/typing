<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;

class RankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->get('type',1);
        $level = $request->get('level',1);
        $tabs = [
            [
                'type' => 1,
                'level' => 1,
                'label' => '実践-長文'
            ],
            [
                'type' => 2,
                'level' => 1,
                'label' => '実践-短文'
            ],
            [
                'type' => 1,
                'level' => 2,
                'label' => 'ゲーム-長文'
            ],
            [
                'type' => 2,
                'level' => 2,
                'label' => 'ゲーム-短文'
            ],
        ];

        $results = Result::with(['user','question'])->
        whereHas('question',function ($query) use ($type,$level) {
            $query->where('type',$type)->where('level',$level);

        })->
        orderByDesc('score')->get();
        $myResult = $results
        ->where('user_id', Auth::id())
        ->first();
        $myRank = null;
    if ($myResult) {

        $myRank = $results->search(
            fn($result) => $result->id === $myResult->id) + 1;
    }
        return view('rank.index',compact('results','type','level','tabs','myResult','myRank'));
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
        //
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
}
