<?php

use Slim\Factory\AppFactory;
use DI\Container;

require 'vendor/autoload.php';

$container = new Container();
$configuration = include('./../configuration.php');
$container->set("config", $configuration);
include "container.php";
AppFactory::setContainer($container);


$app = AppFactory::create();

$app->get('/team-portal/mijn-overzicht', \MijnOverzichtController::class);

$app->run();
