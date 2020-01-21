<?php

declare(strict_types=1);

setlocale(LC_ALL, 'nl_NL');

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

require 'vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

$container = ContainerFactory::Create();
AppFactory::setContainer($container);
$app = AppFactory::create();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = new TeamPortalErrorHandler($app);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $methods = ['GET', 'POST', 'DELETE', 'OPTIONS'];
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

    $response = $handler->handle($request);

    $configuration = $this->get(Configuration::class);
    return $response
        ->withHeader('Access-Control-Allow-Origin', $configuration->AccessControlAllowOrigin)
        ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
        ->withHeader('Access-Control-Allow-Headers', $requestHeaders)
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

$app->options('[/{path:.*}]', function (Request $request, Response $response, array $args) {
    $configuration = $this->get(Configuration::class);
    return $response
        ->withHeader('Access-Control-Allow-Origin', $configuration->AccessControlAllowOrigin)
        ->withHeader('Access-Control-Allow-Methods', 'POST,PUT');
});

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$entryPoint =
    new RouteGroup('/team-portal/api', [
        new GetRoute('/mijn-overzicht', MijnOverzicht::class, AuthorizationRole::USER),

        new RouteGroup('/wedstrijd-overzicht', [
            new GetRoute('', GetWedstrijdOverzicht::class),
            new PostRoute('/aanwezigheid', UpdateAanwezigheid::class),
        ], AuthorizationRole::USER),

        new RouteGroup('/fluiten', [
            new GetRoute('', GetFluitBeschikbaarheid::class),
            new PostRoute('', UpdateFluitBeschikbaarheid::class)
        ], AuthorizationRole::SCHEIDSRECHTER),

        new RouteGroup('/barcie', [
            new GetRoute('', GetBarcieBeschikbaarheid::class),
            new PostRoute('', UpdateBarcieBeschikbaarheid::class)
        ], AuthorizationRole::BARCIE),

        new RouteGroup('/barco', [
            new PostRoute('/toggle-bhv', ToggleBhv::class),
            new GetRoute('/rooster', GetBarcieRooster::class),
            new GetRoute('/beschikbaarheden', GetBarcieBeschikbaarheden::class),
            new RouteGroup('/dienst', [
                new PostRoute('', AddBarcieAanwezigheid::class),
                new DeleteRoute('', DeleteBarcieAanwezigheid::class)
            ]),
            new RouteGroup('/dag', [
                new PostRoute('', AddBardag::class),
                new DeleteRoute('', DeleteBardag::class)
            ])
        ], AuthorizationRole::TEAMCOORDINATOR),

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
        ], AuthorizationRole::TEAMCOORDINATOR),

        new RouteGroup('/dwf', [
            new GetRoute('/gespeelde-punten', GetGespeeldePunten::class),
            new GetRoute('/importeer-wedstrijden', WedstrijdenImporteren::class, AuthorizationRole::UNREGISTERED),
        ], AuthorizationRole::USER),

        new RouteGroup('/taken', [
            new GetRoute('/synchroniseer-wedstrijden', SynchronizeWedstrijden::class),
            new GetRoute('/queue-weekly-emails', QueueWeeklyEmails::class, AuthorizationRole::UNREGISTERED),
            new GetRoute('/send-emails', SendQueuedEmails::class, AuthorizationRole::UNREGISTERED)
        ]),

        new RouteGroup('/joomla', [
            new GetRoute('/groepen', GetGroups::class),
            new GetRoute('/user', GetCurrentUser::class),
            new GetRoute('/users', GetUsers::class, AuthorizationRole::WEBCIE),
            new PostRoute('/inloggen', Inloggen::class, AuthorizationRole::UNREGISTERED)
        ], AuthorizationRole::USER),
    ]);
$entryPoint->RegisterRoutes($app);

$app->run();
