<?php

namespace App\Services\Search\Transformers;

use Illuminate\Support\Str;

class BookGroupSearchTransformer implements SearchTransformerInterface
{
    public function transform($bookGroup, array $config = []): array
    {
        $isAdmin = $config['is_admin'] ?? false;
        $routeName = $isAdmin
            ? 'admin.bookGroups.edit'
            : 'bookGroups.show';

        return [
            'label' => 'Môn học',
            'id' => $bookGroup->id,
            'title' => "<b class='text-orange-400'>[{$bookGroup->category->name}]</b> {$bookGroup->name}",
            'subtitle' => Str::limit(strip_tags($bookGroup->description), 100),
            'url' => route($routeName, $isAdmin ? $bookGroup : $bookGroup->slug),
            'category' => $bookGroup->category?->name,
            'created_at' => $bookGroup->created_at->format('Y-m-d')
        ];
    }
}
