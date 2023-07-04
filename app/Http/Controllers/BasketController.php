<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Order;
use App\Services\BasketService;
use Illuminate\Http\Request;


class BasketController extends Controller
{
    private $basketService;

    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    /**
     * Отображение элементов
     */
    public function index(Request $request)
    {
        $basketId = $request->cookie('basket_id');

        if (!empty($basketId)) {
            $products = $this->basketService->getBasketProducts($basketId);
            return view('basket.index', compact('products'));
        } else {
            abort(404);
        }
    }

    /**
     * Добавление элементов в корзину
     */
    public function add(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');
        $quantity = $request->input('quantity') ?? 1;

        if (empty($basketId)) {
            $basket = $this->basketService->createBasket();
            $basketId = $basket->id;
        }

        $this->basketService->addProductToBasket($basketId, $productId, $quantity);

        return back()->withCookie(cookie('basket_id', $basketId));
    }

    /**
     * Прибавление кол-ва товаров в позиции
     */
    public function plus(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        $this->basketService->changeProductQuantity($basketId, $productId, 1);

        return redirect()
            ->route('basket.index')
            ->withCookie(cookie('basket_id', $basketId, 525600));
    }

    /**
     * Убавление кол-ва товаров в позиции
     */
    public function minus(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        $this->basketService->changeProductQuantity($basketId, $productId, -1);

        return redirect()
            ->route('basket.index')
            ->withCookie(cookie('basket_id', $basketId, 525600));
    }

    /**
     * Удаление элемента в корзине
     */
    public function remove(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        try {
            $this->basketService->removeProductFromBasket($basketId, $productId);
        } catch (\Exception $e) {
            abort(404);
        }

        return redirect()->route('basket.index');
    }

    /**
     * Очистка корзины
     */
    public function clear(Request $request)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        try {
            $this->basketService->clearBasket($basketId);
        } catch (\Exception $e) {
            abort(404);
        }

        return redirect()->route('basket.index');
    }

    /**
     * Сохранение заказа в БД
     */
    public function saveOrder(Request $request)
    {
        // проверяем данные формы оформления
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|max:255',
            'address' => 'required|max:255',
        ]);

        // валидация пройдена, сохраняем заказ
        $basket = Basket::getBasket();
        $user_id = auth()->check() ? auth()->user()->id : null;
        $order = Order::create(
            $request->all() + ['amount' => $basket->getAmount(), 'user_id' => $user_id]
        );

        foreach ($basket->products as $product) {
            $order->items()->create([
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->pivot->quantity,
                'cost' => $product->price * $product->pivot->quantity,
            ]);
        }

        // уничтожаем корзину
        $basket->delete();

        return redirect()
            ->route('basket.success')
            ->with('success', 'Ваш заказ успешно размещен');
    }

    /**
     * Сообщение об успешном оформлении заказа
     */
    public function success(Request $request) {
        if ($request->session()->exists('order_id')) {
            // сюда покупатель попадает сразу после успешного оформления заказа
            $order_id = $request->session()->pull('order_id');
            $order = Order::findOrFail($order_id);
            return view('basket.success', compact('order'));
        } else {
            // если покупатель попал сюда случайно, не после оформления заказа,
            // ему здесь делать нечего — отправляем на страницу корзины
            return redirect()->route('basket.index');
        }
    }
}
