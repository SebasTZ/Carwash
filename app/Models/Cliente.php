<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    public function persona(){
        return $this->belongsTo(Persona::class);
    }

    public function ventas(){
        return $this->hasMany(Venta::class);
    }

    public function fidelizacion()
    {
        return $this->hasOne(Fidelizacion::class);
    }

    protected $fillable = ['persona_id'];
}
