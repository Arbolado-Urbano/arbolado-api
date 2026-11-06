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
        $asunto = $this->datos['asunto'] ?? ($cantidad === 0
            ? '🌳 Revisamos tus aportes en Arbolado Urbano'
            : ($cantidad === 1
                ? '🌳 ¡Se publicó tu aporte en Arbolado Urbano!'
                : '🌳 ¡Se publicaron tus aportes en Arbolado Urbano!'));

        if (isset($this->datos['mensaje'])) {
            $mensaje = (string) $this->datos['mensaje'];
            // Escape first; only known, HTTPS destinations become HTML links.
            $parts = preg_split('~(https://[^\\s<>]+|(?<![\\w/])arboladourbano\\.com)~u', $mensaje, -1, PREG_SPLIT_DELIM_CAPTURE);
            $html = '';
            foreach ($parts as $part) {
                $escaped = htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $trimmed = rtrim($part, '.,;!?');
                $url = $trimmed === 'arboladourbano.com' ? 'https://arboladourbano.com' : $trimmed;
                $host = parse_url($url, PHP_URL_HOST);
                if (str_starts_with($url, 'https://') && in_array($host, ['arboladourbano.com', 'www.arboladourbano.com', 'cafecito.app'], true)) {
                    $href = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $label = htmlspecialchars($trimmed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $html .= '<a href="' . $href . '">' . $label . '</a>' . htmlspecialchars(substr($part, strlen($trimmed)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                } else {
                    $html .= $escaped;
                }
            }
            return $this->subject($asunto)->html('<!doctype html><html><body><div style="font-family:Arial,sans-serif;line-height:1.5;white-space:pre-wrap">' . $html . '</div></body></html>');
        }

        return $this->subject($asunto)->markdown('emails.aporte-publicado', ['datos' => $this->datos]);
    }
}
