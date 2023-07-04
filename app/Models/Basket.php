<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cookie;

class Basket extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'baskets';

    /*
     * Связь «многие ко многим» таблицы baskets с таблицей products
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    /*
     * Удаляет товар с идентификатором $id из корзины покупателя
     */
    public function removeProduct($id)
    {
        $this->products()->detach($id);
        $this->touch();
    }

    /*
     * Очищает корзину покупателя
     */
    public function clear()
    {
        $this->products()->detach();
        $this->touch();
    }

    public static function getBasket()
    {
        $basket_id = request()->cookie('basket_id');
        if (!empty($basket_id)) {
            try {
                $basket = Basket::findOrFail($basket_id);
            } catch (ModelNotFoundException $e) {
                $basket = Basket::create();
            }
        } else {
            $basket = Basket::create();
        }
        Cookie::queue('basket_id', $basket->id, 525600);
        return $basket;
    }

    public static function getCount() {
        $basket_id = request()->cookie('basket_id');
        if (empty($basket_id)) {
            return 0;
        }
        return self::getBasket()->products->count();
    }

    public function getAmount() {
        $amount = 0.0;
        foreach ($this->products as $product) {
            $amount = $amount + $product->price * $product->pivot->quantity;
        }
        return $amount;
    }
}
