<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'user_id'
    ];

    public function publications(){
        return $this->hasMany(publication::class);
    }
    public function user_forums(){
        return $this->hasMany(user_forum::class);
    }
    public function admins(){
        return $this->hasMany(admin::class);
    }

    //inversa
    public function user(){
        return $this->belongsTo(User::class);
    }
}
