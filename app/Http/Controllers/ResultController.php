<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $allowedColumns = [
            'score',
            'correct',
            'miss',
            'input_time',
            'created_at',
        ];

        $sortableColumns = [
            'score' => 'スコア',
            'correct' => '入力数',
            'miss' => 'ミス数',
            'input_time' => '入力時間',
            'created_at' => '入力日',
        ];
        if(!in_array($sort, $allowedColumns)){
            $sort = 'created_at';
        }

        $results = Result::with(['user','question'])->where('user_id', Auth::id())->orderBy($sort,$direction)->get();
        return view('resultList',compact('results','sort','direction','sortableColumns'));
        
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
