<?php

namespace App\Mail;

use App\Models\Jugada;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionResultadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Jugada $jugada,
        public string $tipo = 'resultado' // 'resultado' o 'procesada'
    ) {
        $this->jugada->loadMissing([
            'user',
            'detallesQuiniela',
            'detallesQuini6',
            'detallesLotoPlus',
            'detallesLoto5',
            'detallesPoceada',
            'sorteo',
        ]);
    }

    public function envelope(): Envelope
    {
        $asunto = match ($this->jugada->resultado) {
            'ganadora' => "🎉 ¡Felicitaciones! Tu Boleta #{$this->jugada->id} resultó Ganadora - Agencia N°5801",
            'no_ganadora' => "Resultado de tu Boleta #{$this->jugada->id} - Agencia N°5801",
            default => "Tu Boleta #{$this->jugada->id} ha sido procesada - Agencia N°5801",
        };

        return new Envelope(
            subject: $asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.resultado_jugada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
