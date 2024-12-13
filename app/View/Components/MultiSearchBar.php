<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MultiSearchBar extends Component
{
    public $placeholder;
    public $minLength;
    public $isAdmin;
    public $models;

    public function __construct(
        string $placeholder = 'Tìm kiếm nội dung...',
        int $minLength = 2,
        bool $isAdmin = false,
        array $models = null
    ) {
        $this->placeholder = $placeholder;
        $this->minLength = $minLength;
        $this->isAdmin = $isAdmin;
        $this->models = $models;
    }

    public function render()
    {
        return view('components.multi-search-bar');
    }
}
