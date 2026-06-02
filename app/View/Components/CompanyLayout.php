<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CompanyLayout extends Component
{
    public function __construct(public string $title = 'Dashboard') {}

    public function render()
    {
        return view('company.layouts.app');
    }
}
