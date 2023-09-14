<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'content',
        'image',
    ];

    public static function roots()
    {
        return self::with('children')->where('parent_id', 0)->get();
    }

    public static function hierarchy()
    {
        return self::with('descendants')->where('parent_id', 0)->get();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function getProducts()
    {
        return Product::where('category_id', $this->id)->get();
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function validParent($id)
    {
        $id = (integer)$id;
        $ids = $this->getAllChildren($this->id);
        $ids[] = $this->id;
        return !in_array($id, $ids);
    }

    public function getAllChildren($id)
    {
        $children = self::with('children')->where('parent_id', $id)->get();
        $ids = [];
        foreach ($children as $child) {
            $ids[] = $child->id;
            if ($child->children->count()) {
                $ids = array_merge($ids, $child->getAllChildren($child->id));
            }
        }
        return $ids;
    }

    public function descendants()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('descendants');
    }
}
