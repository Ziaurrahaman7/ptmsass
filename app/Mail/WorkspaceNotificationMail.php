<?php

namespace App\Mail;

use App\Models\Notification;
use App\Services\PlatformMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WorkspaceNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public Notification $notification)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        PlatformMail::apply();

        return new Envelope(subject: $this->notification->title);
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.workspace-notification',
            text: 'emails.workspace-notification-text',
        );
    }
}
