<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'quantity',
        'cost',
    ];

    /**
     * Связь «элемент принадлежит» таблицы `order_item` с таблицей `products`
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
