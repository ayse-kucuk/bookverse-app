<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'type' => ['required', 'in:thought,quote'],
            'book_id' => ['nullable', 'exists:books,id'],
        ]);

        if ($data['type'] === 'quote' && empty($data['book_id'])) {
            return back()->withErrors(['book_id' => 'Alıntı paylaşırken bir kitap seçmelisin.'])->withInput();
        }

        Post::create([
            'user_id' => $request->user()->id,
            'content' => $data['content'],
            'type' => $data['type'],
            'book_id' => $data['book_id'] ?? null,
        ]);

        return back()->with('success', 'Paylaşımın yayınlandı!');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        $post->delete();

        return back()->with('success', 'Paylaşım silindi.');
    }
}
