<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Carbon;

class Helper {
    public static function isBase64($str) {
        $decoded = base64_decode($str, true);
        return ($decoded !== false);
    }

    public static function isDataImageValid(string $imageUrl) {

        // Split the URL at the comma to separate the data type from the Base64 data
        $urlParts = explode(',', $imageUrl, 2);

        if (count($urlParts) === 2) {
            // The Base64 data is in $urlParts[1]
            $base64Data = $urlParts[1];
            $imageUrl = $base64Data;
        }
                
        return self::isBase64($imageUrl);
    }

    public static function extractBase64ImagePart(string $imageUrl) {

        // Split the URL at the comma to separate the data type from the Base64 data
        $urlParts = explode(',', $imageUrl, 2);

        if (count($urlParts) === 2) {
            // The Base64 data is in $urlParts[1]
            $base64Data = $urlParts[1];
            
           return $base64Data;
        }
        
        throw new Exception("Invalid data:image URL format.", 1);
    }

    public static function extractBase64Image(string $imageUrl) {
        return base64_decode(self::extractBase64ImagePart($imageUrl));
    }

    public static function loanDuedifference(Carbon $dueDateObj) {
        $currentDate = Carbon::now();
        if($currentDate->isSameAs("Y-m-d",$dueDateObj))
            return "Due Today";
        else if ($currentDate->greaterThan($dueDateObj)) {
            return $dueDateObj->diffForHumans(null, true) . " overdue";
        } else if ($currentDate->lessThan($dueDateObj)) {
            return $dueDateObj->diffForHumans(null, true, false) . " left";
        }
    }

    public static function diffDays(Carbon $firstDate, Carbon $secondDate) {
        $due_date = $secondDate->startOfDay();
        $now = $firstDate->startOfDay();
        return round($now->floatDiffInDays($due_date, false));
    }
}

