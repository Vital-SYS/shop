<?php

namespace App\Services;

use App\Models\Basket;

class BasketService
{
    public function getBasketProducts($basketId)
    {
        return Basket::findOrFail($basketId)->products;
    }

    public function createBasket()
    {
        return Basket::create();
    }

    public function addProductToBasket($basketId, $productId, $quantity)
    {
        $basket = Basket::findOrFail($basketId);

        if ($basket->products->contains($productId)) {
            $pivotRow = $basket->products()->where('product_id', $productId)->first()->pivot;
            $quantity = $pivotRow->quantity + $quantity;
            $pivotRow->update(['quantity' => $quantity]);

        } else {
            $basket->products()->attach($productId, ['quantity' => $quantity]);
        }
    }

    public function changeProductQuantity($basketId, $productId, $count)
    {
        if ($count == 0) {
            return;
        }

        $basket = Basket::findOrFail($basketId);

        if ($basket->products->contains($productId)) {
            $pivotRow = $basket->products()->where('product_id', $productId)->first()->pivot;
            $quantity = $pivotRow->quantity + $count;

            if ($quantity > 0) {
                $pivotRow->update(['quantity' => $quantity]);
                $basket->touch();
            } else {
                $pivotRow->delete();
            }
        }
    }

    public function removeProductFromBasket($basketId, $productId)
    {
        $basket = Basket::findOrFail($basketId);

        if ($basket->products->contains($productId)) {
            $pivotRow = $basket->products()->where('product_id', $productId)->first()->pivot;
            $pivotRow->delete();
        }
    }

    public function clearBasket($basketId)
    {


        if (empty($basketId)) {
            abort(404);
        }

        $basket = Basket::find($basketId);

        if (!$basket) {
            abort(404);
        }

        $basket->clear();
    }

    public function getBasketProductsCount($basketId)
    {
        $basket = Basket::findOrFail($basketId);
        return $basket->products()->sum('quantity');
    }
}

