<?php

namespace App\Services;

use Brevo\Client\Api\TransactionalSMSApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendTransacSms;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BrevoSmsService
{
    protected TransactionalSMSApi $api;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', config('services.brevo.api_key'));

        $this->api = new TransactionalSMSApi(new Client(), $config);
    }

    /**
     * Invia un SMS transazionale tramite Brevo.
     *
     * @param string $recipient Numero di telefono destinatario (formato internazionale, es: +39XXXXXXXXXX)
     * @param string $content Contenuto del messaggio SMS
     * @param string|null $sender Nome mittente (max 11 caratteri alfanumerici)
     * @return array{success: bool, message_id: string|null, error: string|null}
     */
    public function send(string $recipient, string $content, ?string $sender = null): array
    {
        try {
            // Normalizza il numero di telefono
            $recipient = $this->normalizePhoneNumber($recipient);

            $sms = new SendTransacSms([
                'sender' => $sender ?? config('services.brevo.sms_sender', 'Simecom'),
                'recipient' => $recipient,
                'content' => $content,
            ]);

            $result = $this->api->sendTransacSms($sms);

            Log::info('SMS inviato con successo', [
                'recipient' => $recipient,
                'message_id' => $result->getMessageId(),
            ]);

            return [
                'success' => true,
                'message_id' => $result->getMessageId(),
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Errore invio SMS', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Normalizza il numero di telefono in formato internazionale.
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Rimuovi spazi, trattini, punti
        $phone = preg_replace('/[\s\-\.]/', '', $phone);

        // Se inizia con 00, sostituisci con +
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        // Se non inizia con +, aggiungi prefisso Italia
        if (!str_starts_with($phone, '+')) {
            // Rimuovi eventuale 0 iniziale per numeri italiani
            if (str_starts_with($phone, '0')) {
                $phone = substr($phone, 1);
            }
            $phone = '+39' . $phone;
        }

        return $phone;
    }
}
