<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AportePublicado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $datos)
    {
    }

    public function build()
    {
        $cantidad = (int) ($this->datos['cantidad'] ?? 1);
        return $this
            ->subject($cantidad === 1
                ? '🌳 ¡Se publicó tu aporte en Arbolado Urbano!'
                : '🌳 ¡Se publicaron tus aportes en Arbolado Urbano!')
            ->markdown('emails.aporte-publicado', ['datos' => $this->datos]);
    }
}
