<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'meetings';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'start_date_time', 'from', 'to', 'link', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
