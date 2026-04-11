<?php

use Livewire\Component;

new class extends Component {
    public function confirmLogout()
    {
        auth()->logout();
        return $this->redirect(route('login'), navigate: true);
    }
};
?>

<div>
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('cashier') }}" wire:navigate.hover class="text-xl font-bold text-blue-600">Kasir
                        App</a>
                    <div class="hidden md:flex gap-6">
                        <a href="{{ route('cashier') }}" wire:navigate.hover wire:current.strict="active-link"
                            class="text-gray-700 hover:text-blue-600 font-medium">
                            <span>Kasir</span>
                        </a>
                        <a href="{{ route('transactions.index') }}" wire:navigate.hover wire:current="active-link"
                            class="text-gray-700 hover:text-blue-600 font-medium">Transaksi</a>
                        <a href="{{ route('products.index') }}" wire:navigate.hover wire:current="active-link"
                            class="text-gray-700 hover:text-blue-600 font-medium">Produk</a>
                        <a href="{{ route('categories.index') }}" wire:navigate.hover wire:current="active-link"
                            class="text-gray-700 hover:text-blue-600 font-medium">Kategori</a>
                        <a href="{{ route('customers.index') }}" wire:navigate.hover wire:current="active-link"
                            class="text-gray-700 hover:text-blue-600 font-medium">Pelanggan</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <button wire:click="confirmLogout()" type="button"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>
</div>

<style>
    .active-link {
        color: #2563eb;
        font-weight: 600;
        position: relative;
    }

    .active-link::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -4px;
        width: 100%;
        height: 2px;
        background-color: #2563eb;
        border-radius: 1px;
    }
</style>
