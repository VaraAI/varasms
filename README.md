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

### Get Delivery Reports

```php
use VaraSMS\Laravel\Facades\VaraSMS;

// Get all delivery reports
$reports = VaraSMS::getDeliveryReports();

// Get delivery report for a specific message ID
$report = VaraSMS::getDeliveryReport('28089492984101631440');

// Get delivery reports by date range (Deprecated)
$reports = VaraSMS::getDeliveryReportsByDateRange('2024-03-01', '2024-03-15');
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

### Get SMS Logs

```php
use VaraSMS\Laravel\Facades\VaraSMS;

// Get all SMS logs
$logs = VaraSMS::getSMSLogs();

// Get SMS logs with filters
$logs = VaraSMS::getSMSLogs([
    'from' => 'NEXTSMS',          // Filter by sender ID
    'to' => '255716718040',       // Filter by recipient number
    'sentSince' => '2024-03-01',  // Filter messages sent from this date
    'sentUntil' => '2024-03-15',  // Filter messages sent until this date
    'limit' => 500,               // Limit number of records (max 500)
    'offset' => 20,               // Skip first 20 records
    'reference' => 'ref123'       // Filter by message reference
]);
```

The `getSMSLogs` method supports the following filters:

| Filter | Description | Format/Constraints |
|--------|-------------|-------------------|
| from | Sender ID name | String |
| to | Destination phone number | Must start with 255 followed by 9 digits |
| sentSince | Lower limit on sending date | Y-m-d format (e.g., 2024-03-01) |
| sentUntil | Upper limit on sending date | Y-m-d format (e.g., 2024-03-15) |
| limit | Number of records to return | Integer (max 500) |
| offset | Number of records to skip | Integer |
| reference | Message reference | String |

All filters are optional. You can use any combination of filters to narrow down your search.

### SMS Logs Response
```php
[
    'success' => true,
    'logs' => [
        [
            'message_id' => '123456',
            'sender' => 'NEXTSMS',
            'recipient' => '255738234345',
            'message' => 'Hello World!',
            'status' => 'SENT',
            'timestamp' => '2024-03-24 10:30:00',
            'reference' => 'ref123'
        ],
        // ... more logs
    ],
    'pagination' => [
        'total' => 100,
        'offset' => 20,
        'limit' => 500
    ]
]
```

### Register Sub-Customer (Resellers Only)

```php
use VaraSMS\Laravel\Facades\VaraSMS;

$response = VaraSMS::registerSubCustomer([
    'first_name' => 'Api',
    'last_name' => 'Customer',
    'username' => 'apicust',
    'email' => 'apicust@customer.com',
    'phone_number' => '0738234339',  // Can be in format 0XXXXXXXXX or 255XXXXXXXXX
    'account_type' => 'Sub Customer (Reseller)', // or 'Sub Customer'
    'sms_price' => 20
]);
```

The `registerSubCustomer` method requires the following parameters:

| Parameter | Description | Format/Constraints |
|-----------|-------------|-------------------|
| first_name | Customer's first name | Required string |
| last_name | Customer's last name | Required string |
| username | Customer's username | Required string |
| email | Customer's email address | Valid email format |
| phone_number | Customer's phone number | Format: 0XXXXXXXXX or 255XXXXXXXXX |
| account_type | Type of sub-customer account | Must be either 'Sub Customer' or 'Sub Customer (Reseller)' |
| sms_price | Price per SMS for this customer | Positive number |

### Sub-Customer Registration Response
```php
[
    'success' => true,
    'message' => 'Sub-customer registered successfully',
    'data' => [
        'id' => 123,
        'first_name' => 'Api',
        'last_name' => 'Customer',
        'username' => 'apicust',
        'email' => 'apicust@customer.com',
        'phone_number' => '255738234339',
        'account_type' => 'Sub Customer (Reseller)',
        'sms_price' => 20,
        'created_at' => '2024-03-24 10:30:00'
    ]
]
```

Note: This feature is only available for customers in the reseller program.

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

### Delivery Reports Response
```php
[
    'success' => true,
    'reports' => [
        [
            'message_id' => '123456',
            'status' => 'DELIVERED',
            'recipient' => '255738234345',
            'timestamp' => '2024-03-24 10:30:00'
        ],
        // ... more reports
    ]
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

### Schedule SMS Messages

```php
use VaraSMS\Laravel\Facades\VaraSMS;

// Schedule a one-time SMS
$response = VaraSMS::scheduleSMS(
    '255716718040',
    'Your message',
    '2024-03-25',  // Date in Y-m-d format
    '14:30',       // Time in 24-hour format
    'NEXTSMS'      // Optional sender ID
);

// Schedule a recurring SMS
$response = VaraSMS::scheduleSMS(
    '255716718040',
    'Your recurring message',
    '2024-03-25',  // Initial send date
    '14:30',       // Time to send
    'NEXTSMS',     // Optional sender ID
    [
        'repeat' => 'daily',           // hourly, daily, weekly, or monthly
        'start_date' => '2024-03-25',  // Optional start date for recurring
        'end_date' => '2024-04-25'     // Optional end date for recurring
    ]
);
```

The `scheduleSMS` method supports both one-time and recurring message scheduling:

#### Required Parameters:
| Parameter | Description | Format/Constraints |
|-----------|-------------|-------------------|
| to | Recipient phone number | Must start with 255 followed by 9 digits |
| message | The message to send | String |
| date | Date to send the message | Y-m-d format (e.g., 2024-03-25) |
| time | Time to send the message | 24-hour format HH:mm (e.g., 14:30) |

#### Optional Parameters:
| Parameter | Description | Format/Constraints |
|-----------|-------------|-------------------|
| senderId | Custom sender ID | String |
| recurring | Array of recurring settings | See below |

#### Recurring Settings:
| Setting | Description | Format/Constraints |
|---------|-------------|-------------------|
| repeat | Frequency of repetition | Must be 'hourly', 'daily', 'weekly', or 'monthly' |
| start_date | Start date for recurring messages | Y-m-d format |
| end_date | End date for recurring messages | Y-m-d format |

### Schedule SMS Response
```php
[
    'success' => true,
    'message' => 'Message scheduled successfully',
    'data' => [
        'message_id' => '123456',
        'to' => '255716718040',
        'schedule_time' => '2024-03-25 14:30:00',
        'repeat' => 'daily',           // Only for recurring messages
        'start_date' => '2024-03-25',  // Only for recurring messages
        'end_date' => '2024-04-25'     // Only for recurring messages
    ]
]
``` 