<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // 1. Tüm kitapları listeleyen fonksiyon
    public function index()
    {
        $kitaplar = Book::all();

        return view('books', compact('kitaplar'));
    } 

    // 2. Tek bir kitabın detayını getiren fonksiyon
    public function show($id)
    {
        // Veritabanında o ID'ye sahip kitabı bulur, yoksa 404 fırlatır
        $kitap = Book::findOrFail($id);

        return response()->json($kitap);
    } 
}