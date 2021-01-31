<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reimbursement_details';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'reimbursement_id', 'reimbursement_item_id', 'pengajuan',
    ];

    public function reimbursement()
    {
        return $this->belongsTo('App\Models\Reimbursement');
    }
}
