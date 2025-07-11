<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DeleteModal extends Component
{
    public string $modalId;
    public string $action;
    public string $message;

    public function __construct(string $modalId = 'default', string $action = '#', string $message = 'Are you sure to delete this item?')
    {
        $this->modalId = $modalId;
        $this->action = $action;
        $this->message = $message;
    }

    public function render()
    {
        return view('components.delete-modal');
    }
}
