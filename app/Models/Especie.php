<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especie extends Model
{
    protected $table = 'especies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre_cientifico'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function family()
    {
        return $this->belongsTo('App\Models\Familia', 'familia_id');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\Tipo', 'tipo_id');
    }

    public function trees()
    {
        return $this->hasMany('App\Models\Arbol');
    }
}
