<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SearchBar extends Component
{
    public $model;
    public $routeName;
    public $placeholder;
    public $minLength;
    public $searchFields;
    public $titleField;
    public $subtitleField;
    public $limit;
    public $isAdmin;

    public function __construct(
        string $model,
        string $routeName,
        array $searchFields = ['name'],
        string $titleField = 'name',
        string $subtitleField = 'description',
        string $placeholder = 'Tìm kiếm...',
        bool $isAdmin = false,
        int $minLength = 2,
        int $limit = 10
    ) {
        $this->model = $model;
        $this->routeName = $routeName;
        $this->searchFields = $searchFields;
        $this->titleField = $titleField;
        $this->subtitleField = $subtitleField;
        $this->placeholder = $placeholder;
        $this->minLength = $minLength;
        $this->limit = $limit;
        $this->isAdmin = $isAdmin;
    }

    public function render()
    {
        return view('components.search-bar');
    }
}
