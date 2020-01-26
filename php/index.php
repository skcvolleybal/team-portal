<?php

declare(strict_types=1);

setlocale(LC_ALL, 'nl_NL');

use DI\Container;

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use TeamPortal\RouteObjects;
use TeamPortal\UseCases;
use TeamPortal\Configuration;
use TeamPortal\Entities\AuthorizationRole;

require 'vendor/autoload.php';

$container = new Container();
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
    new RouteObjects\RouteGroup('/team-portal/api', [
        new RouteObjects\GetRoute('/mijn-overzicht', UseCases\MijnOverzicht::class, AuthorizationRole::USER),

        new RouteObjects\RouteGroup('/wedstrijd-overzicht', [
            new RouteObjects\GetRoute('', UseCases\GetWedstrijdOverzicht::class),
            new RouteObjects\PostRoute('/aanwezigheid', UseCases\UpdateAanwezigheid::class),
        ], AuthorizationRole::USER),

        new RouteObjects\RouteGroup('/fluiten', [
            new RouteObjects\GetRoute('', UseCases\GetFluitBeschikbaarheid::class),
            new RouteObjects\PostRoute('', UseCases\UpdateFluitBeschikbaarheid::class)
        ], AuthorizationRole::SCHEIDSRECHTER),

        new RouteObjects\RouteGroup('/barcie', [
            new RouteObjects\GetRoute('', UseCases\GetBarcieBeschikbaarheid::class),
            new RouteObjects\PostRoute('', UseCases\UpdateBarcieBeschikbaarheid::class)
        ], AuthorizationRole::BARCIE),

        new RouteObjects\RouteGroup('/barco', [
            new RouteObjects\PostRoute('/toggle-bhv', UseCases\ToggleBhv::class),
            new RouteObjects\GetRoute('/rooster', UseCases\GetBarcieRooster::class),
            new RouteObjects\GetRoute('/beschikbaarheden', UseCases\GetBarcieBeschikbaarheden::class),
            new RouteObjects\RouteGroup('/dienst', [
                new RouteObjects\PostRoute('', UseCases\AddBarcieAanwezigheid::class),
                new RouteObjects\DeleteRoute('', UseCases\DeleteBarcieAanwezigheid::class)
            ]),
            new RouteObjects\RouteGroup('/dag', [
                new RouteObjects\PostRoute('', UseCases\AddBardag::class),
                new RouteObjects\DeleteRoute('', UseCases\DeleteBardag::class)
            ])
        ], AuthorizationRole::TEAMCOORDINATOR),

        new RouteObjects\RouteGroup('/scheidsco', [
            new RouteObjects\GetRoute('/overzicht', UseCases\GetScheidscoOverzicht::class),
            new RouteObjects\RouteGroup('/zaalwacht', [
                new RouteObjects\PostRoute('', UseCases\UpdateZaalwacht::class),
                new RouteObjects\GetRoute('teams', UseCases\GetZaalwachtTeams::class)
            ]),
            new RouteObjects\RouteGroup('/scheidsrechters', [
                new RouteObjects\GetRoute('', UseCases\GetScheidsrechters::class),
                new RouteObjects\PostRoute('', UseCases\UpdateScheidsrechter::class)
            ]),
            new RouteObjects\RouteGroup('/tellers', [
                new RouteObjects\GetRoute('', UseCases\GetTelTeams::class),
                new RouteObjects\PostRoute('', UseCases\UpdateTellers::class)
            ])
        ], AuthorizationRole::TEAMCOORDINATOR),

        new RouteObjects\RouteGroup('/dwf', [
            new RouteObjects\GetRoute('/gespeelde-punten', UseCases\GetGespeeldePunten::class),
            new RouteObjects\GetRoute('/dwf-punten', UseCases\GetDwfPunten::class, AuthorizationRole::UNREGISTERED),
            new RouteObjects\GetRoute('/importeer-wedstrijden', UseCases\WedstrijdenImporteren::class, AuthorizationRole::UNREGISTERED),
        ], AuthorizationRole::USER),

        new RouteObjects\GetRoute('/calendar', UseCases\GetCalendar::class, AuthorizationRole::UNREGISTERED),

        new RouteObjects\RouteGroup('/tasks', [
            new RouteObjects\GetRoute('/sync-matches', UseCases\SynchronizeWedstrijden::class),
            new RouteObjects\GetRoute('/queue-weekly-emails', UseCases\QueueWeeklyEmails::class),
            new RouteObjects\GetRoute('/send-emails', UseCases\SendQueuedEmails::class),
            new RouteObjects\GetRoute('/daily-tasks', UseCases\DailyTasks::class)
        ], AuthorizationRole::UNREGISTERED),

        new RouteObjects\RouteGroup('/website', [
            new RouteObjects\GetRoute('/voorpagina-rooster', UseCases\GetVoorpaginaRooster::class),
            new RouteObjects\GetRoute('/teamoverzicht', UseCases\GetTeamoverzicht::class),
            new RouteObjects\GetRoute('/teamstanden', UseCases\GetTeamstanden::class),
        ], AuthorizationRole::UNREGISTERED),

        new RouteObjects\RouteGroup('/joomla', [
            new RouteObjects\GetRoute('/groepen', UseCases\GetGroups::class),
            new RouteObjects\GetRoute('/user', UseCases\GetCurrentUser::class),
            new RouteObjects\GetRoute('/users', UseCases\GetUsers::class, AuthorizationRole::WEBCIE),
            new RouteObjects\PostRoute('/inloggen', UseCases\Inloggen::class, AuthorizationRole::UNREGISTERED)
        ], AuthorizationRole::USER)
    ]);
$entryPoint->RegisterRoutes($app);

$app->run();
