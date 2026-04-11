<?php

use Livewire\Component;
use Livewire\Attributes\{Title, Computed};
use Livewire\WithPagination;
use App\Models\Customer;

new #[Title('Pelanggan')] class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $customerId = null;

    public $name = '';
    public $phone = '';
    public $address = '';

    #[Computed]
    public function customers()
    {
        return Customer::when($this->search, function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')->orWhere('phone', 'like', '%' . $this->search . '%');
        })
            ->latest()
            ->paginate(10);
    }

    public function create()
    {
        $this->reset(['name', 'phone', 'address', 'customerId', 'editMode']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        if ($this->editMode) {
            Customer::findOrFail($this->customerId)->update($validated);
            $message = 'Pelanggan berhasil diupdate';
        } else {
            Customer::create($validated);
            $message = 'Pelanggan berhasil ditambahkan';
        }

        $this->showModal = false;
        $this->reset(['name', 'phone', 'address', 'customerId', 'editMode']);
        $this->dispatch('alert', type: 'success', message: $message);
    }

    public function delete($id)
    {
        Customer::findOrFail($id)->delete();
        $this->dispatch('alert', type: 'success', message: 'Pelanggan berhasil dihapus');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }
}; ?>

<div class="max-w-6xl mx-auto p-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Data Pelanggan</h2>
            <button wire:click="create" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Tambah Pelanggan
            </button>
        </div>

        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau telepon..."
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 mb-6">

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}">
                            <td class="px-6 py-4">{{ $customer->name }}</td>
                            <td class="px-6 py-4">{{ $customer->phone ?? '-' }}</td>
                            <td class="px-6 py-4">{{ Str::limit($customer->address, 50) ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <button wire:click="edit({{ $customer->id }})"
                                    class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                                <button wire:click="delete({{ $customer->id }})"
                                    wire:confirm="Yakin hapus pelanggan ini?"
                                    class="text-red-600 hover:text-red-800">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $this->customers->links() }}
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold mb-4">{{ $editMode ? 'Edit' : 'Tambah' }} Pelanggan</h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        @error('name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                        <input type="text" wire:model="phone"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        @error('phone')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea wire:model="address" rows="3"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500"></textarea>
                        @error('address')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" wire:click="closeModal()"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
