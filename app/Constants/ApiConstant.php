<?php
declare(strict_types=1);

namespace App\Constants;

class ApiConstant
{
    // HTTPレスポンスコード
    /** エラー発生時 */
    const HTTP_STATUS_CODE_ERROR = 503;
    
    // APIで返却するステータスコード
    /** 正常終了時 */
    const API_RESPONSE_CODE_OK = 0;
    /** エラー発生時 */
    const API_RESPONSE_CODE_ERROR = 10;

    // /api/twitter/statuses/hashで検索するハッシュタグ
    const SEARCH_HASH_TAG = "#RTAinJapan exclude:retweets";
}
