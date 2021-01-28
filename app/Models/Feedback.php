<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feedbacks';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'psikolog_id', 'chat_room_id', 'rate', 'message', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
