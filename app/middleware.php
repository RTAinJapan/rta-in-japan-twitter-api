<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use App\Middleware\SessionMiddleware;

return function (App $app) {
    // $app->add(SessionMiddleware::class);
    $app->add(function (Request $request, RequestHandler $handler) {
        $sessionMiddleware = new SessionMiddleware();
        return $sessionMiddleware->process($request, $handler);
    });
};
