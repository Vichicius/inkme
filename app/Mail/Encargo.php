<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Encargo extends Mailable
{
    use Queueable, SerializesModels;

    //nombre comentario email
    public $nombre;
    public $comentario;
    public $telefono;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nombre,$comentario,$telefono)
    {
        //
        $this->nombre = $nombre;
        $this->comentario = $comentario;
        $this->telefono = $telefono;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('encargo');
    }
}
