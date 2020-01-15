<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Constants\ApiConstant;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;
use yagamuu\TwitterClientForRtainjapan\Twitter;

class ActionController extends ApiController
{
    
    /** @var Twitter */
    protected $twitter;

    public function __construct(Twitter $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * ツイート投稿をする
     * @param ServerRequestInterface $request リクエスト
     * @param ResponseInterface $response レスポンス
     * @return ResponseInterface
     */
    public function postTweet(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $result = $this->twitter->postUpdate($data['status'] ?? '', $data['media_ids'] ?? []);

        if (count($result['errors']) > 0) {
            return $this->errorResponse($response, implode(PHP_EOL, $result['errors']));
        }

        $payload = [
            'code' => ApiConstant::API_RESPONSE_CODE_OK,
            'data' => $result['data']
        ];
        return $this->asJson($response, $payload);
    }

    /**
     * メディアをTwitterAPIのmedia/uploadに投げる
     * @param ServerRequestInterface $request リクエスト
     * @param ResponseInterface $response レスポンス
     * @return ResponseInterface
     */
    public function postUpload(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        /** @var UploadedFile */ 
        $uploadedFile = reset($uploadedFiles);
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return $this->errorResponse($response, 'Failed to post upload with Twitter API.');
        }

        $filePath = $uploadedFile->getStream()->getMetadata()['uri'];
        $data = $this->twitter->uploadMedia($filePath, $uploadedFile->getClientMediaType(), $uploadedFile->getClientFilename());

        if (count($data['errors']) > 0) {
            return $this->errorResponse($response, implode(PHP_EOL, $data['errors']));
        }

        $payload = [
            'code' => ApiConstant::API_RESPONSE_CODE_OK,
            'data' => [
                'media_id_string' => $data['media_id_string']
            ]
        ];
        return $this->asJson($response, $payload);
    }

    public function deleteTweet(String $id, Response $response): Response
    {
        $tweetId = $id ?? null;
        if ($tweetId === null) {
            return $this->errorResponse($response, 'Tweet ID was not defined.');
        }

        $data = $this->twitter->deleteTweet($tweetId);
        if (count($data['errors']) > 0) {
            return $this->errorResponse($response, implode(PHP_EOL, $data['errors']));
        }
        $payload = [
            'code' => ApiConstant::API_RESPONSE_CODE_OK
        ];

        return $this->asJson($response, $payload);
    }
}
