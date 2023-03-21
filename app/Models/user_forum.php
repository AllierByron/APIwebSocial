<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forum_id',
        'estado'
    ];

    //inversa
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function forum(){
        return $this->belongsTo(forum::class);
    }
}
