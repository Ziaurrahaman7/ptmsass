<?php

namespace App\View\Components;

use Illuminate\View\Component;

class EmployeeLayout extends Component
{
    public function __construct(public string $title = 'Dashboard') {}

    public function render()
    {
        return view('employee.layouts.app');
    }
}
