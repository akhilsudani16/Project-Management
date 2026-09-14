<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public ?Organization $organization,
        public ?Project $project,
        public string $token,
        public string $expiresAt,
        public User $invitedBy
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->organization
            ? 'You have been invited to '.$this->organization->name
            : 'You have been invited';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $acceptUrl = config('app.frontend_url').'/invite/accept?token='.$this->token.'&email='.urlencode($this->user->email);

        return new Content(
            markdown: 'emails.invitation',
            with: [
                'user' => $this->user,
                'organization' => $this->organization,
                'project' => $this->project,
                'invitedBy' => $this->invitedBy,
                'acceptUrl' => $acceptUrl,
                'expiresAt' => $this->expiresAt,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
