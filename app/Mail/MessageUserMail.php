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

class MessageUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ImageStoreTrait;

    /** @var User */
    public User $user;

    /** @var User */
    public User $sender;

    /**
     * @param User $user
     * @param User $sender
     */
    public function __construct(
        User $user,
        User $sender
    ) {
        $this->user = $user;
        $this->sender = $sender;
    }

    /**
     * @return MessageUserMail
     */
    public function build(): MessageUserMail
    {

        $message =["Waiting for your response: you have a message from {$this->sender->name}.",
                   "Important message: {$this->sender->name} is awaiting your reply.",
                   "Message received: {$this->sender->name} is expecting your reaction.",
                   "Don't miss out: {$this->sender->name} has sent you a message and is waiting for your reply.",
                   "Message from {$this->sender->name}: perhaps the start of something bigger."
            ];


        return $this
            ->from("info@cooldreamy-info.com", "New message")
            ->subject($message[rand(0,4)])
            ->view('mail.new_message')
            ->with([
                'user' => $this->user,
                'sender' => $this->sender,
            ]);
    }
}
