<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('superadmin.dashboard', [
            'totalCompanies'    => Company::count(),
            'activeCompanies'   => Company::where('status', 'active')->count(),
            'suspendedCompanies'=> Company::where('status', 'suspended')->count(),
            'totalUsers'        => User::whereNot('role', 'superadmin')->count(),
            'recentCompanies'   => Company::latest()->take(5)->get(),
        ]);
    }
}
