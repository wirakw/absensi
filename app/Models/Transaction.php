<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'chat_room_id', 'status_bayar', 'no_transaction', 'meta', 'psikolog_id', 'topic_id', 'voucher_id', 'cost', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at', 'meta'
    ];
}
