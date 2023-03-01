<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class AdminEditButton extends Component {
    /**
     * The model object.
     *
     * @var mixed
     */
    public $object;

    /**
     * The name of the object. Used for labeling.
     *
     * @var string
     */
    public $name;

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param mixed  $object
     */
    public function __construct($name, $object) {
        $this->name = $name;
        $this->object = $object;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Closure|\Illuminate\Contracts\View\View|null
     */
    public function render() {
        if (Auth::check()) {
            return view('components.admin-edit-button');
        }
    }
}
