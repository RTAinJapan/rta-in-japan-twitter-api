<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;

class RedirectController
{
    public function __construct()
    {
        
    }
    public function index(Request $request, Response $response)
    {
        return $response->withHeader('Location', 'http://www.slimframework.com')->withStatus(302);
    }
}