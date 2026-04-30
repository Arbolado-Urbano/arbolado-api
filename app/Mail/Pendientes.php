<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Pendientes extends Mailable
{
    use Queueable, SerializesModels;

    private $especiesCount;
    private $aportesCount;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($especiesCount, $aportesCount)
    {
        $this->especiesCount = $especiesCount;
        $this->aportesCount = $aportesCount;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.pendientes', ['especiesCount' => $this->especiesCount, 'aportesCount' => $this->aportesCount]);
    }
}
