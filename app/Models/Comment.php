<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'parent_id', 'article_id', 'comment', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function getParentComment()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function getChildComment()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
