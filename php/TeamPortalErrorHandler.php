<?php

use Slim\Interfaces\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TeamPortalErrorHandler implements ErrorHandlerInterface
{
    public function  __construct($app)
    {
        $this->app = $app;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $statusCode = $exception->getCode() == 0 ? 500 : $exception->getCode();
        $message = $exception->getMessage() ?? 'Een dikke error...';

        $error = [
            'message' => $message,
            'statuscode' => $statusCode
        ];

        $payload = json_encode($error, JSON_PRETTY_PRINT);

        $response = $this->app
            ->getResponseFactory()
            ->createResponse()
            ->withStatus($statusCode);
        $response->getBody()
            ->write($payload);

        return $response;
    }
}
