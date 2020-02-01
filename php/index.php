<?php

declare(strict_types=1);

setlocale(LC_ALL, 'nl_NL');

use DI\Container;

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use TeamPortal\UseCases;
use TeamPortal\Configuration;
use TeamPortal\Entities\AuthorizationRole;
use TeamPortal\RouteObjects\DeleteRoute;
use TeamPortal\RouteObjects\GetRoute;
use TeamPortal\RouteObjects\PostRoute;
use TeamPortal\RouteObjects\RouteGroup;

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
    new RouteGroup('/team-portal/api', [
        new GetRoute('/mijn-overzicht', UseCases\MijnOverzicht::class, AuthorizationRole::USER),

        new RouteGroup('/wedstrijd-overzicht', [
            new GetRoute('', UseCases\GetWedstrijdOverzicht::class),
            new PostRoute('/aanwezigheid', UseCases\UpdateAanwezigheid::class),
        ], AuthorizationRole::USER),

        new RouteGroup('/fluiten', [
            new GetRoute('', UseCases\GetFluitBeschikbaarheid::class),
            new PostRoute('', UseCases\UpdateFluitBeschikbaarheid::class)
        ], AuthorizationRole::SCHEIDSRECHTER),

        new RouteGroup('/barcie', [
            new GetRoute('', UseCases\GetBarcieBeschikbaarheid::class),
            new PostRoute('', UseCases\UpdateBarcieBeschikbaarheid::class)
        ], AuthorizationRole::BARCIE),

        new RouteGroup('/barco', [
            new PostRoute('/toggle-bhv', UseCases\ToggleBhv::class),
            new GetRoute('/rooster', UseCases\GetBarcieRooster::class),
            new GetRoute('/beschikbaarheden', UseCases\GetBarcieBeschikbaarheden::class),
            new RouteGroup('/dienst', [
                new PostRoute('', UseCases\AddBarcieAanwezigheid::class),
                new DeleteRoute('', UseCases\DeleteBarcieAanwezigheid::class)
            ]),
            new RouteGroup('/dag', [
                new PostRoute('', UseCases\AddBardag::class),
                new DeleteRoute('', UseCases\DeleteBardag::class)
            ])
        ], AuthorizationRole::TEAMCOORDINATOR),

        new RouteGroup('/scheidsco', [
            new GetRoute('/overzicht', UseCases\GetScheidscoOverzicht::class),
            new RouteGroup('/zaalwacht', [
                new PostRoute('', UseCases\UpdateZaalwacht::class),
                new GetRoute('teams', UseCases\GetZaalwachtTeams::class)
            ]),
            new RouteGroup('/scheidsrechters', [
                new GetRoute('', UseCases\GetScheidsrechters::class),
                new PostRoute('', UseCases\UpdateScheidsrechter::class)
            ]),
            new RouteGroup('/tellers', [
                new GetRoute('', UseCases\GetTelTeams::class),
                new PostRoute('', UseCases\UpdateTellers::class)
            ])
        ], AuthorizationRole::TEAMCOORDINATOR),

        new RouteGroup('/dwf', [
            new GetRoute('/gespeelde-punten', UseCases\GetGespeeldePunten::class),
            new GetRoute('/dwf-punten', UseCases\GetDwfPunten::class, AuthorizationRole::UNREGISTERED),
            new GetRoute('/importeer-wedstrijden', UseCases\WedstrijdenImporteren::class, AuthorizationRole::UNREGISTERED),
        ], AuthorizationRole::USER),

        new RouteGroup('/statistieken', [
            new GetRoute('/wedstrijden', UseCases\GetEigenDwfWedstrijden::class)
        ], AuthorizationRole::USER),

        new GetRoute('/calendar', UseCases\GetCalendar::class, AuthorizationRole::UNREGISTERED),

        new RouteGroup('/tasks', [
            new GetRoute('/sync-matches', UseCases\SynchronizeWedstrijden::class),
            new GetRoute('/queue-weekly-emails', UseCases\QueueWeeklyEmails::class),
            new GetRoute('/send-emails', UseCases\SendQueuedEmails::class),
            new GetRoute('/daily-tasks', UseCases\DailyTasks::class)
        ], AuthorizationRole::UNREGISTERED),

        new RouteGroup('/website', [
            new GetRoute('/voorpagina-rooster', UseCases\GetVoorpaginaRooster::class),
            new GetRoute('/teamoverzicht', UseCases\GetTeamoverzicht::class),
            new GetRoute('/teamstanden', UseCases\GetTeamstanden::class),
        ], AuthorizationRole::UNREGISTERED),

        new RouteGroup('/joomla', [
            new GetRoute('/groepen', UseCases\GetGroups::class),
            new GetRoute('/user', UseCases\GetCurrentUser::class),
            new GetRoute('/users', UseCases\GetUsers::class, AuthorizationRole::WEBCIE),
            new PostRoute('/inloggen', UseCases\Inloggen::class, AuthorizationRole::UNREGISTERED)
        ], AuthorizationRole::USER)
    ]);
$entryPoint->RegisterRoutes($app);

$app->run();
