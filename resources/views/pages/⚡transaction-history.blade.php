<?php

use Livewire\Component;
use Livewire\Attributes\{Title, Computed};
use Livewire\WithPagination;
use App\Models\Transaction;

new #[Title('Riwayat Transaksi')] class extends Component {
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    #[Computed]
    public function transactions()
    {
        return Transaction::with(['user', 'customer'])
            ->when($this->search, function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')->orWhereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('transaction_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('transaction_date', '<=', $this->dateTo);
            })
            ->latest('transaction_date')
            ->paginate(15);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}; ?>

<div class="max-w-7xl mx-auto p-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Transaksi</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID atau nama pelanggan..."
                class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
            <input type="date" wire:model.live="dateFrom"
                class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
            <input type="date" wire:model.live="dateTo"
                class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->transactions as $transaction)
                        <tr class="hover:bg-gray-50" wire:key="transaction-{{ $transaction->id }}">
                            <td class="px-6 py-4 text-sm">#{{ $transaction->id }}</td>
                            <td class="px-6 py-4 text-sm">{{ $transaction->transaction_date->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm">{{ $transaction->customer?->name ?? 'Umum' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $transaction->user->name }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-blue-600">Rp
                                {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('transactions.detail', $transaction->id) }}" wire:navigate.hover
                                    class="text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada transaksi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $this->transactions->links() }}
        </div>
    </div>
</div>
