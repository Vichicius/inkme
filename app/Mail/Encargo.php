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
    public $hash_identifier;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nombre,$comentario,$telefono,$hash_identifier)
    {
        //
        $this->nombre = $nombre;
        $this->comentario = $comentario;
        $this->telefono = $telefono;
        $this->hash_identifier = $hash_identifier;
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
