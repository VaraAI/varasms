<?php

namespace VaraSMS\Laravel\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use VaraSMS\Laravel\VaraSMSClient;
use GuzzleHttp\Middleware;

class VaraSMSClientTest extends TestCase
{
    protected $container = [];
    protected $mockHandler;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        
        // Add history middleware
        $history = Middleware::history($this->container);
        $handlerStack->push($history);

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

    public function test_send_single_sms()
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Message sent successfully',
            'reference' => 'ref123'
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $response = $this->client->sendSMS(
            '255738234345',
            'Test message',
            'SENDER',
            'ref123'
        );

        $this->assertEquals($expectedResponse, $response);

        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/api/sms/v1/text/single', $request->getUri()->getPath());
        
        $requestBody = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals('255738234345', $requestBody['to']);
        $this->assertEquals('Test message', $requestBody['message']);
        $this->assertEquals('SENDER', $requestBody['from']);
        $this->assertEquals('ref123', $requestBody['reference']);
    }

    public function test_send_bulk_sms()
    {
        $expectedResponse = [
            'success' => true,
            'message' => 'Messages sent successfully',
            'references' => ['ref1', 'ref2']
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $messages = [
            [
                'to' => '255738234345',
                'message' => 'Message 1',
                'reference' => 'ref1'
            ],
            [
                'to' => '255738234346',
                'message' => 'Message 2',
                'reference' => 'ref2'
            ]
        ];

        $response = $this->client->sendBulkSMS($messages, 'SENDER');

        $this->assertEquals($expectedResponse, $response);

        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/api/sms/v1/text/multi', $request->getUri()->getPath());
        
        $requestBody = json_decode($request->getBody()->getContents(), true);
        $this->assertCount(2, $requestBody['messages']);
        $this->assertEquals('255738234345', $requestBody['messages'][0]['to']);
        $this->assertEquals('Message 1', $requestBody['messages'][0]['message']);
    }

    public function test_get_balance()
    {
        $expectedResponse = [
            'sms_balance' => 5000
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $response = $this->client->getBalance();

        $this->assertEquals($expectedResponse, $response);

        $request = $this->container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/api/sms/v1/balance', $request->getUri()->getPath());
    }

    public function test_recharge_customer()
    {
        $expectedResponse = [
            'success' => true,
            'status' => 200,
            'message' => 'Transaction completed successfully',
            'result' => [
                'Customer' => 'test@example.com',
                'Sms transferred' => 5000,
                'Your sms balance' => 450000
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $response = $this->client->rechargeCustomer('test@example.com', 5000);

        $this->assertEquals($expectedResponse, $response);

        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/api/reseller/v1/sub_customer/recharge', $request->getUri()->getPath());
        
        $requestBody = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals('test@example.com', $requestBody['email']);
        $this->assertEquals(5000, $requestBody['smscount']);
    }

    public function test_deduct_customer()
    {
        $expectedResponse = [
            'success' => true,
            'status' => 200,
            'message' => 'Transaction completed successfully',
            'result' => [
                'Customer' => 'test@example.com',
                'Sms deducted' => 2000,
                'Your sms balance' => 470000,
                'Customer sms balance' => 3000
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($expectedResponse))
        );

        $response = $this->client->deductCustomer('test@example.com', 2000);

        $this->assertEquals($expectedResponse, $response);

        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/api/reseller/v1/sub_customer/deduct', $request->getUri()->getPath());
        
        $requestBody = json_decode($request->getBody()->getContents(), true);
        $this->assertEquals('test@example.com', $requestBody['email']);
        $this->assertEquals(2000, $requestBody['smscount']);
    }
} 