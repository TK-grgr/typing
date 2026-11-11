<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Question;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()->role != 1){
        abort(403);
        }
        $users = User::with([
        'school',
        'classroom'
    ])->get();

    return view(
        'admin.index',
        compact('users')
    );
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
        if(Auth::user()->role != 1){
        abort(403);
        }
        $user = User::findOrFail($id);

    return view(
        'admin.show',
        compact('user')
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
    public function update(
    Request $request,
    string $id
)
{
    $user = User::findOrFail($id);

    $request->validate([

        'name' => 'required|max:255',

        'email' => 'required|email',

        'role' => 'required'

    ]);

    $user->update([

        'name' => $request->name,

        'email' => $request->email,

        'role' => $request->role

    ]);

    return redirect()
        ->route('admin.index')
        ->with(
            'success',
            '更新しました'
        );
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
    string $id
)
{
    $user = User::findOrFail($id);

    $user->delete();

    return redirect()
        ->route('admin.index')
        ->with(
            'success',
            '削除しました'
        );
}

    public function menu()
{
    if(Auth::user()->role != 1){
        abort(403);
        }
    $questions = Question::all();

    return view(
        'admin.menu',
        compact('questions')
    );
}
}
