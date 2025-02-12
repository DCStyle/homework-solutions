<?php

namespace App\Services\Scrapers;

use App\Services\ContentMirrorService;

abstract class BaseScraper
{
    protected $defaultParams = [];
    protected $template;
    protected $path;
    protected $selector;

    protected $metadata = null;

    abstract protected function getSourceUrl(): string;
    abstract protected function processResponse($content): array;

    public function handle($params = []): ?array
    {
        $mergedParams = array_merge($this->defaultParams, $params);
        $result = app(ContentMirrorService::class)->makeRequest(
            $this->path,
            $mergedParams,
            $this->template,
            $this->selector,
            $this->getSourceUrl()
        );

        if ($result && $result['metadata']) {
            $this->metadata = $result['metadata'];
        }

        return $result ? $this->processResponse($result['content']) : null;
    }
}
