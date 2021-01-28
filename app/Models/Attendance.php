<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendances';
    public $timestamps = true;
    protected $appends = ['attendance_photo_url'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'notes', 'attendance_photo', 'latitude', 'longitude', 'status','created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getAttendancePhotoUrlAttribute()
    {
        if (!isset($this->attendance_photo)) {
            $this->attendance_photo = 'default.jpg';
        }
        return url('app/user/attendance/' . $this->attendance_photo);
    }
}
