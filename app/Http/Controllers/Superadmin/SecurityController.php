<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function edit()
    {
        return redirect()->route('superadmin.dashboard');
    }

    public function update(Request $request)
    {
        return redirect()->route('superadmin.dashboard');
    }
}
