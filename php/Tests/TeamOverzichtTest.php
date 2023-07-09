<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Teamportal\Tests\GatewayMocks;
use TeamPortal\UseCases\GetTeamoverzicht;

class TeamOverzichtTest extends TestCase
{
    function test_Teamoverzicht_Happy_Flow()
    {
        // arrange
        $gatewayMocks = new GatewayMocks();
        $wordPressGateway = $gatewayMocks->GetWordPressGateway();
        $nevobogateway = $gatewayMocks->GetNevobogateway();

        $data = (object) [
            'teamnaam' => null
        ];
        $interactor = new GetTeamoverzicht($wordPressGateway, $nevobogateway);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Teamnaam is leeg");

        // act
        $response = $interactor->Execute($data);

        // assert
        $this->assertNull($response);
    }
}
