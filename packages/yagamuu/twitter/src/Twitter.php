<?php

namespace yagamuu\TwitterClientForRtainjapan;

use mpyw\Co\Co;

class Twitter
{
    protected $client;
    
    /** @var Cache */
    protected $cache;

    public function __construct($client, $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * タイムラインの取得
     * @param bool $force キャッシュを利用せずにAPI実行
     * @return array API実行結果
     */
    public function getUserTimeline(bool $force = false): array
    {
        $errors = [];
        $user_timelines = [];

        // キャッシュがあったら返却
        if (!$force && $user_timelines = $this->cache->getItem('user_timelines')->get() ?? []) {
            return [
                'errors' => $errors,
                'user_timelines' => $user_timelines
            ];
        }

        try {
            $user_timelines = $this->client->get('statuses/user_timeline', [
                'screen_name' => getenv('SCREEN_NAME'),
                'count'       => 10
            ]);

            // キャッシュの更新
            $this->saveCache($user_timelines, 'user_timelines');
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors' => $errors,
            'user_timelines' => $user_timelines
        ];
    }

    /**
     * メンションの取得
     * @param bool $force キャッシュを利用せずにAPI実行
     * @return array API実行結果
     */
    public function getMentionsTimeline(bool $force = false): array
    {
        $errors = [];
        $mentions_timelines = [];

        // キャッシュがあったら返却
        if (!$force && $mentions_timelines = $this->cache->getItem('mentions_timelines')->get() ?? []) {
            return [
                'errors' => $errors,
                'mentions_timelines' => $mentions_timelines
            ];
        }

        try {
            $mentions_timelines = $this->client->get('statuses/mentions_timeline', [
                'count'       => 10
            ]);

            // キャッシュの更新
            $this->saveCache($mentions_timelines, 'mentions_timelines');
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors' => $errors,
            'mentions_timelines' => $mentions_timelines
        ];
    }

    public function deleteTweet($id)
    {
        $errors = [];
        $informations = [];

        try {
            $status = $this->client->post('statuses/destroy', [
                'id' => $id
            ]);
            $informations[] = [
                'text' => '削除しました',
                'url' => self::getTweetUrl($status),
            ];

            // キャッシュの更新
            $user_timelines = $this->getUserTimeline(true);
            $this->saveCache($user_timelines['user_timelines'], 'user_timelines');
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors' => $errors,
            'informations' => $informations
        ];
    }

    public function postTweet($tweet_text, $files)
    {
        $result = [
            'errors' => [],
            'informations'  => [],
        ];
        $uploads = [];

        // 各ファイルをチェック
        $videoOrAnimeGifFlag = false;
        foreach ($files['media']['error'] as $key => $error) {
            try {
                // 更に配列がネストしていれば不正とする
                if (!is_int($error)) {
                    throw new \RuntimeException("[{$key}] パラメータが不正です");
                }

                // 値を確認
                switch ($error) {
                    case UPLOAD_ERR_OK: // OK
                        $fileName = $files['media']['tmp_name'][$key];
                        $clientFileName = $files['media']['name'][$key];
                        $uploads[] = [
                            'file' => $fileName,
                            'clientFileName' => $clientFileName
                        ];
                        if (self::isVideoFile($fileName) || self::isAnimeGif($fileName)) {
                            $videoOrAnimeGifFlag = true;
                        }
                        break;
                    case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                        continue 2;
                    case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                    case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                        throw new \RuntimeException("[{$key}] ファイルサイズが大きすぎます");
                    default:
                        throw new \RuntimeException("[{$key}] その他のエラーが発生しました");
                }

                if ($videoOrAnimeGifFlag && count($uploads) > 1) {
                    throw new \RuntimeException("動画やアニメgifを含む場合は1つだけアップロードしてください");
                }
            } catch (\RuntimeException $e) {
                $result['errors'][] = $e->getMessage();
            }
        }
        if (count($result['errors'])) {
            return $result;
        }

        if (count($uploads)) {
            $result = $this->tweetMedia($tweet_text, $uploads, $videoOrAnimeGifFlag);
        } elseif (isset($tweet_text) && $tweet_text !== '') {
            $result = $this->tweetText($tweet_text);
        }

        // キャッシュの更新
        $user_timelines = $this->getUserTimeline(true);
        $this->saveCache($user_timelines['user_timelines'], 'user_timelines');

        return $result;
    }

    private function tweetText($text)
    {
        $errors = [];
        $informations = [];
        try {
            $status = $this->client->post('statuses/update', [
                'status' => $text,
            ]);
        
            $informations[] = [
                'text' => '投稿しました',
                'url' => self::getTweetUrl($status),
            ];
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors' => $errors,
            'informations'  => $informations
        ];
    }

    private function tweetMedia($text, $files, $isVideo = false)
    {
        $errors = [];
        $informations  = [];

        try {
            $informations = Co::wait(function () use ($informations, $text, $files, $isVideo) {
                $status = [];
                $media_ids = [];
                // 通常の画像
                if (!$isVideo) {
                    $upload = [];
                    foreach ($files as $file) {
                        $fileObj = new \SplFileObject($file['file'], 'rb');
                        self::validateFileSize($fileObj->getPathname(), $file['clientFileName']);
                        $upload[] = $this->client->postMultipartAsync(
                            'media/upload',
                            [
                                'media' => new \CURLFile($file['file'])
                            ]
                        );
                    }
                    $info = yield $upload;
                    $media_ids = implode(',', array_column($info, 'media_id_string'));
                } else {
                    $file = $files[0];
                    $video = new \SplFileObject($file['file'], 'rb');
                    self::validateFileSize($video->getPathname(), $file['clientFileName']);
                    $method = self::isVideoFile($file['file']) ? 'uploadVideoAsync' : 'uploadAnimeGifAsync';
                    $media_ids = (yield $this->client->$method($video))->media_id_string;
                }

                $status = yield $this->client->postAsync('statuses/update', [
                    'status' => $text ?? '',
                    'media_ids' => $media_ids,
                ]);
                $informations[] = [
                    'text' => '投稿しました',
                    'url' => self::getTweetUrl($status),
                ];

                return $informations;
            });
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors'      => $errors,
            'informations' => $informations
        ];
    }

    /**
     * ツイートの検索を行う
     * @param string $query 検索クエリ
     * @param int $count 検索件数
     * @param bool $force キャッシュを利用せずにAPI実行
     * @return array API実行結果
     */
    public function getSearchTweet(string $query, int $count = 15, bool $force = false): array
    {
        $result = [];
        $errors = [];
        $key = hash('sha256', 'search_tweet_' . $query . '_' . $count);

        // キャッシュがあったら返却
        if (!$force && $result = $this->cache->getItem($key)->get() ?? []) {
            return [
                'errors' => $errors,
                'result' => $result
            ];
        }

        try {
            $result = $this->client->get('search/tweets', [
                'q' => $query,
                'count' => $count,
                'result_type' => 'recent'
            ]);
            
            // キャッシュの更新
            $this->saveCache($result->statuses, $key);
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors' => $errors,
            'result' => $result->statuses ?? []
        ];
    }

    /**
     * ツイートの投稿を行う
     * @param string $status ツイート本文
     * @param array $media_ids アップロードしたいメディアのid
     * @param array $options その他パラメーター
     * @return array
     */
    public function postUpdate(string $status, array $media_ids = [], array $options = []): array
    {
        $user_timelines = [];
        $errors = [];

        $request = ['status' => $status ?? ''];
        if (count($media_ids) > 0) {
            $request['media_ids'] = implode(',', $media_ids);
        }

        if (!empty($options)) {
            $request = self::setRequestOptionParam($request, $options);
        }

        try {
            $this->client->post('statuses/update', $request);

            $user_timelines = $this->getUserTimeline(true);
            // キャッシュの更新
            $this->saveCache($user_timelines['user_timelines'], 'user_timelines');
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors' => $errors,
            'data'   => $user_timelines['user_timelines'] ?? []
        ];
    }

    /**
     * メディアをアップロードしmedia_idを返却する
     * @param string $path アップロードしたメディアのファイルパス
     * @param string $type アップロードしたメディアのタイプ
     * @param string $name アップロードしたメディアのファイル名
     * @return array
     */
    public function uploadMedia(string $path, string $type, string $name): array
    {
        $media_id_string = '';
        $errors = [];
        try {
            self::validateFileSize($path, $name);
            if (self::isVideoFile($path) || self::isAnimeGif($path)) {
                $media_id_string = Co::wait(function () use ($path) {
                    $video = new \SplFileObject($path, 'rb');
                    $method = self::isVideoFile($path) ? 'uploadVideoAsync' : 'uploadAnimeGifAsync';
                    return (yield $this->client->$method($video))->media_id_string;
                });
            } else {
                $status = $this->client->post('media/upload', [
                    'media' => new \CURLFile($path, $type, $name)
                ]);
                $media_id_string = $status->media_id_string;
            }
        } catch (\RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'errors'          => $errors,
            'media_id_string' => $media_id_string
        ];
    }

    private static function setRequestOptionParam(array $request, array $options): array
    {
        foreach ($options as $key => $value) {
            if ($key === 'in_reply_to_status_id') {
                $request['in_reply_to_status_id'] = $value;
            }
            if ($key === 'attachment_url' && filter_var($value, FILTER_VALIDATE_URL) && preg_match('|^https?://twitter.com/.*$|', $value)) {
                $request['attachment_url'] = $value;
            }
        }
        return $request;
    }

    private static function validateFileSize(string $path, string $submitFileName): bool
    {
        if (self::isVideoFile($path)) {
            $fileType = 'Video';
            $maxFileSize = Constant::MAX_FILE_SIZE_VIDEO;
        } else if (self::isAnimeGif($path)) {
            $fileType = 'Animation GIF';
            $maxFileSize = Constant::MAX_FILE_SIZE_GIF_ANIME;
        } else {
            $fileType = 'Image';
            $maxFileSize = Constant::MAX_FILE_SIZE_IMAGE;
        }

        $file = new \SplFileObject($path, 'rb');
        if ($file->getSize() > $maxFileSize) {
            throw new ValidationErrorException([
                $submitFileName => $fileType . ' size must be <= ' . $maxFileSize . ' bytes'
            ]);
        }

        return true;
    }

    private static function getTweetUrl($status)
    {
        return 'https://twitter.com/'
        . $status->user->screen_name
        . '/status/'
        . $status->id_str;
    }

    private static function isVideoFile($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file);
        finfo_close($finfo);
        return in_array($mime_type, ['video/mp4', 'video/quicktime', 'video/x-m4v'], true);
    }

    private static function isAnimeGif($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file);
        finfo_close($finfo);
        if ($mime_type !== 'image/gif') {
            return false;
        }

        $imagick = new \Imagick();
        $imagick->readImage($file);
        $image_frames = $imagick->getNumberImages();
        $imagick->clear();

        return $image_frames > 1;
    }

    /**
     * キャッシュの保存
     * @param array $data キャッシュに格納したいデータ
     * @param string $key キャッシュのキー名
     * @param int $expires 有効期限(秒)
     * @return void
     */
    private function saveCache(array $data, string $key, int $expires = 60): void
    {
        $item = $this->cache->getItem($key);
        $item->set($data)->expiresAfter($expires);
        $this->cache->save($item);
    }
}
