<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use TeamPortal\Configuration;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\Team;
use TeamPortal\Gateways\JoomlaGateway;

class BarcieTest extends TestCase
{
    public function BarcieTest(
        JoomlaGateway $joomlaGateway,
        Configuration $configuration
    ) {
        $this->configuration = $configuration;
        $this->joomlaGateway = $joomlaGateway;
    }

    function test_When_BarlidId_is_Null_throw_Exception()
    {
        // arrange
        $configuration = new Configuration();
        $joomlaGateway = new JoomlaGateway();
        $thuisteam = new Team("SKC HS 2");
        $wedstrijd = new DwfWedstrijd("XXX", $thuisteam, $uitteam, 3, 0);




        // act
        $response = $interactor->Execute($data);

        // assert

    }
}
