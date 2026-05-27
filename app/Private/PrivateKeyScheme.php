<?php
namespace App\Private;

class PrivateKeyScheme
{
    private const string SHIPPING_KEY1 = 'C/qLUDR6I85iHlEkZLCHvQ==';
    private const string SHIPPING_KEY2 = 'ex2vK6GQc9QsR011O7UOKA==';
    private const string SHIPPING_KEY3 = 'pksdms';

    public static function getPrivateKey($choose = 'first'): string
    {

    return match($choose){
        default => self::SHIPPING_KEY1,
        'first' =>self::SHIPPING_KEY2,
        'second' => self::SHIPPING_KEY3,
      };
    }
}