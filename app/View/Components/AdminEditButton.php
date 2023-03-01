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
     * Optional class override for styling purposes.
     *
     * @var string
     */
    public $class;

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param mixed  $object
     * @param string $class
     */
    public function __construct($name, $object, $class = null) {
        $this->name = $name;
        $this->object = $object;
        $this->class = $class ?? 'btn btn-secondary';
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
