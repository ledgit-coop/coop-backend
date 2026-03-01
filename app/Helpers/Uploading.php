<?php

namespace App\Helpers;

use App\Models\Member;
use Exception;
use Illuminate\Support\Facades\Storage;

class Uploading
{

    public static function storage()
    {
        return Storage::disk('public');
    }

    public static function memberImage(Member $member, string $image)
    {
        $storage = self::storage();

        if (!Helper::isDataImageValid($image)) throw new Exception("Image must be base64 format.", 1);

        $content = Helper::extractBase64Image($image);
        $path = "members/image/$member->id/$member->member_number.png";

        if ($storage->put($path, $content));
        return $path;

        throw new Exception("Failed to upload the image", 1);
    }

    public static function getMemberImageUrl($path)
    {
        return self::storage()->url($path);
    }

    public static function getUrl($path)
    {
        $base = config('app.url');
        return !empty($path) ? "$base$path" : null;
    }
}
