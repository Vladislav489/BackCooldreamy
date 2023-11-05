<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class UserVerifiedMail extends Mailable
{
    use /*Queueable, */SerializesModels; // TODO: uncomment when we have a queue

    /** @var User */
    public User $user;

    /** @var bool */
    public bool $isPreview;

    /**
     * @param User $user
     * @param bool $isPreview
     */
    public function __construct(
        User $user,
        bool $isPreview = false,
    ) {
        $this->user = $user;
        $this->isPreview = $isPreview;
    }

    /**
     * @return VerificationMail
     */
    public function build(): UserVerifiedMail
    {
        return $this
            ->subject('Your account has been verified ')
            ->view('mail.verified')
            ->with([
                'user' => $this->user,
                'is_preview' => $this->isPreview,
            ]);
    }

    /**
     * Get the attachable representation of the model.
     */
    public function attachments(): Attachment|array
    {
        // Attach the logo
        return [
            Attachment::fromPath(storage_path('app/media/logo.png')),
            Attachment::fromPath(storage_path('app/media/success.png')),
        ];
    }
}
