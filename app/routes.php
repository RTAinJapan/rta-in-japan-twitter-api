<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Slim\App;
use App\Controllers\TimelineController;
use App\Controllers\ActionController;
use Slim\Routing\RouteCollectorProxy;
use Slim\Middleware\BodyParsingMiddleware;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/api/twitter', function(RouteCollectorProxy $group) {
        $group->get('/statuses/user_timeline', [TimelineController::class, 'getUserTimeline']);
        $group->get('/statuses/mentions_timeline', [TimelineController::class, 'getMentions']);
        $group->get('/statuses/hash', [TimelineController::class, 'getSearchResultByHashTag']);

        $group->post('/statuses/update', [ActionController::class, 'postTweet'])->add(new BodyParsingMiddleware());
        $group->post('/media/upload', [ActionController::class, 'postUpload']);
        $group->post('/statuses/destroy/{id}', [ActionController::class, 'deleteTweet']);
    });
};
