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



Route::namespace('Admin')->name('admin.')->prefix('admin')->middleware('auth', 'admin')->group(function () {
    Route::get('index', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('index');
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

Route::post('/basket/saveorder', [BasketController::class, 'saveorder'])->name('basket.saveorder');

Route::get('/basket/success', [BasketController::class, 'success'])->name('basket.success');

Auth::routes();

Route::get('/home', [App\Http\Controllers\UserController::class, 'index'])->name('home');
