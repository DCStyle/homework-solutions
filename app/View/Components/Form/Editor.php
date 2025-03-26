<?php

namespace App\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Editor extends Component
{
    public $name;
    public $value;
    public $height;

    /**
     * Create a new component instance.
     */
    public function __construct($name, $value = null, $height = 900)
    {
        $this->name = $name;
        $this->value = $value;
        $this->height = $height;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.form.editor');
    }
}
