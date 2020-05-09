<?php
// config.php

use TeamPortal\Configuration;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\UseCases\IJoomlaGateway;
use DI\Container;
use TeamPortal\Common\Database;
use TeamPortal\Gateways\BarcieGateway;
use TeamPortal\UseCases\IBarcieGateway;

return [
    IJoomlaGateway::class => DI\factory(function (Container $container) {
        $configuration = $container->get(Configuration::class);
        $database = $container->get(Database::class);
        return new JoomlaGateway($configuration, $database);
    }),
    IBarcieGateway::class => DI\factory(function (Container $container) {
        $database = $container->get(Database::class);
        return new BarcieGateway($database);
    })
];
