<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{

    protected $table = 'pages';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'parent_id',
    ];

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Page::class);
    }
}
