<?php

declare(strict_types=1);

setlocale(LC_ALL, 'nl_NL');


use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use TeamPortal\UseCases;
use TeamPortal\Entities\AuthorizationRole;
use TeamPortal\RouteObjects\DeleteRoute;
use TeamPortal\RouteObjects\GetRoute;
use TeamPortal\RouteObjects\PostRoute;
use TeamPortal\RouteObjects\RouteGroup;
use DI\ContainerBuilder;

require 'vendor/autoload.php';

// Load WordPress


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$wordpressPath = $_ENV['WORDPRESS_PATH'];
require_once $wordpressPath . '/wp-load.php';

$containerBuilder  = new ContainerBuilder();
$containerBuilder->addDefinitions('di-config.php');
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = new TeamPortalErrorHandler($app);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->add(function (Request $request, RequestHandlerInterface $handler): Response {
    $methods = ['GET', 'POST', 'DELETE', 'OPTIONS'];
    $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV['ACCESSCONTROLALLOWORIGIN'])
        ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
        ->withHeader('Access-Control-Allow-Headers', $requestHeaders)
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

$app->options('[/{path:.*}]', function (Request $request, Response $response, array $args) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV['ACCESSCONTROLALLOWORIGIN'])
        ->withHeader('Access-Control-Allow-Methods', 'POST,PUT');
});

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

// If we're deploying to SKC test server, the request URL will contain "/test/public_html". 
// So we need to prepend /test/public_html to every route so that Slim framework processes the Route. 
$baseRoute = '/team-portal/api';
$testUrl = '/test/public_html';
if (str_contains($_SERVER['REQUEST_URI'], $testUrl)) {
    $baseRoute = $testUrl . $baseRoute;
}


$entryPoint =
    new RouteGroup($baseRoute, [
          
        new GetRoute('/mijn-overzicht', UseCases\MijnOverzicht::class, AuthorizationRole::USER),

        new RouteGroup('/wedstrijd-overzicht', [
            new GetRoute('', UseCases\GetWedstrijdOverzicht::class),
            new PostRoute('/aanwezigheid', UseCases\UpdateAanwezigheid::class),
        ], AuthorizationRole::USER),

        new RouteGroup('/week-overzicht', [
            new GetRoute('', UseCases\GetWeekOverzicht::class),
        ], AuthorizationRole::TEAMCOORDINATOR),


        new RouteGroup('/fluiten', [
            new GetRoute('', UseCases\GetBeschikbaarheid::class),
            new PostRoute('', UseCases\UpdateBeschikbaarheid::class)
        ], AuthorizationRole::USER),

        new RouteGroup('/barcie-beschikbaarheid', [
            new GetRoute('', UseCases\GetBarcieBeschikbaarheid::class),
            new PostRoute('', UseCases\UpdateBarcieBeschikbaarheid::class)
        ], AuthorizationRole::BARCIE),

        new RouteGroup('/barcie', [
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

        new RouteGroup('/teamtakenco', [
            new GetRoute('/overzicht', UseCases\GetTeamtakencoOverzicht::class),
            new RouteGroup('/zaalwacht', [
                new PostRoute('', UseCases\UpdateZaalwacht::class),
                new GetRoute('teams', UseCases\GetZaalwachtTeams::class)
            ]),
            new RouteGroup('/scheidsrechters', [
                new GetRoute('', UseCases\GetScheidsrechters::class),
                new PostRoute('', UseCases\UpdateScheidsrechter::class)
            ]),
            new RouteGroup('/tellers', [
                new GetRoute('', UseCases\GetTellers::class),
                new PostRoute('', UseCases\UpdateTellers::class)
            ])
        ], AuthorizationRole::TEAMCOORDINATOR),

        new GetRoute('/calendar', UseCases\GetCalendar::class, AuthorizationRole::UNREGISTERED),
        

        new RouteGroup('/tasks', [
            new GetRoute('/sync-matches', UseCases\SynchronizeWedstrijden::class),
            new GetRoute('/queue-weekly-emails', UseCases\QueueWeeklyEmails::class),
            new GetRoute('/send-emails', UseCases\SendQueuedEmails::class),
            new GetRoute('/daily-tasks', UseCases\DailyTasks::class)
        ], AuthorizationRole::UNREGISTERED),


        new RouteGroup('/wordpress', [
            new GetRoute('/groepen', UseCases\GetGroups::class),
            new GetRoute('/user', UseCases\GetCurrentUser::class),
            new GetRoute('/users', UseCases\GetUsers::class, AuthorizationRole::WEBCIE),
            new PostRoute('/inloggen', UseCases\Inloggen::class, AuthorizationRole::UNREGISTERED)
        ], AuthorizationRole::USER),
        
        new RouteGroup('/statistics', [
            new GetRoute('/getskcranking', UseCases\GetSkcRanking::class)
        ], AuthorizationRole::USER),


    ]);
$entryPoint->RegisterRoutes($app);

$app->run();
