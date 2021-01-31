<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reimbursements';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'reimbursement_date', 'reimbursement_type_id', 'description', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function detail()
    {
        return $this->hasMany('App\Models\ReimbursementDetail');
    }
    
    public function photos()
    {
        return $this->hasMany('App\Models\ReimbursementPhoto');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\ReimbursementType');
    }
}
