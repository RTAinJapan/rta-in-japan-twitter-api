<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Constants\ApiConstant;
use Psr\Http\Message\ResponseInterface as Response;

class ApiController
{
    protected function asJson(Response $response, array $data, int $status = 200): Response
    {
        $payload = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /**
     * エラー時のレスポンスを返却
     * @param ResponseInterface $response レスポンス
     * @param string $message エラーメッセージ
     * @param int $code エラーコード
     * @param int $status HTTPステータスコード
     * @return ResponseInterface $response
     */
    protected function errorResponse(
        Response $response,
        string $message,
        int $code = ApiConstant::API_RESPONSE_CODE_ERROR,
        int $status = ApiConstant::HTTP_STATUS_CODE_ERROR
    ): Response
    {
        return $this->asJson($response, [
            'code' => $code,
            'error' => [
                'message' => $message
            ]
        ], $status);
    }
}