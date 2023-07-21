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

    /**
     * Возвращает список корневых категорий каталога товаров
     */
    public static function roots()
    {
        return self::with('children')->where('parent_id', 0)->get();
    }

    /**
     * Возвращает список всех категорий каталога в виде дерева
     */
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

    /**
     * Связь «один ко многим» таблицы categories с таблицей categories
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Проверяет, что переданный идентификатор $id может быть родителем
     * этой категории; что категорию не пытаются поместить внутрь себя
     */
    public function validParent($id)
    {
        $id = (integer)$id;
        // получаем идентификаторы всех потомков текущей категории
        $ids = $this->getAllChildren($this->id);
        $ids[] = $this->id;
        return !in_array($id, $ids);
    }

    /**
     * Возвращает всех потомков категории с идентификатором $id
     */
    public function getAllChildren($id)
    {
        // получаем прямых потомков категории с идентификатором $id
        $children = self::with('children')->where('parent_id', $id)->get();
        $ids = [];
        foreach ($children as $child) {
            $ids[] = $child->id;
            // для каждого прямого потомка получаем его прямых потомков
            if ($child->children->count()) {
                $ids = array_merge($ids, $child->getAllChildren($child->id));
            }
        }
        return $ids;
    }

    /**
     * Связь «один ко многим» таблицы categories с таблицей categories, но
     * позволяет получить не только дочерние категории, но и дочерние-дочерние
     */
    public function descendants()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('descendants');
    }
}
