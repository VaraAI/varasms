# VaraSMS Laravel Package

A Laravel package for integrating with the VaraSMS messaging service. This package provides an easy way to send SMS messages, manage customer balances, and interact with the VaraSMS API.

## Installation

You can install the package via composer:

```bash
composer require varaai/varasms
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="VaraSMS\Laravel\VaraSMSServiceProvider"
```

Add the following environment variables to your `.env` file:

For username/password authentication (default):
```env
VARASMS_AUTH_METHOD=basic
VARASMS_USERNAME=your_username
VARASMS_PASSWORD=your_password
VARASMS_SENDER_ID=your_sender_id
VARASMS_BASE_URL=https://messaging-service.co.tz
```

Or for token-based authentication:
```env
VARASMS_AUTH_METHOD=token
VARASMS_TOKEN=your_authorization_token
VARASMS_SENDER_ID=your_sender_id
VARASMS_BASE_URL=https://messaging-service.co.tz
```

## Usage

### Send a Single SMS

```php
use VaraSMS\Laravel\Facades\VaraSMS;

// Basic usage
$response = VaraSMS::sendSMS('255738234345', 'Hello World!');

// With custom sender ID and reference
$response = VaraSMS::sendSMS(
    '255738234345',
    'Hello World!',
    'MYSENDER',
    'ref123'
);
```

### Send Bulk SMS

```php
use VaraSMS\Laravel\Facades\VaraSMS;

$messages = [
    [
        'to' => '255738234345',
        'message' => 'Hello User 1!',
        'reference' => 'ref1'
    ],
    [
        'to' => '255738234346',
        'message' => 'Hello User 2!',
        'reference' => 'ref2'
    ]
];

$response = VaraSMS::sendBulkSMS($messages);
```

### Check Balance

```php
use VaraSMS\Laravel\Facades\VaraSMS;

$balance = VaraSMS::getBalance();
```

### Recharge Customer

```php
use VaraSMS\Laravel\Facades\VaraSMS;

$response = VaraSMS::rechargeCustomer('customer@example.com', 5000);
```

### Deduct Customer Balance

```php
use VaraSMS\Laravel\Facades\VaraSMS;

$response = VaraSMS::deductCustomer('customer@example.com', 2000);
```

## Response Format

All methods return an array containing the API response. Here are some example responses:

### Send SMS Response
```php
[
    'success' => true,
    'message' => 'Message sent successfully',
    'reference' => 'ref123'
]
```

### Balance Response
```php
[
    'sms_balance' => 5000
]
```

### Recharge/Deduct Response
```php
[
    'success' => true,
    'status' => 200,
    'message' => 'Transaction completed successfully',
    'result' => [
        'Customer' => 'customer@example.com',
        'Sms transferred' => 5000,
        'Your sms balance' => 450000
    ]
]
```

## Error Handling

The package throws exceptions when API calls fail. It's recommended to wrap API calls in try-catch blocks:

```php
try {
    $response = VaraSMS::sendSMS('255738234345', 'Hello World!');
} catch (\Exception $e) {
    // Handle the error
    Log::error('SMS sending failed: ' . $e->getMessage());
}
```

## Testing

The package includes comprehensive tests. To run the tests:

```bash
composer test
```

To generate test coverage reports:

```bash
composer test-coverage
```

### Test Environment

For testing your application with VaraSMS, you can use the test mode by setting your base URL to the test endpoint:

```env
VARASMS_BASE_URL=https://messaging-service.co.tz/api/sms/v1/test
```

### Writing Tests

When writing tests for your application that uses VaraSMS, you can mock the VaraSMS facade:

```php
use VaraSMS\Laravel\Facades\VaraSMS;

class YourTest extends TestCase
{
    public function test_sends_sms()
    {
        VaraSMS::shouldReceive('sendSMS')
            ->once()
            ->with('255738234345', 'Test message')
            ->andReturn([
                'success' => true,
                'message' => 'Message sent successfully'
            ]);

        // Your test code here
    }
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 