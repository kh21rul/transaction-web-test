<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'pages::⚡login')->name('login');

Route::middleware('auth')->group(function () {
    Route::livewire('/', 'pages::⚡cashier')->name('cashier');
    Route::livewire('/transactions', 'pages::⚡transaction-history')->name('transactions.index');
    Route::livewire('/transactions/{id}', 'pages::⚡transaction-detail')->name('transactions.detail');

    // Master Data
    Route::livewire('/products', 'pages::⚡products')->name('products.index');
    Route::livewire('/categories', 'pages::⚡categories')->name('categories.index');
    Route::livewire('/customers', 'pages::⚡customers')->name('customers.index');
});
