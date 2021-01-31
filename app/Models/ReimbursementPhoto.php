<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReimbursementPhoto extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reimbursement_photos';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'reimbursement_id', 'reimbursement_photo',
    ];

    public function reimbursement()
    {
        return $this->belongsTo('App\Models\Reimbursement');
    }
}
