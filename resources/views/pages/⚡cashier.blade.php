<?php

use Livewire\Component;
use Livewire\Attributes\{Title, Computed};
use App\Models\{Product, Customer, Transaction, TransactionDetail};
use Illuminate\Support\Facades\DB;

new #[Title('Kasir')] class extends Component {
    public $search = '';
    public $customer_id = null;
    public $cart = [];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
    }

    #[Computed]
    public function products()
    {
        return Product::with('category')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('id', 'like', '%' . $this->search . '%')
            ->orWhereHas('category', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function customers()
    {
        return Customer::orderBy('name')->get();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product || $product->stock <= 0) {
            $this->dispatch('alert', type: 'error', message: 'Stok tidak tersedia');
            return;
        }

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['qty'] >= $product->stock) {
                $this->dispatch('alert', type: 'error', message: 'Stok tidak mencukupi');
                return;
            }
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1,
                'stock' => $product->stock,
            ];
        }

        session()->put('cart', $this->cart);
        $this->dispatch('alert', type: 'success', message: 'Produk ditambahkan');
    }

    public function updateQty($productId, $qty)
    {
        if ($qty <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        if ($qty > $this->cart[$productId]['stock']) {
            $this->dispatch('alert', type: 'error', message: 'Stok tidak mencukupi');
            return;
        }

        $this->cart[$productId]['qty'] = $qty;
        session()->put('cart', $this->cart);
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        session()->put('cart', $this->cart);
    }

    public function getTotal()
    {
        return collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['qty'];
        });
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', type: 'error', message: 'Keranjang kosong');
            return;
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'customer_id' => $this->customer_id,
                'total_price' => $this->getTotal(),
            ]);

            foreach ($this->cart as $productId => $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $productId,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                $product = Product::find($productId);
                $product->decrement('stock', $item['qty']);
            }

            DB::commit();

            session()->forget('cart');
            $this->cart = [];
            $this->customer_id = null;

            $this->dispatch('alert', type: 'success', message: 'Transaksi berhasil');

            $this->js(
                "
                setTimeout(() => {
                    Livewire.navigate('" .
                    route('transactions.detail', $transaction->id) .
                    "');
                }, 1500);
            ",
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', type: 'error', message: 'Transaksi gagal: ' . $e->getMessage());
        }
    }
}; ?>

<div class="max-w-7xl mx-auto p-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Products Section -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Produk</h2>

                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..."
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-4">

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse($this->products as $product)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer {{ $product->stock <= 0 ? 'opacity-50' : '' }}"
                            wire:click="addToCart({{ $product->id }})" wire:key="product-{{ $product->id }}">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-gray-800">{{ $product->name }}</h3>
                                <span
                                    class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $product->category->name }}</span>
                            </div>
                            <p class="text-lg font-bold text-blue-600">Rp
                                {{ number_format($product->price, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-500">Stok: {{ $product->stock }}</p>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 text-gray-500">
                            Produk tidak ditemukan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-6 sticky top-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Keranjang</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pelanggan (Opsional)</label>
                    <select wire:model="customer_id"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">Umum</option>
                        @foreach ($this->customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                    @forelse($cart as $productId => $item)
                        <div class="flex justify-between items-center border-b pb-3"
                            wire:key="cart-{{ $productId }}">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-800">{{ $item['name'] }}</h4>
                                <p class="text-sm text-gray-500">Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="updateQty({{ $productId }}, {{ $item['qty'] - 1 }})"
                                    class="w-8 h-8 bg-gray-200 rounded hover:bg-gray-300">-</button>
                                <span class="w-8 text-center">{{ $item['qty'] }}</span>
                                <button wire:click="updateQty({{ $productId }}, {{ $item['qty'] + 1 }})"
                                    class="w-8 h-8 bg-gray-200 rounded hover:bg-gray-300">+</button>
                                <button wire:click="removeFromCart({{ $productId }})"
                                    class="ml-2 text-red-500 hover:text-red-700">×</button>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">Keranjang kosong</p>
                    @endforelse
                </div>

                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span class="text-blue-600">Rp {{ number_format($this->getTotal(), 0, ',', '.') }}</span>
                    </div>
                </div>

                <button wire:click="checkout" @disabled(empty($cart))
                    class="w-full mt-4 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Bayar
                </button>
            </div>
        </div>
    </div>
</div>
