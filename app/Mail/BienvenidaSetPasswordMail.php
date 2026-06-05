<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class BienvenidaSetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $setPasswordUrl;

    public function __construct(public User $user)
    {
        // URL firmada válida por 7 días — no requiere tabla extra
        $this->setPasswordUrl = URL::temporarySignedRoute(
            'first-login.show',
            now()->addDays(7),
            ['user' => $user->id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido/a — Configura tu contraseña',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida',
        );
    }
}
