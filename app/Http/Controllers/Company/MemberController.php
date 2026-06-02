<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    private function company()
    {
        return auth()->user()->company;
    }

    public function index()
    {
        $members = $this->company()->users()->latest()->get();
        return view('company.members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'role'       => 'employee',
            'company_id' => $this->company()->id,
            'is_active'  => true,
        ]);

        return back()->with('success', 'Member added successfully.');
    }

    public function toggle(User $user)
    {
        abort_if($user->company_id !== $this->company()->id, 403);
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'Member status updated.');
    }
}
