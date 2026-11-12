<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [

    'title',

    'no',

    'type',

    'level',

    'release',

];

public function details()
{
    return $this->hasMany(Detail::class);
}
}
