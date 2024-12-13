<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModalSearch extends Component
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

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.modal-search');
    }
}
