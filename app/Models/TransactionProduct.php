<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionProduct extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaction_product';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'no_transaction', 'user_id', 'product_id', 'qty', 'voucher_id', 'cost', 'diskon', 'status_bayar', 'meta', 'detail', 'created_at', 'updated_at'
    ];

    protected $hidden = [
        'updated_at', 'meta'
    ];
}
