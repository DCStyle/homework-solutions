<?php

namespace App\Services\Search\Transformers;

interface SearchTransformerInterface
{
    public function transform($model, array $config = []): array;
}
