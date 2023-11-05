<?php

namespace App\Events;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class ResetPassword extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public string $token;

    /**
     * Create a notification instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     */
    public function via(User $notifiable)
    {
       Mail::to($notifiable)->send(new ResetPasswordMail($this->resetUrl($notifiable), $notifiable->name));
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl(mixed $notifiable): string
    {

        return url(route('app.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

}
