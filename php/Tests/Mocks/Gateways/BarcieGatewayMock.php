<?php

namespace Teamportal\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use TeamPortal\Entities\Bardag;
use TeamPortal\Entities\Bardienst;
use TeamPortal\Entities\Persoon;
use TeamPortal\UseCases\IBarcieGateway;
use TeamPortal\UseCases\IJoomlaGateway;
use TeamPortal\UseCases\INevoboGateway;

class GatewayMocks extends TestCase
{

    function GetBarcieGateway(): IBarcieGateway
    {
        $today = new DateTime();

        /** @var \TeamPortal\UseCases\IBarcieGateway */
        $barcieGatewayMock = $this->createMock(IBarcieGateway::class);
        $barcieGatewayMock->method('GetBardag')->willReturn(new Bardag(1, $today));
        $barcieGatewayMock->method('GetBardienst')->willReturn(new Bardienst(
            new Bardag(1, new DateTime()),
            new Persoon(1, "Thomas", "thomas@ghpomasd.asd"),
            null,
            null
        ));

        return $barcieGatewayMock;
    }

    function GetJoomlaGateway(): IJoomlaGateway
    {
        /** @var \TeamPortal\UseCases\IJoomlaGateway */
        $joomlaGatewwayMock = $this->createMock(IJoomlaGateway::class);
        $newPerson = new Persoon(1, "Sjon", "sjons@sjons.clm");
        $joomlaGatewwayMock->method('GetUser')->willReturn($newPerson);

        return $joomlaGatewwayMock;
    }

    function GetNevoboGateway(): INevoboGateway
    {
        return $this->createMock(INevoboGateway::class);
    }
}
