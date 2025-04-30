<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $sid;
    private $token;
    private $from;

    public function __construct(string $sid, string $token, string $from)
    {
        $this->sid = $sid;
        $this->token = $token;
        $this->from = $from; // Your Twilio phone number
    }

    public function sendSms(string $to, string $message): void
    {
        $twilio = new Client($this->sid, $this->token);
        
        // Send the SMS
        $twilio->messages->create(
            $to, // Recipient phone number
            [
                'from' => $this->from, // Twilio phone number
                'body' => $message
            ]
        );
    }
}
