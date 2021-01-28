<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoomMember extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_room_members';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'chat_room_id', 'user_id', 'joined_at', 'left_at', 'is_owner', 'is_moderator', 'meta', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
