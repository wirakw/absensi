<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reimbursement_items';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'reimbursement_type_id', 'item_name',
    ];
}
