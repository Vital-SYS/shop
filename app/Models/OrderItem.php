<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'quantity',
        'cost',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
