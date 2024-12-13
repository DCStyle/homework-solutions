<?php

namespace App\Services\Search\Transformers;

class UserSearchTransformer implements SearchTransformerInterface
{
    public function transform($user, array $config = []): array
    {
        $isAdmin = $config['is_admin'] ?? false;
        $routeName = $isAdmin
            ? 'admin.articles.edit'
            : 'articles.show';

        return [
            'label' => 'Người dùng',
            'id' => $user->id,
            'title' => $user->name,
            'subtitle' => $user->email,
            'url' => route($routeName, $user),
            'avatar' => $user->avatar_url,
            'role' => $user->role
        ];
    }
}
