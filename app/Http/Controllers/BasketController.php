<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\BasketService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BasketController extends Controller
{
    protected BasketService $basketService;
    protected Basket $basket;

    public function __construct(BasketService $basketService, Basket $basket)
    {
        $this->basketService = $basketService;
        $this->basket = $basket;
    }

    public function index(Request $request)
    {
        $basketId = (string)$request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        $this->basketService->getBasketProducts($basketId);

        $basket = Basket::find($basketId);

        if (is_null($basket)) {
            abort(404);
        }

        $amount = $basket->getAmount();
        $products = $basket->products;

        return view('basket.index', compact('products', 'amount'));
    }


    public function add(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');
        $quantity = $request->input('quantity') ?? 1;

        if (empty($basketId)) {
            $basket = $this->basketService->createBasket();
            $basketId = $basket->id;
        }

        $this->basketService->addProductToBasket($basketId, $productId, $quantity);

        $basket = Basket::find($basketId);

        if (is_null($basket)) {
            abort(404);
        }

        $positionsCount = $basket->getCount();

        return response()->json(['success' => true, 'positionsCount' => $positionsCount]);
    }


    public function plus(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        $this->basketService->changeProductQuantity($basketId, $productId, 1);

        return back();
    }


    public function minus(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        $this->basketService->changeProductQuantity($basketId, $productId, -1);

        return back();
    }


    public function remove(Request $request, $productId)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        try {
            $this->basketService->removeProductFromBasket($basketId, $productId);
        } catch (Exception $e) {
            abort(404);
        }

        return back();
    }

    public function clear(Request $request)
    {
        $basketId = $request->cookie('basket_id');

        if (empty($basketId)) {
            abort(404);
        }

        try {
            $this->basketService->clearBasket($basketId);
        } catch (Exception $e) {
            abort(404);
        }

        return back();
    }


    public function saveOrder(Request $request)
    {
        // Проверяем данные формы оформления
        try {
            $this->validate($request, [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|max:255',
                'address' => 'required|max:255',
            ]);
        } catch (ValidationException $e) {
        }

        // Валидация пройдена, сохраняем заказ
        $basket = Basket::getBasket();
        $user_id = auth()->check() ? auth()->user()->id : null;
        $order = new Order();
        $order->fill($request->all() + [
                'amount' => $basket->getAmount(),
                'user_id' => $user_id,
            ]);

        $order->created_at = Carbon::now();

        $order->save();

        foreach ($basket->products as $product) {
            $orderItem = new OrderItem();
            $orderItem->product_id = $product->id;
            $orderItem->name = $product->name;
            $orderItem->price = $product->price;
            $orderItem->quantity = $product->pivot->quantity;
            $orderItem->cost = $product->price * $product->pivot->quantity;
            $order->items()->save($orderItem);
        }

        // Уничтожаем корзину
        $basket->delete();

        return redirect()
            ->route('basket.success')
            ->with('order_id', $order->id);
    }

    public function success(Request $request)
    {
        if ($request->session()->exists('order_id')) {
            $order_id = $request->session()->pull('order_id');
            $order = Order::findOrFail($order_id);
            return view('basket.success', compact('order'));
        } else {
            return redirect()->route('basket.index');
        }
    }


    public function checkout(Request $request)
    {
        $profile = null;
        $profiles = null;
        if (auth()->check()) {
            $user = auth()->user();

            if (!empty($user->profiles)) {
                $profiles = $user->profiles;
            }
            $prof_id = (int)$request->input('profile_id');
            if ($prof_id) {
                $profile = $user->profiles()->whereIdAndUserId($prof_id, $user->id)->first();
            }
        }
        return view('basket.checkout', compact('profiles', 'profile'));
    }

    public function profile(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        if (!auth()->check()) {
            return response()->json(['error' => 'Нужна авторизация!'], 404);
        }
        $user = auth()->user();
        $profile_id = (int)$request->input('profile_id');
        if ($profile_id) {
            $profile = $user->profiles()->whereIdAndUserId($profile_id, $user->id)->first();
            if ($profile) {
                return response()->json(['profile' => $profile]);
            }
        }
        return response()->json(['error' => 'Профиль не найден!'], 404);
    }
}
