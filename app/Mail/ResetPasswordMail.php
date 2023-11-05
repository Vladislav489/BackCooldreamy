<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use /*Queueable, */SerializesModels; // TODO: uncomment when need a queue

    /** @var string */
    public string $code;

    /** @var string */
    public string $name;

    /**
     * @param string $url
     * @param string $name
     */
    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return VerificationMail
     */
    public function build(): ResetPasswordMail
    {
        return $this
            ->subject('Notification about password reset Cooldreamy')
            ->view('mail.reset-password')
            ->text('mail.text.reset-password')
            ->with([
                'url' => $this->code,
                'name' => $this->name,
            ]);
    }


}
