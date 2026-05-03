<?php

use Illuminate\Support\Facades\Route;

// Named route for authentication redirects
Route::get('/login', function () {
    return file_get_contents(public_path('login.html'));
})->name('login');

// Serve your HTML files
Route::get('/', function () {
    return file_get_contents(public_path('index.html'));
});

Route::get('/catalogue', function () {
    return file_get_contents(public_path('catalogue.html'));
});

Route::get('/product', function () {
    return file_get_contents(public_path('product.html'));
});

Route::get('/cart', function () {
    return file_get_contents(public_path('cart.html'));
});

Route::get('/checkout', function () {
    return file_get_contents(public_path('checkout.html'));
});

Route::get('/orders', function () {
    return file_get_contents(public_path('orders.html'));
});

Route::get('/profile', function () {
    return file_get_contents(public_path('profile.html'));
});

// Catch-all route for other HTML files
Route::get('/{any}', function ($any) {
    $file = public_path($any);
    if (file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'html') {
        return file_get_contents($file);
    }
    abort(404);
})->where('any', '.*\.html$');