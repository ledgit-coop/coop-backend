<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\Member;
use App\Models\MemberAccount;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Uploading {

    public static function memberImage(Member $member, string $image) {
        if(!Helper::isDataImageValid($image)) throw new Exception("Image must be base64 format.", 1);

        $content = Helper::extractBase64Image($image);
        $path = "members/image/$member->id/$member->member_number.png";

        Log::info(Storage::url($path));
        if(Storage::put($path, $content));
            return Storage::url($path);


        throw new Exception("Failed to upload the image", 1);
    }

    public static function getUrl($path) {
        $base = config('app.url');
        return !empty($path) ? "$base$path" : null;
    }
}