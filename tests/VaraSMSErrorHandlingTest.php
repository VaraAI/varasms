<?php

namespace VaraSMS\Laravel\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use VaraSMS\Laravel\VaraSMSClient;

class VaraSMSErrorHandlingTest extends TestCase
{
    protected $mockHandler;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        $this->client = new VaraSMSClient(
            'test_user',
            'test_password',
            'https://messaging-service.co.tz'
        );

        // Replace the Guzzle client with our mocked version
        $mockedGuzzle = new Client(['handler' => $handlerStack]);
        $reflection = new \ReflectionClass($this->client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->client, $mockedGuzzle);
    }

    public function test_handles_unauthorized_error()
    {
        $this->mockHandler->append(
            new Response(401, [], json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid credentials'
            ]))
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to send SMS: Client error: `POST /api/sms/v1/text/single` resulted in a `401 Unauthorized`');

        $this->client->sendSMS('255738234345', 'Test message');
    }

    public function test_handles_network_error()
    {
        $this->mockHandler->append(
            new RequestException(
                'Network error',
                new Request('POST', '/api/sms/v1/text/single')
            )
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to send SMS: Network error');

        $this->client->sendSMS('255738234345', 'Test message');
    }

    public function test_handles_invalid_response()
    {
        $this->mockHandler->append(
            new Response(200, [], 'Invalid JSON')
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to send SMS: Syntax error');

        $this->client->sendSMS('255738234345', 'Test message');
    }

    public function test_handles_server_error()
    {
        $this->mockHandler->append(
            new Response(500, [], json_encode([
                'error' => 'Internal Server Error',
                'message' => 'Something went wrong'
            ]))
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to send SMS: Server error: `POST /api/sms/v1/text/single` resulted in a `500 Internal Server Error`');

        $this->client->sendSMS('255738234345', 'Test message');
    }

    public function test_handles_rate_limit_error()
    {
        $this->mockHandler->append(
            new Response(429, [], json_encode([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded'
            ]))
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to send SMS: Client error: `POST /api/sms/v1/text/single` resulted in a `429 Too Many Requests`');

        $this->client->sendSMS('255738234345', 'Test message');
    }
} 