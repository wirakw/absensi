<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnose extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'diagnoses';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'psikolog_id', 'chat_room_id', 'no_konsultasi', 'diagnosa_1', 'diagnosa_2', 'diagnosa_3', 'diagnosa_4','created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
