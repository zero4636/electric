<?php

namespace App\Helpers;

class NumberToWords
{
    private static $units = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    private static $tens = ['', 'mười', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
    
    public static function convert($number)
    {
        if ($number == 0) {
            return 'không';
        }
        
        if ($number < 0) {
            return 'âm ' . self::convert(-$number);
        }
        
        $result = '';
        
        // Tỷ
        if ($number >= 1000000000) {
            $billions = floor($number / 1000000000);
            $result .= self::convertGroup($billions) . ' tỷ ';
            $number %= 1000000000;
        }
        
        // Triệu
        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            $result .= self::convertGroup($millions) . ' triệu ';
            $number %= 1000000;
        }
        
        // Nghìn
        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            $result .= self::convertGroup($thousands) . ' nghìn ';
            $number %= 1000;
        }
        
        // Trăm
        if ($number > 0) {
            $result .= self::convertGroup($number);
        }
        
        return ucfirst(trim($result));
    }
    
    private static function convertGroup($number)
    {
        if ($number == 0) {
            return '';
        }
        
        $result = '';
        
        // Hàng trăm
        $hundreds = floor($number / 100);
        if ($hundreds > 0) {
            $result .= self::$units[$hundreds] . ' trăm ';
            $number %= 100;
            
            if ($number > 0 && $number < 10) {
                $result .= 'linh ';
            }
        }
        
        // Hàng chục
        if ($number >= 10) {
            $tensDigit = floor($number / 10);
            $unitsDigit = $number % 10;
            
            if ($tensDigit == 1) {
                $result .= 'mười ';
            } else {
                $result .= self::$units[$tensDigit] . ' mươi ';
            }
            
            if ($unitsDigit == 1 && $tensDigit >= 1) {
                $result .= 'một';
            } elseif ($unitsDigit == 5 && $tensDigit >= 1) {
                $result .= 'lăm';
            } elseif ($unitsDigit > 0) {
                $result .= self::$units[$unitsDigit];
            }
        } else if ($number > 0) {
            $result .= self::$units[$number];
        }
        
        return trim($result);
    }
}
