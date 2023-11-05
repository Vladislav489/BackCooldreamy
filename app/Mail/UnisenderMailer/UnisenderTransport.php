<?php

namespace App\Mail\UnisenderMailer;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class UnisenderTransport extends AbstractTransport
{
    /**
     * Create a new Mailchimp transport instance.
     */
    public function __construct(
        protected UnisenderApi $client,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $data = [
            'sender_name' => config('mail.from.name'),
            'sender_email' => config('mail.from.address'),
            'email' => collect($email->getTo())->map(function (Address $email) {
                $address = $email->getAddress();
                $name = $email->getName();
                return "{$name} <{$address}>";
            })->first(),
            'subject' => $email->getSubject(),
            'body' => $email->getHtmlBody(),
            'list_id' => 1,
            // 'cc' => $email->getCc(),
        ];

        if ($attachments = $email->getAttachments()) {
            foreach ($attachments as $attachment) {

                $data['attachments['.$attachment->getName().']'] = $attachment->getBody();
            }
        }

       $this->client->sendEmail($data);
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'unisender';
    }
}
