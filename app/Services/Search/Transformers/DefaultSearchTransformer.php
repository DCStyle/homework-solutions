<?php

namespace App\Services\Search\Transformers;

use Illuminate\Support\Str;

class DefaultSearchTransformer implements SearchTransformerInterface
{
    private $titleField;
    private $subtitleField;
    private $routeName;
    private $isAdmin;

    public function __construct(
        $titleField = 'name',
        $subtitleField = 'description',
        $routeName = null,
        $isAdmin = false
    )
    {
        $this->titleField = $titleField;
        $this->subtitleField = $subtitleField;
        $this->routeName = $routeName;
        $this->isAdmin = $isAdmin;
    }

    public function transform($model, array $config = []): array
    {
        $isAdmin = $config['is_admin'] ?? $this->isAdmin;
        $routeName = $this->routeName;

        if ($routeName) {
            $routeName = $isAdmin
                ? $routeName . '.edit'
                : $routeName . '.show';
        }

        return [
            'model' => $model,
            'id' => $model->id,
            'title' => $model->{$this->titleField},
            'subtitle' => Str::limit(strip_tags($model->{$this->subtitleField} ?? ''), 100),
            'url' => $routeName ? route($routeName, $model) : '#'
        ];
    }
}
