<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use PHPUnit\Framework\TestCase;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\UseCases\GetTeamoverzicht;
use TeamPortal\UseCases\IJoomlaGateway;

class TeamoverzichtTest extends TestCase
{
    function test_That_it_works()
    {
        // arrange
        $nevobogateway = $this->createMock(NevoboGateway::class);
        $joomlaGateway = $this->createMock(IJoomlaGateway::class);

        $interactor = new GetTeamoverzicht($joomlaGateway, $nevobogateway);
        $data = (object) [
            'teamnaam' => null
        ];

        $this->expectExceptionMessage("Teamnaam is leeg");
        // act
        $result = $interactor->Execute($data);

        // assert

    }
}
