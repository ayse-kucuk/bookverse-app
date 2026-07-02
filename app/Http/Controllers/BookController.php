<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Comment; // Yorum modelimizi buraya dahil ettik
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Anasayfa (Kitap Listesi)
    // Anasayfa (Kitap Listesi)
    public function index()
    {
        // Kitapları kategorileri ve yorumlarıyla birlikte çekiyoruz
        $books = Book::with(['category', 'comments'])->get();

        return view('welcome', [
            'books' => $books
        ]);
    }

    // Kitap Detay Sayfası
    public function show($id)
    {
        // Kitabı kategorisi ve yorumlarıyla birlikte en yeniye göre sıralayarak çekiyoruz
        $book = Book::with(['category', 'comments' => function($query) {
            $query->latest();
        }])->findOrFail($id);

        return view('books', [
            'book' => $book
        ]);
    }

    // Yeni Yorum Kaydetme Metodu
    public function storeComment(Request $request, $id)
    {
        // Giriş yapmamış kullanıcıları güvenlik amacıyla durduruyoruz
        if (!auth()->check()) {
            return back()->with('error', 'Yorum yapabilmek için giriş yapmalısınız.');
        }

        // Gelen yorum içeriğini doğruluyoruz
        $request->validate([
            'content' => 'required|min:3|max:1000'
        ]);

        // Supabase veritabanına yorumu yazıyoruz
        Comment::create([
            'book_id' => $id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name, // Giriş yapan kullanıcının adını otomatik alıyoruz
            'content' => $request->content
        ]);

        return back()->with('success', 'Yorumunuz başarıyla eklendi! 🌸');
    }
}