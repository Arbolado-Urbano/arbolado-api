<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Pendientes extends Mailable
{
    use Queueable, SerializesModels;

    private $especies;
    private $aportes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($especies, $aportes)
    {
        $this->especies = $especies;
        $this->aportes = $aportes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.pendientes', ['especies' => $this->especies, 'aportes' => $this->aportes]);
    }
}
