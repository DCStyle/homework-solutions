<?php

namespace App\Services\Search\Transformers;

use Illuminate\Support\Str;

class BookSearchTransformer implements SearchTransformerInterface
{
    public function transform($book, array $config = []): array
    {
        $isAdmin = $config['is_admin'] ?? false;
        $routeName = $isAdmin
            ? 'admin.books.edit'
            : 'books.show';

        return [
            'label' => 'Sách',
            'id' => $book->id,
            'title' => "<b class='text-orange-400'>[{$book->group->category->name}] {$book->group->name}:</b> {$book->name}",
            'subtitle' => Str::limit(strip_tags($book->description), 100),
            'url' => route($routeName, $isAdmin ? $book : $book->slug),
            'category' => $book->group?->name,
            'created_at' => $book->created_at->format('Y-m-d')
        ];
    }
}
