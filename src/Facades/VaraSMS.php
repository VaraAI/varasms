<?php

namespace VaraSMS\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendSMS(string $to, string $message, ?string $senderId = null, ?string $reference = null)
 * @method static array sendBulkSMS(array $messages, ?string $senderId = null, ?string $reference = null)
 * @method static array getBalance()
 * @method static array rechargeCustomer(string $email, int $smsCount)
 * @method static array deductCustomer(string $email, int $smsCount)
 */
class VaraSMS extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'varasms';
    }
} 