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
    protected $appends = ['reimbursement_photo_url'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'reimbursement_date', 'name', 'description', 'reimbursement_photo', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getReimbursementPhotoUrlAttribute()
    {
        if (!isset($this->reimbursement_photo)) {
            $this->reimbursement_photo = 'default.jpg';
        }
        return url('app/user/reimbursement/' . $this->reimbursement_photo);
    }
}
