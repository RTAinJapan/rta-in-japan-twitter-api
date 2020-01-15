<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Constants\ApiConstant;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use yagamuu\TwitterClientForRtainjapan\Twitter;

class TimelineController extends ApiController
{

    /** @var Twitter */
    protected $twitter;

    public function __construct(Twitter $twitter)
    {
        $this->twitter = $twitter;
    }

    public function getUserTimeline(Request $request, Response $response): Response
    {
        $data = $this->twitter->getUserTimeline();

        if (count($data['errors']) > 0) {
            return $this->errorResponse($response, implode(PHP_EOL, $data['errors']));
        }

        if (!array_key_exists('user_timelines', $data)) {
            return $this->asJson($response, [
                'code' => ApiConstant::API_RESPONSE_CODE_ERROR,
                'error' => [
                    'message' => 'Failed to get user_timeline with Twitter API.'
                ]
            ], ApiConstant::HTTP_STATUS_CODE_ERROR);
        }

        $payload = [
            'code' => ApiConstant::API_RESPONSE_CODE_OK,
            'data' => $data['user_timelines']
        ];
        return $this->asJson($response, $payload);
    }

    public function getMentions(Request $request, Response $response): Response
    {
        $data = $this->twitter->getMentionsTimeline();
        
        if (count($data['errors']) > 0) {
            return $this->errorResponse($response, implode(PHP_EOL, $data['errors']));
        }

        if (!array_key_exists('mentions_timelines', $data)) {
            return $this->asJson($response, [
                'code' => ApiConstant::API_RESPONSE_CODE_ERROR,
                'error' => [
                    'message' => 'Failed to get mentions_timeline with Twitter API.'
                ]
            ], ApiConstant::HTTP_STATUS_CODE_ERROR);
        }
        $payload = [
            'code' => ApiConstant::API_RESPONSE_CODE_OK,
            'data' => $data['mentions_timelines']
        ];
        return $this->asJson($response, $payload);
    }

    public function getSearchResultByHashTag(Request $request, Response $response): Response
    {
        $data = $this->twitter->getSearchTweet(getenv('SEARCH_QUERY'));
        
        if (count($data['errors']) > 0) {
            return $this->errorResponse($response, implode(PHP_EOL, $data['errors']));
        }

        $payload = [
            'code' => ApiConstant::API_RESPONSE_CODE_OK,
            'data' => $data['result']
        ];
        return $this->asJson($response, $payload);
    }
}
