<?php

namespace yagamuu\TwitterClientForRtainjapan\Tests\Examples;

class PostMediaUpload
{
    public static function example()
    {
        return json_decode(json_encode(
            [
                "media_id"=> 710511363345354753,
                "media_id_string"=> "710511363345354753",
                "media_key"=> "3_710511363345354753",
                "size"=> 11065,
                "expires_after_secs"=> 86400,
                "image"=> [
                    "image_type"=> "image/jpeg",
                    "w"=> 800,
                    "h"=> 320
                ]
            ]
        ), false);
    }
}
