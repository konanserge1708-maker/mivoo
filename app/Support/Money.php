<?php

namespace App\Support;

class Money
{
    public static function formatXof(int $amount): string
    {
        return number_format($amount, 0, ',', ' ').' F CFA';
    }

    public static function formatShort(int $amount): string
    {
        return number_format($amount, 0, ',', ' ').' F';
    }
}
