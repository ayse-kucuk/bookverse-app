<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Comment; 
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Anasayfa (Kitap Listesi)
    public function index()
    {
        $books = Book::with(['category', 'comments'])->get();

        return view('welcome', [
            'books' => $books
        ]);
    }

    // Kütüphane Durumu Güncelleme
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:okuyorum,okundu,okuyacagim'
        ]);

        $user = auth()->user();
        $existing = $user->books()->where('books.id', $id)->first();

        if ($existing && $existing->pivot->is_protected) {
            return back()->with('success', 'Bu kitap rafı korumalı olduğu için durumu değiştirilemez. 🔒');
        }

        $user->books()->syncWithoutDetaching([
            $id => [
                'status' => $request->status,
                'is_protected' => true,
            ]
        ]);

        return back()->with('success', 'Kitap durumun güncellendi! 📚');
    }

    // Kitap Detay Sayfası
    public function show($id)
    {
        $book = Book::with(['category', 'comments' => function($query) {
            $query->latest();
        }])->findOrFail($id);

        return view('books', [
            'book' => $book
        ]);
    }

    // Yorum Kaydetme Metodu
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
        ]);

        Comment::create([
            'user_id' => auth()->id(), 
            'book_id' => $id,          
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Yorumunuz başarıyla eklendi!');
    }

    // Profil Sayfası Metodu
    public function profile()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userBooks = $user->books;

        $reading = $userBooks->where('pivot.status', 'okuyorum');
        $read = $userBooks->where('pivot.status', 'okundu');
        $willRead = $userBooks->where('pivot.status', 'okuyacagim');
        $posts = $user->posts()->with('book')->paginate(10, ['*'], 'posts_page');

        return view('profile', [
            'user' => $user,
            'reading' => $reading,
            'read' => $read,
            'willRead' => $willRead,
            'posts' => $posts,
        ]);
    }

    // 1. Form Sayfasını Gösteren Metot
    public function createBook()
    {
        $categories = Category::all();
        
        return view('admin.create-book', [
            'categories' => $categories
        ]);
    }

    // 2. Formdan Gelen Verileri Doğrulayıp Veri Tabanına Kaydeden Metot
    public function storeBook(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id', 
            'image_url' => 'required|url', 
            'description' => 'required|string|min:10',
            'page_count' => 'required|integer|min:1',
        ]);

        Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'category_id' => $request->category_id,
            'image_url' => $request->image_url,
            'description' => $request->description,
            'page_count' => $request->page_count,
            'is_protected' => true,
        ]);

        return redirect()->route('home')->with('success', 'Yeni kitap başarıyla kütüphaneye eklendi! 🚀');
    }
    // 1. Düzenleme Formunu Mevcut Bilgilerle Açan Metot
public function editBook($id)
{
    $book = Book::findOrFail($id);
    $categories = Category::all(); // Kategoriyi değiştirebilmek için listeyi çekiyoruz
    
    return view('admin.edit-book', [
        'book' => $book,
        'categories' => $categories
    ]);
}

// 2. Formdan Gelen Yeni Bilgileri Supabase'de Güncelleyen Metot
public function updateBook(Request $request, $id)
{
    $book = Book::findOrFail($id);

    // Gelen verileri tekrar güvenli şekilde doğruluyoruz
    $request->validate([
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id', 
        'image_url' => 'required|url', // Görseli düzeltebilmen için burası kritik!
        'description' => 'required|string|min:10',
        'page_count' => 'required|integer|min:1',
    ]);

    // Veri tabanında güncelleme işlemi
    $book->update([
        'title' => $request->title,
        'author' => $request->author,
        'category_id' => $request->category_id,
        'image_url' => $request->image_url,
        'description' => $request->description,
        'page_count' => $request->page_count,
    ]);

    // Düzelttikten sonra kullanıcıyı kitap detay sayfasına gönderip başarı mesajı verelim
    return redirect()->route('books.show', $id)->with('success', 'Kitap bilgileri ve kapak görseli başarıyla güncellendi! ✍️');
}
}