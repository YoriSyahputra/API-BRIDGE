<?php
namespace App\Private;

class PrivateKeyScheme
{
   private static function getKey1(): string
    {
        return env('SHIPPING_KEY_ADMIN');
    }

    private static function getKey2(): string
    {
        return env('SHIPPING_KEY_USER');
    }

    public static function getPrivateKey($choose = 'first'): string
    {

    return match($choose){
        default => self::getKey1(),
        'first' =>self::getKey2()
      };
    }
}

