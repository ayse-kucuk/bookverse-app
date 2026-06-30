<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Kitaplığım</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans">

    <nav class="bg-white shadow-md p-4 mb-8">
        <div class="container mx-auto flex justify-between items-center">
            <!-- İsmi burada dinamik olarak çağırdık -->
            <h1 class="text-2xl font-bold text-amber-600 tracking-wide">
                {{ config('app.name') }} 
                <span class="text-gray-500 text-sm font-normal">Platformu</span>
            </h1>
            <span class="text-gray-600 font-medium">Hoş Geldin, Ayşe</span>
        </div>
    </nav>

    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6">Keşfetmeye Hazır Kitaplar</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            @foreach($kitaplar as $kitap)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition duration-300 flex flex-col justify-between">
                <div>
                    <img src="{{ $kitap->cover_image }}" alt="Kitap Kapağı" class="w-full h-48 object-cover">
                    
                    <div class="p-5">
                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Sayfa: {{ $kitap->page_count }}</span>
                        <h3 class="text-xl font-bold mt-2 text-gray-800 line-clamp-1">{{ $kitap->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1 italic">Yazar: {{ $kitap->author }}</p>
                        <p class="text-gray-600 text-sm mt-3 line-clamp-3">{{ $kitap->description }}</p>
                    </div>
                </div>
                
                <div class="p-5 pt-0">
                    <a href="/kitaplar/{{ $kitap->id }}" class="block text-center w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                        İncele & Puanla
                    </a>
                </div>
            </div>
            @endforeach

        </div>
    </div>

</body>
</html>