<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Transaction;

new #[Title('Detail Transaksi')] class extends Component {
    public Transaction $transaction;

    public function mount($id)
    {
        $this->transaction = Transaction::with(['user', 'customer', 'details.product'])->findOrFail($id);
    }

    public function print()
    {
        $this->dispatch('print');
    }
}; ?>

<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-xl shadow-sm p-8" id="print-area">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Invoice</h1>
                <p class="text-gray-500 mt-1">#{{ $transaction->id }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Tanggal Transaksi</p>
                <p class="font-semibold">{{ $transaction->transaction_date->format('d F Y, H:i') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8 pb-8 border-b">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Kasir</h3>
                <p class="font-medium">{{ $transaction->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $transaction->user->email }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Pelanggan</h3>
                <p class="font-medium">{{ $transaction->customer?->name ?? 'Umum' }}</p>
                @if ($transaction->customer)
                    <p class="text-sm text-gray-600">{{ $transaction->customer->phone }}</p>
                    <p class="text-sm text-gray-600">{{ $transaction->customer->address }}</p>
                @endif
            </div>
        </div>

        <div class="mb-8">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($transaction->details as $detail)
                        <tr wire:key="detail-{{ $detail->id }}">
                            <td class="px-4 py-3">{{ $detail->product->name }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">{{ $detail->qty }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Rp
                                {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right font-bold">Total</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-600 text-lg">Rp
                            {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex gap-3 print:hidden">
            <a href="{{ route('transactions.index') }}" wire:navigate.hover
                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Kembali
            </a>
            <button onclick="window.print()"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Print
            </button>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #print-area,
        #print-area * {
            visibility: visible;
        }

        #print-area {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>
