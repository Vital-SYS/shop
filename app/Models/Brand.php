<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'image',
    ];

    public static function popular()
    {
        return self::withCount('products')->orderByDesc('products_count')->limit(5)->get();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getProducts()
    {
        return Product::where('brand_id', $this->id)->get();
    }
}
