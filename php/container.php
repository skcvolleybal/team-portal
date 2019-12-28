<?php

use Psr\Container\ContainerInterface;

$container->set(\MijnOverzichtController::class, function (ContainerInterface $container) {
    $database = $container->get(\Database::class);
    $config = $container->get('config');
    $joomlaGateway = new JoomlaGateway($config, $database);
    $nevoboGateway = new NevoboGateway();
    $telFluitGateway = new TelFluitGateway($database);
    $zaalwachtGateway = new ZaalwachtGateway($database);
    $barcieGateway = new BarcieGateway($database);
    return new MijnOverzichtController($joomlaGateway,  $nevoboGateway, $telFluitGateway, $zaalwachtGateway, $barcieGateway);
});

$container->set(\Database::class, function (ContainerInterface $container) {
    $config = $container->get('config');
    return new \Database($config);
});
