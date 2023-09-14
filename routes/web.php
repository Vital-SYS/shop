<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
 * Главная страница интернет-магазина
 */
Route::get('/', \App\Http\Controllers\IndexController::class)->name('index');

/*
 * Страницы «Доставка», «Контакты» и прочие
 */
Route::get('/page/{page:slug}', \App\Http\Controllers\PageController::class)->name('page.show');

/*
 * Каталог товаров: категория, бренд и товар
 */
Route::group([
    'as' => 'catalog.', // имя маршрута, например catalog.index
    'prefix' => 'catalog', // префикс маршрута, например catalog/index
], function () {
    // главная страница каталога
    Route::get('/', [\App\Http\Controllers\CatalogController::class, 'index'])
        ->name('index');
    // категория каталога товаров
    Route::get('category/{category:slug}', [\App\Http\Controllers\CatalogController::class, 'category'])
        ->name('category');
    // бренд каталога товаров
    Route::get('brand/{brand:slug}', [\App\Http\Controllers\CatalogController::class, 'brand'])
        ->name('brand');
    // страница товара каталога
    Route::get('product/{product:slug}', [\App\Http\Controllers\CatalogController::class, 'product'])
        ->name('product');
    // страница результатов поиска
    Route::get('search', [\App\Http\Controllers\CatalogController::class, 'search'])
        ->name('search');
});

/*
 * Корзина покупателя
 */
Route::group([
    'as' => 'basket.', // имя маршрута, например basket.index
    'prefix' => 'basket', // префикс маршрута, например basket/index
], function () {
    // список всех товаров в корзине
    Route::get('/', [\App\Http\Controllers\BasketController::class, 'index'])
        ->name('index');
    // страница с формой оформления заказа
    Route::get('checkout', [\App\Http\Controllers\BasketController::class, 'checkout'])
        ->name('checkout');
    // получение данных профиля для оформления
    Route::post('profile', [\App\Http\Controllers\BasketController::class, 'profile'])
        ->name('profile');
    // отправка данных формы для сохранения заказа в БД
    Route::post('save-order', [\App\Http\Controllers\BasketController::class, 'saveOrder'])
        ->name('saveOrder');
    // страница после успешного сохранения заказа в БД
    Route::get('success', [\App\Http\Controllers\BasketController::class, 'success'])
        ->name('success');
    // отправка формы добавления товара в корзину
    Route::post('add/{id}', [\App\Http\Controllers\BasketController::class, 'add'])
        ->where('id', '[0-9]+')
        ->name('add');
    // отправка формы изменения кол-ва отдельного товара в корзине
    Route::post('plus/{id}', [\App\Http\Controllers\BasketController::class, 'plus'])
        ->where('id', '[0-9]+')
        ->name('plus');
    // отправка формы изменения кол-ва отдельного товара в корзине
    Route::post('minus/{id}', [\App\Http\Controllers\BasketController::class, 'minus'])
        ->where('id', '[0-9]+')
        ->name('minus');
    // отправка формы удаления отдельного товара из корзины
    Route::post('remove/{id}', [\App\Http\Controllers\BasketController::class, 'remove'])
        ->where('id', '[0-9]+')
        ->name('remove');
    // отправка формы для удаления всех товаров из корзины
    Route::post('clear', [\App\Http\Controllers\BasketController::class, 'clear'])
        ->name('clear');
});

/*
 * Регистрация, вход в ЛК, восстановление пароля
 */
Route::name('user.')->prefix('user')->group(function () {
    Auth::routes();
});

/*
 * Личный кабинет зарегистрированного пользователя
 */
Route::group([
    'as' => 'user.', // имя маршрута, например user.index
    'prefix' => 'user', // префикс маршрута, например user/index
    'middleware' => ['auth'] // один или несколько посредников
], function () {
    // главная страница личного кабинета пользователя
    Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('index');
    // CRUD-операции над профилями пользователя
    Route::resource('profile', \App\Http\Controllers\ProfileController::class);
    // просмотр списка заказов в личном кабинете
    Route::get('order', [\App\Http\Controllers\OrderController::class, 'index'])->name('order.index');
    // просмотр отдельного заказа в личном кабинете
    Route::get('order/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('order.show');
});

/*
 * Панель управления магазином для администратора сайта
 */
Route::group([
    'as' => 'admin.', // имя маршрута, например admin.index
    'prefix' => 'admin', // префикс маршрута, например admin/index
    'middleware' => ['auth', 'admin'] // один или несколько посредников
], function () {
    // главная страница панели управления
    Route::get('/', [\App\Http\Controllers\Admin\IndexController::class, '__invoke'])->name('index');
    // CRUD-операции над категориями каталога
    Route::resource('category', \App\Http\Controllers\Admin\CategoryController::class);
    // CRUD-операции над брендами каталога
    Route::resource('brand', \App\Http\Controllers\Admin\BrandController::class);
    // CRUD-операции над товарами каталога
    Route::resource('product', \App\Http\Controllers\Admin\ProductController::class);
    // доп.маршрут для показа товаров категории
    Route::get('product/category/{category}', [\App\Http\Controllers\Admin\ProductController::class, 'category'])
        ->name('product.category');
    // просмотр и редактирование заказов
    Route::resource('order', \App\Http\Controllers\Admin\OrderController::class, ['except' => [
        'create', 'store', 'destroy'
    ]]);
    // просмотр и редактирование пользователей
    Route::resource('user', \App\Http\Controllers\Admin\UserController::class, ['except' => [
        'create', 'store', 'show', 'destroy'
    ]]);
    // CRUD-операции над страницами сайта
    Route::resource('page', \App\Http\Controllers\Admin\PageController::class);
    // загрузка изображения из wysiwyg-редактора
    Route::post('page/upload/image', [\App\Http\Controllers\Admin\PageController::class, 'uploadImage'])
        ->name('page.upload.image');
    // удаление изображения в wysiwyg-редакторе
    Route::delete('page/remove/image', [\App\Http\Controllers\Admin\PageController::class, 'removeImage'])
        ->name('page.remove.image');
});
