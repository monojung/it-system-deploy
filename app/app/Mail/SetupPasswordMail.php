<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SetupPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $setupUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $setupUrl)
    {
        $this->user = $user;
        $this->setupUrl = $setupUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        return new Envelope(
            subject: "ยืนยันการลงทะเบียนและตั้งรหัสผ่านเข้าใช้งานระบบ - {$hospitalName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.setup_password',
            with: [
                'user' => $this->user,
                'setupUrl' => $this->setupUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
