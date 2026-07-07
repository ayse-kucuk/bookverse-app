<x-app-layout>
    <div class="py-12 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">✍️ Kitap Bilgilerini Düzenle</h2>

            <form action="{{ route('admin.books.update', $book->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT') <!-- Laravel'in update işlemleri için PUT direktifi şarttır -->

                <!-- Kitap Adı -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kitap Adı</label>
                    <input type="text" name="title" value="{{ $book->title }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Yazar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Yazar</label>
                    <input type="text" name="author" value="{{ $book->author }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Kategori Seçimi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $book->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sayfa Sayısı -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sayfa Sayısı</label>
                    <input type="number" name="page_count" value="{{ $book->page_count }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Kapak Fotoğrafı URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kapak Fotoğrafı URL (Doğru Linki Buraya Yapıştır)</label>
                    <input type="url" name="image_url" value="{{ $book->image_url }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Açıklama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kitap Açıklaması</label>
                    <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $book->description }}</textarea>
                </div>

                <!-- Güncelleme Butonu -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow-md transition duration-200">
                        Değişiklikleri Kaydet 🚀
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>