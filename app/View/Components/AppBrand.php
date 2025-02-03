<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppBrand extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <a href="/" wire:navigate>
                    <!-- Hidden when collapsed -->
                    <div {{ $attributes->class(["hidden-when-collapsed"]) }}>
                        <div class="flex items-center gap-3 pt-2 ">
                             <img
                             src="{{ asset('images/GALICIA_LOGO.png') }}"
                             alt="Galicia"
                             class="inline-block h-auto max-w-[200px] mt-3"
                             >
                        </div>
                    </div>

                    <!-- Display when collapsed -->
                    <div class="display-when-collapsed hidden mx-5 mt-4 lg:mb-6 h-[28px]">
                        <img
                        src="{{ asset('images/GALICIA_ICON.png') }}"
                        class="inline-block h-auto max-w-[50px] mt-3"
                        >
                    </div>
                </a>
            HTML;
    }
}
