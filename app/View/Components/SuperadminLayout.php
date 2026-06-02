<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SuperadminLayout extends Component
{
    public function __construct(public string $title = 'Dashboard') {}

    public function render()
    {
        return view('superadmin.layouts.app');
    }
}
