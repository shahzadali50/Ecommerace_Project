<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TranslationTestController;

Route::get('/', [MainController::class, 'index'])->name('home');
Route::get('product/{slug}', [MainController::class, 'productDetail'])->name('product.detail');
// Route::get('lang/{locale}', [TranslationTestController::class, 'switchLanguage'])->name('language.switch');
Route::post('lang/switch', [TranslationTestController::class, 'switchLanguage'])->name('language.switch');
Route::post('cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::get('wishlist', [CartController::class, 'wishlist'])->name('wishlist');
Route::post('add-to-whishlist', [CartController::class, 'addToWhishlist'])->name('wishlist.add');
Route::get('cart/checkout', [CartController::class, 'cartCheckout'])->name('cart.checkout');
Route::get('cart/payment', [CartController::class, 'cartPayment'])->name('cart.payment');
Route::post('login-modal', [MainController::class, 'loginModal'])->name('loginModal');
Route::post('order/generate', [OrderController::class, 'orderGenerate'])->name('order.generate');
Route::post('billing/detail', [OrderController::class, 'billingDetail'])->name('billing.details');
Route::get('shop', [MainController::class, 'allProducts'])->name('all.products');
Route::post('/create-checkout-session', [StripeController::class, 'createCheckoutSession'])->name('stripe.checkout');
Route::get('/payment/cancel', [StripeController::class, 'paymentCancel'])->name('stripe.cancel');
Route::get('/payment/success', [StripeController::class, 'paymentSuccess'])->name('stripe.success');
Route::get('/products/search', [ProductController::class, 'productSearch'])->name('products.search');
Route::get('/track-order', [MainController::class, 'trackOrder'])->name('track.order');
Route::post('/track-order', [MainController::class, 'trackOrderPost'])->name('track.order.post');
Route::get('/order-list', [MainController::class, 'orderList'])->name('order.list');

Route::get('/translation-test', [TranslationTestController::class, 'index'])->name('translation.test');
Route::post('/translate', [TranslationTestController::class, 'translate'])->name('translate');
Route::middleware(['auth', 'admin', 'verified'])->name('admin.')->group(function () {
    Route::get('cache-clear', [MainController::class, 'cacheClear'])->name('cache.clear');
    Route::get('admin/dashboard', [MainController::class, 'dashboard'])->name('dashboard');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('category/store', [CategoryController::class, 'store'])->name('category.store');
    Route::post('import/data', [CategoryController::class, 'import'])->name('import.data');
    Route::put('category/update/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('category/delete/{id}', [CategoryController::class, 'destroy'])->name('category.delete');
    Route::get('category/log', [CategoryController::class, 'category_log'])->name('category.log');
    Route::get('brands', [BrandController::class, 'index'])->name('brands');
    Route::post('brand/store', action: [BrandController::class, 'store'])->name('brand.store');
    Route::get('brand/list/{slug}', action: [BrandController::class, 'related_brand_list'])->name('related-brand-list');
    Route::delete('brand/delete/{id}', [BrandController::class, 'destroy'])->name('brand.delete');
    Route::put('brand/update/{id}', [BrandController::class, 'update'])->name('brand.update');
    Route::get('brand/logs', [BrandController::class, 'brand_log'])->name('brand-log');
    Route::get('brand/edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
    Route::get('products', [ProductController::class, 'index'])->name('products');
    Route::post('product/store', action: [ProductController::class, 'store'])->name('product.store');
    Route::get('product/list/{slug}', action: [ProductController::class, 'related_product_list'])->name('related-product-list');
    Route::delete('product/delete/{id}', [ProductController::class, 'destroy'])->name('product.delete');
    Route::post('product/update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::get('product/logs', [ProductController::class, 'product_log'])->name('product-log');
    Route::get('order/list', [OrderController::class, 'orderList'])->name('order.list');
    Route::get('today/order/list', [OrderController::class, 'todayOrderList'])->name('today.order.list');
    Route::get('orders/last-3-days', [OrderController::class, 'lastThreeDaysOrderList'])->name('orders.last3days');
    Route::get('orders/last-7-days', [OrderController::class, 'lastSevenDaysOrderList'])->name('orders.last7days');
    Route::get('orders/last-30-days', [OrderController::class, 'lastThirtyDaysOrderList'])->name('orders.last30days');

    Route::get('orders/this-month', [OrderController::class, 'thisMonthOrderList'])->name('orders.thisMonth');



    Route::get('pending/order/list', [OrderController::class, 'pendingOrderList'])->name('pending.orders');
    Route::get('processing/order/list', [OrderController::class, 'processingOrderList'])->name('processing.orders');
    Route::get('dispatched/order/list', [OrderController::class, 'dispatchedOrderList'])->name('dispatched.orders');
    Route::get('delivered/order/list', [OrderController::class, 'deliveredOrderList'])->name('delivered.orders');
    Route::get('cancelled/order/list', [OrderController::class, 'cancelledOrderList'])->name('cancelled.orders');
    Route::get('returned/order/list', [OrderController::class, 'returnedOrderList'])->name('returned.orders');
    Route::get('refunded/order/list', [OrderController::class, 'refundedOrderList'])->name('refunded.orders');
    Route::put('order/status/update/{id}', [OrderController::class, 'updateOrderStatus'])->name('order.status.update');
    Route::get('admin/get-brands-by-category/{category}', [ProductController::class, 'getBrandsByCategory'])->name('admin.get.brands.by.category');
    Route::get('user', [MainController::class, 'getUser'])->name('login.user');
});

Route::middleware('auth')->name('user.')->group(function () {
    Route::get('user/order/list', [OrderController::class, 'userOrderList'])->name('order.list');

});


Route::get('lacuna-migrate', [MainController::class, 'migrate'])->name('migrate');
Route::get('storage-link', [MainController::class, 'storageLink'])->name('storage.link');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [MainController::class, 'checkRole'])->name('dashboard');
});
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
