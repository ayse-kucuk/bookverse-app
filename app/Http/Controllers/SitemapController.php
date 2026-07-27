<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            [
                'loc' => route('home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('explore'),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => route('search'),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ],
        ];

        $books = Book::query()
            ->whereNotNull('slug')
            ->orderByDesc('updated_at')
            ->get(['id', 'slug', 'updated_at']);

        foreach ($books as $book) {
            $urls[] = [
                'loc' => route('books.show', $book),
                'lastmod' => $book->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $users = User::query()
            ->where('account_visibility', User::VISIBILITY_PUBLIC)
            ->orderByDesc('updated_at')
            ->get(['id', 'updated_at']);

        foreach ($users as $user) {
            $urls[] = [
                'loc' => route('users.show', $user),
                'lastmod' => $user->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
