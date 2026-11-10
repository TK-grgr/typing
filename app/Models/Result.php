<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    public function user()
{
    return $this->belongsTo(User::class,'user_id');
}
    public function question()
    {
        return $this->belongsTo(Question::class,'question_id');
    }
}
