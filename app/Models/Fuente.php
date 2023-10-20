<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fuente extends Model
{
    protected $table = 'fuentes';

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
    protected $hidden = ['email'];

    public function records()
    {
        return $this->hasMany('App\Models\Registro', 'fuente_id');
    }
}
