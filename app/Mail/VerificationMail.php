<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use /*Queueable, */SerializesModels; // TODO: uncomment when we have a queue

    /** @var string */
    public string $token;

    /** @var bool */
    public bool $isPreview = false;

    /** @var User */
    public User $user;

    /**
     * @param string $token
     * @param User $user
     */
    public function __construct(string $token, User $user, $isPreview = false)
    {
        $this->token = $token;
        $this->user = $user;
        $this->isPreview = $isPreview;
    }

    /**
     * @return VerificationMail
     */
    public function build(): VerificationMail
    {
        return $this
            ->subject('Cooldreamy email verification')
            ->view('mail.verify')
            ->with([
                'token' => $this->token,
                'user_id' => $this->user->id,
                'name' => $this->user->name,
                'is_preview' => $this->isPreview,
            ]);
    }
}
