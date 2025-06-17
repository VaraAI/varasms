<?php

namespace VaraSMS\Laravel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class VaraSMSClient
{
    protected $client;
    protected $baseUrl;
    protected $auth;

    public function __construct(string $username, string $password, string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->auth = base64_encode("$username:$password");
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => "Basic {$this->auth}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a single SMS message
     *
     * @param string $to
     * @param string $message
     * @param string|null $senderId
     * @param string|null $reference
     * @return array
     * @throws \Exception
     */
    public function sendSMS(string $to, string $message, ?string $senderId = null, ?string $reference = null): array
    {
        try {
            $response = $this->client->post('/api/sms/v1/text/single', [
                'json' => array_filter([
                    'to' => $to,
                    'message' => $message,
                    'from' => $senderId ?? config('varasms.sender_id'),
                    'reference' => $reference,
                ]),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }

    /**
     * Send multiple SMS messages
     *
     * @param array $messages Array of message arrays with 'to' and 'message' keys
     * @param string|null $senderId
     * @param string|null $reference
     * @return array
     * @throws \Exception
     */
    public function sendBulkSMS(array $messages, ?string $senderId = null, ?string $reference = null): array
    {
        try {
            $response = $this->client->post('/api/sms/v1/text/multi', [
                'json' => [
                    'messages' => array_map(function ($message) use ($senderId) {
                        return array_filter([
                            'to' => $message['to'],
                            'message' => $message['message'],
                            'from' => $senderId ?? config('varasms.sender_id'),
                            'reference' => $message['reference'] ?? null,
                        ]);
                    }, $messages),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to send bulk SMS: ' . $e->getMessage());
        }
    }

    /**
     * Get SMS balance
     *
     * @return array
     * @throws \Exception
     */
    public function getBalance(): array
    {
        try {
            $response = $this->client->get('/api/sms/v1/balance');
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to get balance: ' . $e->getMessage());
        }
    }

    /**
     * Recharge a customer's SMS balance
     *
     * @param string $email
     * @param int $smsCount
     * @return array
     * @throws \Exception
     */
    public function rechargeCustomer(string $email, int $smsCount): array
    {
        try {
            $response = $this->client->post('/api/reseller/v1/sub_customer/recharge', [
                'json' => [
                    'email' => $email,
                    'smscount' => $smsCount,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to recharge customer: ' . $e->getMessage());
        }
    }

    /**
     * Deduct SMS from a customer's balance
     *
     * @param string $email
     * @param int $smsCount
     * @return array
     * @throws \Exception
     */
    public function deductCustomer(string $email, int $smsCount): array
    {
        try {
            $response = $this->client->post('/api/reseller/v1/sub_customer/deduct', [
                'json' => [
                    'email' => $email,
                    'smscount' => $smsCount,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to deduct from customer: ' . $e->getMessage());
        }
    }
} 