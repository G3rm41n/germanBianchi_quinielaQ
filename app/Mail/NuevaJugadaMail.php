<?php

namespace App\Mail;

use App\Models\Jugada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaJugadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Jugada $jugada)
    {
        $this->jugada->loadMissing([
            'user', 
            'detallesQuiniela', 
            'detallesQuini6', 
            'detallesLotoPlus', 
            'detallesLoto5', 
            'detallesPoceada'
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Agencia N°5801] Nueva Boleta - Ticket #{$this->jugada->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.nueva_jugada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
