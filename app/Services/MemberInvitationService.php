<?php

namespace App\Services;

use App\Mail\MemberInvitedMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberInvitationService
{
    public function invite(Company $company, User $inviter, array $data): array
    {
        $this->assertEmailAvailable($data['email'], $company);

        $invitation = Invitation::query()
            ->where('company_id', $company->id)
            ->where('email', $data['email'])
            ->pending()
            ->first();

        $token = Str::random(64);

        $payload = [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'role'       => $data['role'] ?? 'employee',
            'token'      => $token,
            'expires_at' => now()->addDays(7),
            'invited_by' => $inviter->id,
        ];

        if ($invitation) {
            $invitation->update($payload);
        } else {
            $invitation = Invitation::create($payload + ['company_id' => $company->id]);
        }

        $invitation->loadMissing('company', 'inviter');
        $mailed = $this->send($invitation);

        return [
            'invitation' => $invitation->fresh(),
            'url'        => $invitation->acceptUrl(),
            'mailed'     => $mailed,
        ];
    }

    public function resend(Invitation $invitation): array
    {
        abort_if($invitation->accepted_at, 422, 'This invite was already accepted.');

        $invitation->update([
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->loadMissing('company', 'inviter');
        $mailed = $this->send($invitation);

        return [
            'invitation' => $invitation->fresh(),
            'url'        => $invitation->acceptUrl(),
            'mailed'     => $mailed,
        ];
    }

    public function send(Invitation $invitation): bool
    {
        try {
            PlatformMail::apply();
            Mail::to($invitation->email)->send(new MemberInvitedMail($invitation, $invitation->acceptUrl()));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public function accept(Invitation $invitation, string $password): User
    {
        abort_if($invitation->accepted_at, 410, 'This invite has already been used.');
        abort_if($invitation->expires_at->isPast(), 410, 'This invite has expired.');
        abort_if(! $invitation->company || ! $invitation->company->isActive(), 403, 'This company is not active.');
        abort_if(User::where('email', $invitation->email)->exists(), 422, 'An account already exists for this email.');

        $user = User::create([
            'name'              => $invitation->name,
            'email'             => $invitation->email,
            'password'          => $password,
            'role'              => in_array($invitation->role, ['employee', 'client'], true) ? $invitation->role : 'employee',
            'company_id'        => $invitation->company_id,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $invitation->update(['accepted_at' => now()]);

        return $user;
    }

    protected function assertEmailAvailable(string $email, Company $company): void
    {
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'A member with this email already exists.',
            ]);
        }

        $elsewhere = Invitation::query()
            ->pending()
            ->where('email', $email)
            ->where('company_id', '!=', $company->id)
            ->exists();

        if ($elsewhere) {
            throw ValidationException::withMessages([
                'email' => 'This email already has a pending invite.',
            ]);
        }
    }
}
