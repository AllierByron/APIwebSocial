<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'contenido',
        'descripcion',
        'user_id',
        'forum_id',
        'estado'
    ];


    public function comments(){
        return $this->hasMany(comment::class);
    }

    //inversa
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function forum(){
        return $this->belongsTo(forum::class);
    }
}
