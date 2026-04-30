<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function records()
    {
        return $this->hasMany('App\Models\Registro', 'usuario_id');
    }

    public function source()
    {
        return $this->belongsTo('App\Models\Fuente', 'fuente_id');
    }
}
