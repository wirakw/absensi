<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'consultations';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'consultation_name',
    ];
}
