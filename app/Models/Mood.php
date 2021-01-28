<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'moods';
    public $timestamps = false;
    protected $appends = ['photo_url'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'photo'
    ];

    public function getPhotoUrlAttribute()
    {
        if (!isset($this->photo)) {
            $this->photo = 'default.jpg';
        }
        return url('app/mood/' . $this->photo);
    }
}
