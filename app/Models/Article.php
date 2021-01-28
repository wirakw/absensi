<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'articles';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'category_article_id', 'mood_id', 'isi', 'photo', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
