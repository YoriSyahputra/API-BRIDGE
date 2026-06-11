<?php
namespace App\Private;

class PrivateKeyScheme
{
    private const string SHIPPING_KEY1 = '(......)'; // Set Your Private Key for Admin Here
    private const string SHIPPING_KEY2 = '(......)'; // Set Your Private Key for User/Customer Here

    public static function getPrivateKey($choose = 'first'): string
    {

    return match($choose){
        default => self::SHIPPING_KEY1,
        'first' =>self::SHIPPING_KEY2
      };
    }
}

