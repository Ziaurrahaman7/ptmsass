<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('users')->latest()->paginate(10);
        return view('superadmin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('superadmin.companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:companies',
            'phone'          => 'nullable|string|max:20',
            'status'         => 'required|in:active,inactive,suspended',
            'trial_ends_at'  => 'nullable|date',
            'admin_name'     => 'required|string|max:255',
            'admin_email'    => 'required|email|unique:users,email',
            'admin_password' => 'required|min:8',
        ]);

        $company = Company::create([
            'name'          => $data['name'],
            'slug'          => Str::slug($data['name']),
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?? null,
            'status'        => $data['status'],
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
        ]);

        User::create([
            'name'              => $data['admin_name'],
            'email'             => $data['admin_email'],
            'password'          => $data['admin_password'],
            'role'              => 'company_admin',
            'company_id'        => $company->id,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('superadmin.companies.index')
            ->with('success', "Company \"{$company->name}\" created successfully.");
    }

    public function edit(Company $company)
    {
        return view('superadmin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:companies,email,' . $company->id,
            'phone'         => 'nullable|string|max:20',
            'status'        => 'required|in:active,inactive,suspended',
            'trial_ends_at' => 'nullable|date',
        ]);

        $company->update($data);

        return redirect()->route('superadmin.companies.index')
            ->with('success', "Company \"{$company->name}\" updated successfully.");
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    public function toggleStatus(Company $company)
    {
        $company->update([
            'status' => $company->status === 'active' ? 'suspended' : 'active',
        ]);

        return back()->with('success', 'Company status updated.');
    }
}
