<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $userName;

    public function __construct(string $code, string $userName)
    {
        $this->code = $code;
        $this->userName = $userName;
    }

    public function build()
    {
        return $this->subject('Código de verificación de administrador')
            ->html("
                <div style='font-family: Arial, sans-serif; padding: 20px;'>
                    <h2>Hola, {$this->userName}</h2>
                    <p>Se detectó un inicio de sesión con rol <strong>admin</strong>.</p>
                    <p>Tu código de verificación es:</p>
                    <h1 style='letter-spacing: 6px; color: #6f42c1;'>{$this->code}</h1>
                    <p style='color: #666; font-size: 13px;'>Este código expira en 10 minutos.</p>
                </div>
            ");
    }
}