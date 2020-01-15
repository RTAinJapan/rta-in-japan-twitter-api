<?php
declare(strict_types=1);

namespace Tests\Controllers;

use Tests\TestCase;
use DI\Container;
use App\Constants\ApiConstant;
use yagamuu\TwitterClientForRtainjapan\Tests\Examples\GetStatusesMentionTimelines;
use yagamuu\TwitterClientForRtainjapan\Tests\Examples\GetSearchTweets;
use yagamuu\TwitterClientForRtainjapan\Twitter;
use yagamuu\TwitterClientForRtainjapan\Tests\Examples\GetStatusesUserTimelines;

class TimelineControllerTest extends TestCase
{
    /**
     * GET /timeline のテスト.
     * based on https://github.com/slimphp/Slim-Skeleton/blob/master/tests/Application/Actions/User/ListUserActionTest.php
     *  @test
     */
    public function testGetTimeline()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();

        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->getUserTimeline()
            ->willReturn([
                'user_timelines' => GetStatusesUserTimelines::example(),
                'errors' => []
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request = $this->createRequest('GET', '/api/twitter/statuses/user_timeline');
        $response = $app->handle($request);

        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 0,
            'data' => GetStatusesUserTimelines::example()
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }
    
    /** @test */
    public function testGetMentions()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();

        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->getMentionsTimeline()
            ->willReturn([
                'mentions_timelines' => GetStatusesMentionTimelines::example(),
                'errors' => []
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request = $this->createRequest('GET', '/api/twitter/statuses/mentions_timeline');
        $response = $app->handle($request);

        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 0,
            'data' => GetStatusesMentionTimelines::example()
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }

    /** @test */
    public function testGetSearchResultByHashTag()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();

        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->getSearchTweet(getenv('SEARCH_QUERY'))
            ->willReturn([
                'result' => GetSearchTweets::example()->statuses,
                'errors' => []
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request = $this->createRequest('GET', '/api/twitter/statuses/hash');
        $response = $app->handle($request);

        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 0,
            'data' => GetSearchTweets::example()->statuses
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }

    /** @test */
    public function testErrorWhenUserTimeline()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();

        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->getUserTimeline()
            ->willReturn([
                'user_timelines' => [],
                'errors' => ['error message 1.', 'error happened.']
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request = $this->createRequest('GET', '/api/twitter/statuses/user_timeline');
        $response = $app->handle($request);

        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 10,
            'error' => [
                'message' => implode(PHP_EOL, ['error message 1.', 'error happened.'])
            ]
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }

    /** @test */
    public function testErrorWhenGetMentions()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();

        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->getMentionsTimeline()
            ->willReturn([
                'mentions_timelines' => [],
                'errors' => ['error message 1.', 'error happened.']
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request = $this->createRequest('GET', '/api/twitter/statuses/mentions_timeline');
        $response = $app->handle($request);

        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 10,
            'error' => [
                'message' => implode(PHP_EOL, ['error message 1.', 'error happened.'])
            ]
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }

    /** @test */
    public function testErrorWhenGetSearchResultByHashTag()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();

        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->getSearchTweet(getenv('SEARCH_QUERY'))
            ->willReturn([
                'result' => [],
                'errors' => ['error message 1.', 'error happened.']
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request = $this->createRequest('GET', '/api/twitter/statuses/hash');
        $response = $app->handle($request);

        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 10,
            'error' => [
                'message' => implode(PHP_EOL, ['error message 1.', 'error happened.'])
            ]
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }
}
