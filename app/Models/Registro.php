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
    protected $fillable = [
        'altura',
        'diametro_a_p',
        'diametro_copa',
        'inclinacion',
        'estado_fitosanitario',
        'etapa_desarrollo',
        'notas',
        'arbol_id',
        'usuario_id',
        'fuente_id',
    ];

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
