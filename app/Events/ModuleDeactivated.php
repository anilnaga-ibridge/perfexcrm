<?php

namespace App\Events;

use App\Models\Module;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModuleDeactivated
{
    use Dispatchable, SerializesModels;

    public $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }
}
