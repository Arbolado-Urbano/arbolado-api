<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Mail\NuevoArbol as NuevoArbolCorreo;

class EnviarNuevoArbolEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $recipient,
        private array $emailData,
        private array $images,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = new NuevoArbolCorreo($this->emailData);
        $email->subject('Nuevo árbol | Arbolado Urbano');
        if (count($this->images) > 0) {
            try {
                foreach ($this->images as $image) {
                    $email->attach(Storage::path($image['path']), [
                        'as'   => $image['name'],
                        'mime' => Storage::mimeType($image['path']),
                    ]);
                }
            } catch (\Throwable $th) {
                \Log::error('Enviar Email - error adjuntando fotos:');
                \Log::error($th);
            }
        }
        try {
            Mail::to($this->recipient)->send($email);
            foreach ($this->images as $image) {
                Storage::delete($image['path']);
            }
        } catch (\Throwable $th) {
            \Log::error('Enviar Email - error al enviar:');
            \Log::error($th);
        }
    }
}
