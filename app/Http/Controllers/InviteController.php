<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\MemberInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class InviteController extends Controller
{
    public function show(string $token)
    {
        $invitation = Invitation::with('company')->where('token', $token)->first();

        return view('invite.accept', compact('invitation', 'token'));
    }

    public function store(Request $request, string $token, MemberInvitationService $invites)
    {
        $invitation = Invitation::with('company')->where('token', $token)->first();

        if (! $invitation || $invitation->accepted_at || $invitation->expires_at->isPast()) {
            return redirect()->route('invite.show', $token);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $invites->accept($invitation, $request->password);

        Auth::login($user);
        $request->session()->regenerate();

        $slug = $user->company?->slug;
        if ($user->isClient()) {
            return redirect()->route('client.dashboard', $slug);
        }

        return redirect()->route('employee.dashboard', $slug);
    }
}
