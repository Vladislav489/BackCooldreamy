<?php

namespace App\Mail;

use App\Models\User;
use App\Traits\ImageStoreTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Facades\Image;

class LikeUserMail extends Mailable
{
    use /*Queueable, */SerializesModels, ImageStoreTrait; // TODO: uncomment when we have a queue

    /** @var int */
    public User $user;

    /** @var int */
    public User $sender;

    /**
     * @param User $user
     * @param User $sender
     */
    public function __construct(User $user, User $sender)
    {
        $this->user = $user;
        $this->sender = $sender;
    }

    /**
     * @return LikeUserMail
     */
    public function build(): LikeUserMail
    {
        return $this
            ->subject('Somebody liked you')
            ->view('mail.like_user')
            ->with([
                'user' => $this->user,
                'sender' => $this->sender,
            ]);
    }
}
