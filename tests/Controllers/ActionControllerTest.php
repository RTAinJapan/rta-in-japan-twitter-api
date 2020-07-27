<?php
declare(strict_types=1);

namespace Tests\Controllers;

use Tests\TestCase;
use DI\Container;
use yagamuu\TwitterClientForRtainjapan\Twitter;
use yagamuu\TwitterClientForRtainjapan\Tests\Examples\GetStatusesUserTimelines;
use org\bovigo\vfs\vfsStream;
use Slim\Psr7\UploadedFile;

class ActionControllerTest extends TestCase
{

    /** @test */
    public function testPostTweet()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();
        
        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->postUpdate('Hello, Twitter!', [])
            ->willReturn([
                'errors' => [],
                'data' => GetStatusesUserTimelines::example()
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request_body = [
            'status' => 'Hello, Twitter!',
            'media_ids' => []
        ];
        
        $request = $this->createRequest('POST', '/api/twitter/statuses/update');
        $request = $request->withParsedBody($request_body);
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
    public function testPostMedia()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();

        // アップロードファイルのモック
        $root = vfsStream::setup('tmp');
        $file = vfsStream::newFile('test.mp4')->at($root)->setContent('test');

        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->uploadMedia(vfsStream::url($root->getName() . '/' . $file->getName()), 'video/mp4', 'test.mp4')
            ->willReturn([
                'errors' => [],
                'media_id_string' => '123456789'
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());

        $request = $this->createRequest('POST', '/api/twitter/media/upload');
        $request = $request->withUploadedFiles([new UploadedFile(vfsStream::url($root->getName() . '/' . $file->getName()), 'test.mp4', 'video/mp4', 256000000)]);

        $response = $app->handle($request);
        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 0,
            'data' => ['media_id_string' => '123456789']
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }
    
    /** @test */
    public function testPostMediaWithEmptyFiles()
    {
        $app = $this->getAppInstance();
        
        $request = $this->createRequest('POST', '/api/twitter/media/upload');

        $response = $app->handle($request);
        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 10,
            'error' => ['message' => 'Failed to get uploaded file.']
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }
    

    /** @test */
    public function testDeleteTweet()
    {
        $app = $this->getAppInstance();

        /** @var Container */
        $container = $app->getContainer();
        
        // Twitterクラスのモックオブジェクト
        $twitterProphecy = $this->prophesize(Twitter::class);
        $twitterProphecy
            ->deleteTweet(1)
            ->willReturn([
                'errors' => [],
                'informations' => [
                    'text' => '削除しました',
                    'url' => 'https://twitter.com/test_user/status/123456789'
                ]
            ])
            ->shouldBeCalledOnce();

        // DIコンテナにTwitterモックを設定
        $container->set(Twitter::class, $twitterProphecy->reveal());
        
        $request = $this->createRequest('POST', '/api/twitter/statuses/destroy/1');
        $response = $app->handle($request);
        $payload = (string)$response->getBody();
        $expectedData = [
            'code' => 0
        ];
        $expected = json_encode($expectedData, JSON_PRETTY_PRINT);

        $this->assertEquals($expected, $payload);
    }
       
}