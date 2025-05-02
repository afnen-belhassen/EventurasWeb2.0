<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailJSService
{
    private HttpClientInterface $client;
    private const API_URL = 'https://api.emailjs.com/api/v1.0/email/send';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendRefusalEmail(string $toName, string $toEmail, string $reason): bool
    {
        return $this->sendWithTemplate('template_azxmhct', [
            'status' => 'Rejeté',
            'to_name' => $toName,
            'to_email' => $toEmail,
            'reason' => $reason,
        ]);
    }
    
    
    public function sendAcceptanceEmail(string $toName, string $toEmail): bool
    {
        return $this->sendWithTemplate('template_ichlzjl', [
            'status' => 'En cours',
            'to_name' => $toName,
            'to_email' => $toEmail,
        ]);
    }
    
    
    private function sendWithTemplate(string $templateId, array $params): bool
    {
        $payload = [
            'service_id' => 'service_i0524yq',
            'template_id' => $templateId,
            'user_id' => 'EFBTBRVBtpK6Uelob',
            'accessToken' => 'zkOZbRev8ZVtKUa2H6ZuN',
            'template_params' => $params,
        ];
    
        $response = $this->client->request('POST', self::API_URL, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
    
        return $response->getStatusCode() === 200;
    }

    
}
