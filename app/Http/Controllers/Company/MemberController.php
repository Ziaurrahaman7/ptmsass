<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Services\MemberInvitationService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(private MemberInvitationService $invites) {}

    private function company()
    {
        return auth()->user()->company;
    }

    public function index(string $slug)
    {
        $members = $this->company()->users()->latest()->get();
        $invitations = $this->company()->invitations()->pending()->latest()->get();

        return view('company.members.index', compact('members', 'invitations'));
    }

    public function store(Request $request, string $slug)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role'  => 'nullable|in:employee,client',
        ]);

        $result = $this->invites->invite($this->company(), auth()->user(), $data);

        $message = $result['mailed']
            ? 'Invite sent to '.$data['email'].'.'
            : 'Invite created, but email could not be sent. Copy the link below.';

        return back()
            ->with('success', $message)
            ->with('invite_url', $result['url']);
    }

    public function resend(string $slug, Invitation $invitation)
    {
        abort_if($invitation->company_id !== $this->company()->id, 403);

        $result = $this->invites->resend($invitation);

        $message = $result['mailed']
            ? 'Invite resent.'
            : 'New invite link created, but email could not be sent. Copy the link below.';

        return back()
            ->with('success', $message)
            ->with('invite_url', $result['url']);
    }

    public function revoke(string $slug, Invitation $invitation)
    {
        abort_if($invitation->company_id !== $this->company()->id, 403);
        abort_if($invitation->accepted_at, 422);

        $invitation->delete();

        return back()->with('success', 'Invite revoked.');
    }

    public function toggle(string $slug, User $user)
    {
        abort_if($user->company_id !== $this->company()->id, 403);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Member status updated.');
    }
}
