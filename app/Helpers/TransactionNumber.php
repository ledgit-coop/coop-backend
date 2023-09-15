<?php

namespace App\Helpers;

use DateTime;

class TransactionNumber {
    public static function generateTransactionNumber($prefix = 'DST') {
        $dateTime = new DateTime();
        $timestamp = $dateTime->format('YmdHis');
        $randomPart = mt_rand(1000, 9999); // You can adjust the range as needed
        $transactionNumber = $prefix . '-' . $timestamp . '-' . $randomPart;
        return $transactionNumber;
    }
}

