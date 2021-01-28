<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'schedules';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'start', 'end'
    ];

    protected $hidden = [
    ];
    
    public function psikolog()
    {
        return $this->belongsToMany('App\Models\Psikolog');
    }
}
