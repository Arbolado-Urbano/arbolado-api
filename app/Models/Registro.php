<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $table = 'registros';

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

    public function tree()
    {
        return $this->belongsTo('App\Models\Arbol', 'arbol_id');
    }

    public function source()
    {
        return $this->belongsTo('App\Models\Fuente', 'fuente_id');
    }
}
