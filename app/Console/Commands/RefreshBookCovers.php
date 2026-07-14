<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\GoogleBooksCoverResolver;
use Illuminate\Console\Command;

class RefreshBookCovers extends Command
{
    protected $signature = 'books:refresh-covers';

    protected $description = 'Kitaplarin kapak gorsellerini Google Books uzerinden gunceller';

    public function handle(GoogleBooksCoverResolver $resolver): int
    {
        $books = Book::all();
        $updated = 0;

        foreach ($books as $book) {
            $cover = $resolver->resolve($book->title, $book->author);

            if (! $cover) {
                $this->warn("Kapak bulunamadi: {$book->title}");
                usleep(300000);
                continue;
            }

            if ($book->image_url === $cover) {
                continue;
            }

            $book->update(['image_url' => $cover]);
            $updated++;
            $this->line("Guncellendi: {$book->title}");

            usleep(300000);
        }

        $this->info("Toplam {$updated} kitap kapagi guncellendi.");

        return self::SUCCESS;
    }
}
