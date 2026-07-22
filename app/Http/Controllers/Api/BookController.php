<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    #[OA\Get(
        path: '/books',
        summary: 'Kitap listesi',
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Başlık veya yazar ara'),
            new OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), description: 'Kategori id'),
            new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['latest', 'title', 'rating'])),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Kitap listesi'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Book::query()
            ->with('category')
            ->withCount('comments')
            ->withRatingStats();

        if ($search = trim((string) $request->input('q', ''))) {
            $query->matchingSearchTerm($search);
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        match ($request->input('sort', 'latest')) {
            'title' => $query->orderBy('title'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('ratings_count'),
            default => $query->latest(),
        };

        return response()->json($query->paginate(12));
    }

    #[OA\Get(
        path: '/books/{id}',
        summary: 'Kitap detayı',
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Kitap detayı'),
            new OA\Response(response: 404, description: 'Bulunamadı'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $book = Book::with(['category', 'comments.user'])
            ->withRatingStats()
            ->findOrFail($id);

        return response()->json(['data' => $book]);
    }
}
