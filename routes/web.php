<?php

use App\Http\Controllers\BasketController;
use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::name('user.')->prefix('user')->group(function () {
    Route::get('index', [\App\Http\Controllers\UserController::class, 'index'])->name('index');
    Auth::routes();
});

Route::get('/home', 'UserController@index')->name('home');



Route::group([
    'as' => 'admin.', // имя маршрута, например admin.index
    'prefix' => 'admin', // префикс маршрута, например admin/index
    'middleware' => ['auth', 'admin'] // один или несколько посредников
], function () {
    // главная страница панели управления
    Route::get('index', \App\Http\Controllers\Admin\IndexController::class)->name('index');
    // доп.маршрут для просмотра товаров категории

    Route::get('product/category/{category}', 'ProductController@category')
        ->name('product.category');

    Route::get('/page/{page:slug}', [\App\Http\Controllers\PageController::class, '__invoke'])->name('page.show');

    Route::post('page/upload/image', [\App\Http\Controllers\Admin\PageController::class, 'uploadImage'])
        ->name('page.upload.image');
    // удаление изображения в редакторе
    Route::delete('page/remove/image', [\App\Http\Controllers\Admin\PageController::class, 'removeImage'])
        ->name('page.remove.image');
    // CRUD-операции
    Route::resources([
        'category' => \App\Http\Controllers\Admin\CategoryController::class,
        'brand' => \App\Http\Controllers\Admin\BrandController::class,
        'product' => \App\Http\Controllers\Admin\ProductController::class,
        'order' => \App\Http\Controllers\Admin\OrderController::class,
        'user' => \App\Http\Controllers\Admin\UserController::class,
        'page' => \App\Http\Controllers\Admin\PageController::class,
    ]);
});

Route::get('/', 'App\Http\Controllers\IndexController')->name('index');

Route::get('/catalog/index', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/category/{slug}', [CatalogController::class, 'category'])->name('catalog.category');
Route::get('/catalog/brand/{slug}', [CatalogController::class, 'brand'])->name('catalog.brand');
Route::get('/catalog/product/{slug}', [CatalogController::class, 'product'])->name('catalog.product');

Route::get('/basket/index', [BasketController::class, 'index'])->name('basket.index');
Route::get('/basket/checkout', [BasketController::class, 'checkout'])->name('basket.checkout');

Route::post('/basket/add/{id}', [BasketController::class, 'add'])
    ->where('id', '[0-9]+')
    ->name('basket.add');

Route::post('/basket/plus/{id}', [BasketController::class, 'plus'])
    ->where('id', '[0-9]+')
    ->name('basket.plus');

Route::post('/basket/minus/{id}', [BasketController::class, 'minus'])
    ->where('id', '[0-9]+')
    ->name('basket.minus');

Route::post('/basket/remove/{id}', [BasketController::class, 'remove'])
    ->where('id', '[0-9]+')
    ->name('basket.remove');

Route::post('/basket/clear', [BasketController::class, 'clear'])->name('basket.clear');

Route::post('/basket/save-order', [BasketController::class, 'saveOrder'])->name('basket.save-order');

Route::get('/basket/success', [BasketController::class, 'success'])->name('basket.success');

