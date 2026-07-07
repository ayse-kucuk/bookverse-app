<x-app-layout>
    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">🛠️ Yeni Kitap Ekle (Yönetici Paneli)</h2>

            <form action="{{ route('admin.books.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Kitap Adı -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kitap Adı</label>
                    <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Yazar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Yazar</label>
                    <input type="text" name="author" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Kategori Seçimi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Kategori Seçin...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sayfa Sayısı -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sayfa Sayısı</label>
                    <input type="number" name="page_count" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Kapak Fotoğrafı URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kapak Fotoğrafı URL (Google tbn veya Unsplash)</label>
                    <input type="url" name="image_url" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://...">
                </div>

                <!-- Açıklama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kitap Açıklaması</label>
                    <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Gönder Butonu -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-200">
                        Kitabı Sisteme Kaydet ✨
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>