<?php

use Livewire\Component;
use Livewire\Attributes\{Title, Computed};
use Livewire\WithPagination;
use App\Models\Category;

new #[Title('Kategori')] class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $categoryId = null;
    public $name = '';

    #[Computed]
    public function categories()
    {
        return Category::withCount('products')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);
    }

    public function create()
    {
        $this->reset(['name', 'categoryId', 'editMode']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($this->editMode) {
            Category::findOrFail($this->categoryId)->update($validated);
            $message = 'Kategori berhasil diupdate';
        } else {
            Category::create($validated);
            $message = 'Kategori berhasil ditambahkan';
        }

        $this->showModal = false;
        $this->reset(['name', 'categoryId', 'editMode']);
        $this->dispatch('alert', type: 'success', message: $message);
    }

    public function delete($id)
    {
        try {
            Category::findOrFail($id)->delete();
            $this->dispatch('alert', type: 'success', message: 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Kategori tidak bisa dihapus karena masih memiliki produk');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }
}; ?>

<div class="max-w-5xl mx-auto p-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Data Kategori</h2>
            <button wire:click="create" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Tambah Kategori
            </button>
        </div>

        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kategori..."
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 mb-6">

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="px-6 py-4">{{ $category->name }}</td>
                            <td class="px-6 py-4">{{ $category->products_count }} produk</td>
                            <td class="px-6 py-4">
                                <button wire:click="edit({{ $category->id }})"
                                    class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                                <button wire:click="delete({{ $category->id }})"
                                    wire:confirm="Yakin hapus kategori ini?"
                                    class="text-red-600 hover:text-red-800">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $this->categories->links() }}
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold mb-4">{{ $editMode ? 'Edit' : 'Tambah' }} Kategori</h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        @error('name')
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
