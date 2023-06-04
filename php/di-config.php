<?php
// config.php

use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\UseCases\IWordPressGateway;
use DI\Container;
use TeamPortal\Common\Database;
use TeamPortal\Gateways\BarcieGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\UseCases\IBarcieGateway;
use TeamPortal\UseCases\INevoboGateway;

return [
    IWordPressGateway::class => DI\factory(function () {
        return new WordPressGateway();
    }),
    IBarcieGateway::class => DI\factory(function (Container $container) {
        $database = $container->get(Database::class);
        return new BarcieGateway($database);
    }),
    INevoboGateway::class => DI\factory(function () {
        return new NevoboGateway();
    })
];
