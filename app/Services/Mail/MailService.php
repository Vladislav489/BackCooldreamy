<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Mail\Mailable as MailableContract;

class MailService
{
    /** @var LoggerInterface */
    protected LoggerInterface $log;

    public function __construct()
    {
        $this->log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/mail/mail.log')
        ]);
    }

    /**
     * Отправка сообщения на email
     */
    public function send($email, MailableContract $mail): bool {
        try {
            Mail::to($email)->send($mail);
        } catch (\Exception $e) {
            $this->log->error('[EmailService::send] Ошибка при отпрвке на сообщения на почту: ' . json_encode($e->getMessage()));
            echo json_encode($e->getMessage());
        }

        return 'tes';
    }
}
