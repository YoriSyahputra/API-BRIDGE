<?php
namespace App\Private;

class PrivateKeyScheme
{
    const SHIPPING_KEY1 = 'C/qLUDR6I85iHlEkZLCHvQ==';
    const SHIPPING_KEY2 = 'ex2vK6GQc9QsR011O7UOKA==';
    const SHIPPING_KEY3 ='pksdms';

    public static function getPrivateKey($choose = 'first')
    {
        if($choose === 'second') {
            return self::SHIPPING_KEY2;
        }elseif($choose === 'third') {
            return self::SHIPPING_KEY3;
        }
        
        return self::SHIPPING_KEY1;
    }
}