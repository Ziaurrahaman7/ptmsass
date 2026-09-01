<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Services\PlatformMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MemberInvitedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Invitation $invitation,
        public string $acceptUrl,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        PlatformMail::apply();

        $company = $this->invitation->company?->name ?? config('app.name');
        $inviter = $this->invitation->inviter;

        $envelope = new Envelope(
            subject: "{$inviter?->name} added you to {$company}",
        );

        if ($inviter?->email) {
            $envelope->replyTo([
                new Address($inviter->email, $inviter->name),
            ]);
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.member-invited',
            text: 'emails.member-invited-text',
        );
    }
}
