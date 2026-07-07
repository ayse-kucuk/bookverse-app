<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(Request $request, $book_id)
    {
        // 1. Veri Doğrulama
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
        ]);

        // 2. Veri Tabanına İlişkisel Kayıt
        Comment::create([
            'user_id' => auth()->id(), // Oturum açmış kullanıcının ID'si
            'book_id' => $book_id,     // URL'den gelen kitap ID'si
            'content' => $request->content,
        ]);

        // 3. Sayfaya Geri Yönlendirme
        return redirect()->back()->with('success', 'Yorumunuz başarıyla eklendi!');
    }
}