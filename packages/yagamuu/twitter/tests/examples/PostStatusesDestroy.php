<?php

namespace yagamuu\TwitterClientForRtainjapan\Tests\Examples;

class PostStatusesDestroy
{
    public static function example()
    {
        return json_decode(json_encode([
            "created_at"=> "Tue Oct 16 00=>37=>45 +0000 2018",
            "id"=> 1051995576441733120,
            "id_str"=> "1051995576441733120",
            "text"=> "This will is going to be deleted",
            "truncated"=> false,
            "entities"=> [
              "hashtags"=> [],
              "symbols"=> [],
              "user_mentions"=> [],
              "urls"=> []
            ],
            "source"=> "Twitter Web Client",
            "in_reply_to_status_id"=> null,
            "in_reply_to_status_id_str"=> null,
            "in_reply_to_user_id"=> null,
            "in_reply_to_user_id_str"=> null,
            "in_reply_to_screen_name"=> null,
            "user"=> [
              "id"=> 2244994945,
              "id_str"=> "2244994945",
              "name"=> "TwitterDev",
              "screen_name"=> "TwitterDev",
              "location"=> "The World",
              "description"=> "TwitterDev",
              "url"=> null,
              "entities"=> [
                "description"=> [
                  "urls"=> []
                ]
            ],
              "protected"=> false,
              "followers_count"=> 2,
              "friends_count"=> 8,
              "listed_count"=> 0,
              "created_at"=> "Sun Jul 15 02=>31=>20 +0000 2018",
              "favourites_count"=> 10,
              "utc_offset"=> null,
              "time_zone"=> null,
              "geo_enabled"=> true,
              "verified"=> false,
              "statuses_count"=> 46,
              "lang"=> "en",
              "contributors_enabled"=> false,
              "is_translator"=> false,
              "is_translation_enabled"=> null,
              "profile_background_color"=> "null",
              "profile_background_image_url"=> null,
              "profile_background_image_url_https"=> null,
              "profile_background_tile"=> null,
              "profile_image_url"=> "null",
              "profile_image_url_https"=> "https=>//pbs.twimg.com/profile_images/103426685007701430/CDRBFNN2_normal.jpg",
              "profile_banner_url"=> "https=>//pbs.twimg.com/profile_banners/1018322089701531651/1539492123",
              "profile_link_color"=> "null",
              "profile_sidebar_border_color"=> "null",
              "profile_sidebar_fill_color"=> "null",
              "profile_text_color"=> "null",
              "profile_use_background_image"=> null,
              "has_extended_profile"=> null,
              "default_profile"=> true,
              "default_profile_image"=> false,
              "following"=> false,
              "follow_request_sent"=> false,
              "notifications"=> false,
              "translator_type"=> "none"
        ],
            "geo"=> null,
            "coordinates"=> null,
            "place"=> null,
            "contributors"=> null,
            "is_quote_status"=> false,
            "retweet_count"=> 0,
            "favorite_count"=> 0,
            "favorited"=> false,
            "retweeted"=> false,
            "lang"=> "en"
        ]), false);
    }
}
