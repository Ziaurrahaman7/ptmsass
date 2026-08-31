<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;

class MemberInvitedMail extends Mailable
{
    public function __construct(
        public Invitation $invitation,
        public string $acceptUrl,
    ) {}

    public function envelope(): Envelope
    {
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
