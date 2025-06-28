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

    public function __construct(string $baseUrl, array $config = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($config['auth_method'] === 'token') {
            if (empty($config['token'])) {
                throw new \InvalidArgumentException('Authorization token is required when using token authentication.');
            }
            $headers['Authorization'] = "Basic {$config['token']}";
        } else {
            if (empty($config['username']) || empty($config['password'])) {
                throw new \InvalidArgumentException('Username and password are required when using basic authentication.');
            }
            $headers['Authorization'] = "Basic " . base64_encode("{$config['username']}:{$config['password']}");
        }
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers,
        ]);
    }

    /**
     * Send a single SMS message to one or multiple recipients
     *
     * @param string|array $to Recipient phone number(s) starting with 255
     * @param string $message The message to send
     * @param string|null $senderId Custom sender ID
     * @param string|null $reference Custom reference for the message
     * @return array
     * @throws \InvalidArgumentException|\Exception
     */
    public function sendSMS(string|array $to, string $message, ?string $senderId = null, ?string $reference = null): array
    {
        try {
            // Convert single recipient to array for consistent handling
            $recipients = is_array($to) ? $to : [$to];

            // Validate all phone numbers
            foreach ($recipients as $recipient) {
                if (!preg_match('/^255\d{9}$/', $recipient)) {
                    throw new \InvalidArgumentException(
                        "Invalid phone number format: {$recipient}. Must start with 255 followed by 9 digits"
                    );
                }
            }

            $payload = array_filter([
                'from' => $senderId ?? config('varasms.sender_id'),
                'to' => is_array($to) ? $to : $to, // Keep original format (string or array)
                'text' => $message,
                'reference' => $reference,
            ]);

            // Use test endpoint if test mode is enabled
            $endpoint = config('varasms.test_mode', false)
                ? '/api/sms/v1/test/text/single'
                : '/api/sms/v1/text/single';

            $response = $this->client->post($endpoint, [
                'json' => $payload
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

    /**
     * Get all delivery reports
     *
     * @return array
     * @throws \Exception
     */
    public function getDeliveryReports(): array
    {
        try {
            $response = $this->client->get('/api/sms/v1/reports');
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to get delivery reports: ' . $e->getMessage());
        }
    }

    /**
     * Get delivery reports for a specific message ID
     *
     * @param string $messageId
     * @return array
     * @throws \Exception
     */
    public function getDeliveryReport(string $messageId): array
    {
        try {
            $response = $this->client->get('/api/sms/v1/reports', [
                'query' => ['messageId' => $messageId]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to get delivery report: ' . $e->getMessage());
        }
    }

    /**
     * Get delivery reports within a specific date range
     * 
     * @deprecated This method will be removed in a future version as it's being deprecated by the API
     * @param string $sentSince Start date in Y-m-d format
     * @param string $sentUntil End date in Y-m-d format
     * @return array
     * @throws \Exception
     */
    public function getDeliveryReportsByDateRange(string $sentSince, string $sentUntil): array
    {
        try {
            if (!$this->isValidDate($sentSince) || !$this->isValidDate($sentUntil)) {
                throw new \InvalidArgumentException('Dates must be in Y-m-d format (e.g., 2020-02-01)');
            }

            $response = $this->client->get('/api/sms/v1/reports', [
                'query' => [
                    'sentSince' => $sentSince,
                    'sentUntil' => $sentUntil
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to get delivery reports by date range: ' . $e->getMessage());
        }
    }

    /**
     * Validate date format
     *
     * @param string $date
     * @return bool
     */
    private function isValidDate(string $date): bool
    {
        $format = 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Get SMS logs with optional filtering
     *
     * @param array $filters Optional filters including:
     *                      - from: string (sender ID)
     *                      - to: string (destination phone number starting with 255)
     *                      - sentSince: string (lower limit date in Y-m-d format)
     *                      - sentUntil: string (upper limit date in Y-m-d format)
     *                      - limit: int (number of records to return, max 500)
     *                      - offset: int (number of records to skip)
     *                      - reference: string (message reference used during sending)
     * @return array
     * @throws \Exception
     */
    public function getSMSLogs(array $filters = []): array
    {
        try {
            // Validate dates if provided
            if (isset($filters['sentSince']) && !$this->isValidDate($filters['sentSince'])) {
                throw new \InvalidArgumentException('sentSince must be in Y-m-d format (e.g., 2020-02-01)');
            }
            if (isset($filters['sentUntil']) && !$this->isValidDate($filters['sentUntil'])) {
                throw new \InvalidArgumentException('sentUntil must be in Y-m-d format (e.g., 2020-02-01)');
            }

            // Validate phone number format if provided
            if (isset($filters['to']) && !preg_match('/^255\d{9}$/', $filters['to'])) {
                throw new \InvalidArgumentException('Phone number must start with 255 followed by 9 digits');
            }

            // Validate limit if provided
            if (isset($filters['limit'])) {
                $filters['limit'] = (int) $filters['limit'];
                if ($filters['limit'] > 500) {
                    throw new \InvalidArgumentException('Maximum limit is 500 records');
                }
            }

            $validFilters = array_filter([
                'from' => $filters['from'] ?? null,
                'to' => $filters['to'] ?? null,
                'sentSince' => $filters['sentSince'] ?? null,
                'sentUntil' => $filters['sentUntil'] ?? null,
                'limit' => isset($filters['limit']) ? (int) $filters['limit'] : null,
                'offset' => isset($filters['offset']) ? (int) $filters['offset'] : null,
                'reference' => $filters['reference'] ?? null,
            ]);

            $response = $this->client->get('/api/sms/v1/logs', [
                'query' => $validFilters
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to get SMS logs: ' . $e->getMessage());
        }
    }

    /**
     * Register a sub-customer (for resellers only)
     *
     * @param array $customerData Customer details including:
     *                           - first_name: string
     *                           - last_name: string
     *                           - username: string
     *                           - email: string
     *                           - phone_number: string
     *                           - account_type: string ('Sub Customer' or 'Sub Customer (Reseller)')
     *                           - sms_price: numeric
     * @return array
     * @throws \InvalidArgumentException|\Exception
     */
    public function registerSubCustomer(array $customerData): array
    {
        try {
            // Required fields validation
            $requiredFields = [
                'first_name', 'last_name', 'username', 'email',
                'phone_number', 'account_type', 'sms_price'
            ];

            foreach ($requiredFields as $field) {
                if (empty($customerData[$field])) {
                    throw new \InvalidArgumentException("The {$field} field is required");
                }
            }

            // Validate email format
            if (!filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format');
            }

            // Validate phone number format (convert to international format if needed)
            $phone = $customerData['phone_number'];
            if (str_starts_with($phone, '0')) {
                $phone = '255' . substr($phone, 1);
            }
            if (!preg_match('/^255\d{9}$/', $phone)) {
                throw new \InvalidArgumentException('Phone number must be in the format 0XXXXXXXXX or 255XXXXXXXXX');
            }
            $customerData['phone_number'] = $phone;

            // Validate account type
            $validAccountTypes = ['Sub Customer', 'Sub Customer (Reseller)'];
            if (!in_array($customerData['account_type'], $validAccountTypes)) {
                throw new \InvalidArgumentException('Account type must be either "Sub Customer" or "Sub Customer (Reseller)"');
            }

            // Validate SMS price
            if (!is_numeric($customerData['sms_price']) || $customerData['sms_price'] <= 0) {
                throw new \InvalidArgumentException('SMS price must be a positive number');
            }

            $response = $this->client->post('/api/reseller/v1/sub_customer/create', [
                'json' => $customerData
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to register sub-customer: ' . $e->getMessage());
        }
    }

    /**
     * Schedule an SMS message for future delivery
     *
     * @param string $to Recipient phone number (starting with 255)
     * @param string $message The message to send
     * @param string $date Date to send (Y-m-d format)
     * @param string $time Time to send (HH:mm format, 24-hour)
     * @param string|null $senderId Custom sender ID
     * @param array $recurring Optional recurring settings:
     *                        - repeat: 'hourly'|'daily'|'weekly'|'monthly'
     *                        - start_date: Y-m-d format
     *                        - end_date: Y-m-d format
     * @return array
     * @throws \InvalidArgumentException|\Exception
     */
    public function scheduleSMS(
        string $to,
        string $message,
        string $date,
        string $time,
        ?string $senderId = null,
        array $recurring = []
    ): array {
        try {
            // Validate phone number format
            if (!preg_match('/^255\d{9}$/', $to)) {
                throw new \InvalidArgumentException('Phone number must start with 255 followed by 9 digits');
            }

            // Validate date format
            if (!$this->isValidDate($date)) {
                throw new \InvalidArgumentException('Date must be in Y-m-d format (e.g., 2024-03-24)');
            }

            // Validate time format
            if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                throw new \InvalidArgumentException('Time must be in 24-hour format HH:mm (e.g., 13:30)');
            }

            // Validate recurring parameters if provided
            if (!empty($recurring)) {
                $validRepeatValues = ['hourly', 'daily', 'weekly', 'monthly'];
                if (isset($recurring['repeat']) && !in_array($recurring['repeat'], $validRepeatValues)) {
                    throw new \InvalidArgumentException('Repeat value must be one of: ' . implode(', ', $validRepeatValues));
                }

                if (isset($recurring['start_date']) && !$this->isValidDate($recurring['start_date'])) {
                    throw new \InvalidArgumentException('Start date must be in Y-m-d format');
                }

                if (isset($recurring['end_date']) && !$this->isValidDate($recurring['end_date'])) {
                    throw new \InvalidArgumentException('End date must be in Y-m-d format');
                }

                // Validate date ranges
                if (isset($recurring['start_date'], $recurring['end_date'])) {
                    $start = strtotime($recurring['start_date']);
                    $end = strtotime($recurring['end_date']);
                    if ($start >= $end) {
                        throw new \InvalidArgumentException('End date must be after start date');
                    }
                }
            }

            $payload = array_filter([
                'from' => $senderId ?? config('varasms.sender_id'),
                'to' => $to,
                'text' => $message,
                'date' => $date,
                'time' => $time,
                'repeat' => $recurring['repeat'] ?? null,
                'start_date' => $recurring['start_date'] ?? null,
                'end_date' => $recurring['end_date'] ?? null,
            ]);

            $response = $this->client->post('/api/sms/v1/text/single', [
                'json' => $payload
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to schedule SMS: ' . $e->getMessage());
        }
    }

    /**
     * Send multiple messages to multiple destinations
     *
     * @param array $messages Array of message arrays, each containing:
     *                       - to: string|array Recipients
     *                       - text: string Message content
     *                       - from: string|null (optional) Sender ID
     * @param string|null $reference Global reference for all messages
     * @return array
     * @throws \InvalidArgumentException|\Exception
     */
    public function sendMultipleMessages(array $messages, ?string $reference = null): array
    {
        try {
            // Validate messages structure
            foreach ($messages as $message) {
                if (!isset($message['to'], $message['text'])) {
                    throw new \InvalidArgumentException(
                        'Each message must contain "to" (string|array) and "text" (string) fields'
                    );
                }

                // Convert single recipient to array
                $recipients = is_array($message['to']) ? $message['to'] : [$message['to']];

                // Validate all phone numbers
                foreach ($recipients as $recipient) {
                    if (!preg_match('/^255\d{9}$/', $recipient)) {
                        throw new \InvalidArgumentException(
                            "Invalid phone number format: {$recipient}. Must start with 255 followed by 9 digits"
                        );
                    }
                }

                // Ensure message text is not empty
                if (empty($message['text'])) {
                    throw new \InvalidArgumentException('Message text cannot be empty');
                }
            }

            // Format messages with default sender ID if not provided
            $formattedMessages = array_map(function ($message) {
                return array_filter([
                    'from' => $message['from'] ?? config('varasms.sender_id'),
                    'to' => $message['to'],
                    'text' => $message['text']
                ]);
            }, $messages);

            $payload = array_filter([
                'messages' => $formattedMessages,
                'reference' => $reference
            ]);

            // Use test endpoint if test mode is enabled
            $endpoint = config('varasms.test_mode', false)
                ? '/api/sms/v1/test/text/multi'
                : '/api/sms/v1/text/multi';

            $response = $this->client->post($endpoint, [
                'json' => $payload
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('VaraSMS API Error: ' . $e->getMessage());
            throw new \Exception('Failed to send multiple messages: ' . $e->getMessage());
        }
    }
} 