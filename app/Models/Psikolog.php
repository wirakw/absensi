<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Psikolog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'psikolog';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'no_himpsi', 'no_sertifikasi_psikolog', 'no_sertifikasi_lsp', 'tarif', 'is_approve',
    ];

    protected $hidden = [
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function schedules()
    {
        return $this->belongsToMany('App\Models\Schedule');
    }
}
