<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

require 'vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

$container = ContainerFactory::Create();
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = new TeamPortalErrorHandler($app);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $methods = ['GET', 'POST', 'POST', 'DELETE', 'PUT', 'OPTIONS'];
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

    $response = $handler->handle($request);

    $configuration = $this->get(Configuration::class);
    return $response
        ->withHeader('Access-Control-Allow-Origin', $configuration->AccessControlAllowOrigin)
        ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
        ->withHeader('Access-Control-Allow-Headers', $requestHeaders)
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

$app->options('[/{path:.*}]', function (Request $request, Response $response, iterable $args) {
    $configuration = $this->get(Configuration::class);
    return $response
        ->withHeader('Access-Control-Allow-Origin', $configuration->AccessControlAllowOrigin)
        ->withHeader('Access-Control-Allow-Methods', 'POST,PUT');
});

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$entryPoint =
    new RouteGroup('/team-portal/api', [
        new RouteGroup('/joomla', [
            new GetRoute('/groepen', GetGroupsInteractor::class),
            new GetRoute('/user', GetCurrentUserInteractor::class),
            new GetRoute('/users', GetUsers::class),
            new PostRoute('/inloggen', InloggenInteractor::class)
        ]),
        new GetRoute('/mijn-overzicht', MijnOverzichtInteractor::class),
        new PostRoute('/aanwezigheid', UpdateAanwezigheid::class),
        new GetRoute('/wedstrijd-overzicht', GetWedstrijdOverzicht::class),
        new PostRoute('/wedstrijd-aanwezigheid', UpdateAanwezigheid::class),
        new RouteGroup('/fluiten', [
            new GetRoute('', GetFluitBeschikbaarheid::class),
            new PostRoute('', UpdateFluitBeschikbaarheid::class)
        ]),
        new RouteGroup('/barcie', [
            new GetRoute('', GetBarcieBeschikbaarheid::class),
            new PostRoute('', UpdateBarcieBeschikbaarheid::class),
            new PostRoute('/toggle-bhv', ToggleBhv::class),
            new GetRoute('/rooster', GetBarcieRooster::class),
            new GetRoute('/beschikbaarheden', GetBarcieBeschikbaarheden::class),
            new RouteGroup('/dienst', [
                new PostRoute('/add', AddBarcieAanwezigheid::class),
                new PostRoute('/delete', DeleteBarcieAanwezigheid::class)
            ]),
            new PostRoute('/barciedag/add', AddBarcieDag::class),
            new PostRoute('/barciedag/delete', DeleteBarcieDag::class),
        ]),
        new RouteGroup('/scheidsco', [
            new GetRoute('/overzicht', GetScheidscoOverzicht::class),
            new RouteGroup('/zaalwacht', [
                new PostRoute('', UpdateZaalwacht::class),
                new GetRoute('teams', GetZaalwachtTeams::class)
            ]),
            new RouteGroup('/scheidsrechters', [
                new GetRoute('', GetScheidsrechters::class),
                new PostRoute('', UpdateScheidsrechter::class)
            ]),
            new RouteGroup('/tellers', [
                new GetRoute('', GetTelTeams::class),
                new PostRoute('', UpdateTellers::class)
            ])
        ])
    ]);
$entryPoint->RegisterRoutes($app);

$app->run();
