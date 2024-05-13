<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aporte extends Model
{
    protected $table = 'aportes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'lat',
      'lng',
      'especie',
      'altura',
      'diametro_a_p',
      'diametro_copa',
      'inclinacion',
      'estado_fitosanitario',
      'etapa_desarrollo',
      'fuente_id',
      'especie_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function species()
    {
        return $this->belongsTo('App\Models\Especie', 'especie_id');
    }

    public function source()
    {
        return $this->belongsTo('App\Models\Fuente', 'fuente_id');
    }
}
