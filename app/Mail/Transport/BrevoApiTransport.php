<?php

namespace App\Mail\Transport;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport implements TransportInterface
{
    protected TransactionalEmailsApi $api;

    public function __construct(
        protected string $apiKey
    ) {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->apiKey);
        $this->api = new TransactionalEmailsApi(new Client(), $config);
    }

    public function send(\Symfony\Component\Mime\RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope = null): ?SentMessage
    {
        $email = MessageConverter::toEmail($message);

        $sendSmtpEmail = new SendSmtpEmail([
            'sender' => [
                'name' => $email->getFrom()[0]->getName() ?: config('mail.from.name'),
                'email' => $email->getFrom()[0]->getAddress(),
            ],
            'to' => array_map(function ($address) {
                return [
                    'email' => $address->getAddress(),
                    'name' => $address->getName() ?: $address->getAddress(),
                ];
            }, $email->getTo()),
            'subject' => $email->getSubject(),
            'htmlContent' => $email->getHtmlBody(),
            'textContent' => $email->getTextBody(),
        ]);

        // Handle CC
        if ($email->getCc()) {
            $sendSmtpEmail->setCc(array_map(function ($address) {
                return [
                    'email' => $address->getAddress(),
                    'name' => $address->getName() ?: $address->getAddress(),
                ];
            }, $email->getCc()));
        }

        // Handle BCC
        if ($email->getBcc()) {
            $sendSmtpEmail->setBcc(array_map(function ($address) {
                return [
                    'email' => $address->getAddress(),
                    'name' => $address->getName() ?: $address->getAddress(),
                ];
            }, $email->getBcc()));
        }

        $this->api->sendTransacEmail($sendSmtpEmail);

        $envelope = $envelope ?? Envelope::create($email);

        return new SentMessage($message, $envelope);
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }
}
