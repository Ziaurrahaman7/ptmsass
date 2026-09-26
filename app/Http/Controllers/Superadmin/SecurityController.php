<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function edit()
    {
        $companies = Company::query()->orderBy('name')->get();
        $logs = SecurityAuditLog::query()->latest()->limit(40)->get();

        return view('superadmin.security.edit', compact('companies', 'logs'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'mfa_required' => 'nullable|boolean',
        ]);
        $company = Company::findOrFail($data['company_id']);
        $company->update(['mfa_required' => (bool) ($data['mfa_required'] ?? false)]);
        SecurityAuditLog::create([
            'company_id' => $company->id,
            'user_id' => auth()->id(),
            'action' => 'superadmin.security.updated',
            'ip' => $request->ip(),
            'meta' => $data,
        ]);

        return back()->with('success', 'Company security flag updated.');
    }
}
