<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use TeamPortal\Entities\Bardag;
use TeamPortal\Gateways\BarcieGateway;
use TeamPortal\Tests\Gateways\JoomlaGateway;
use TeamPortal\UseCases\AddBarcieAanwezigheid;
use TeamPortal\UseCases\IBarcieGateway;
use TeamPortal\UseCases\IJoomlaGateway;

class BarcieTest extends TestCase
{
    function test_When_BarlidId_is_Null_throw_Exception()
    {
        // arrange        
        $barcieGateway = $this->getMockBuilder(IBarcieGateway::class)->setMockClassName(IBarcieGateway::class)->getMock();
        $joomlaGateway = $this->getMockBuilder(IJoomlaGateway::class)->getMock();

        $today = new DateTime();
        $barcieGateway
            ->method('GetBardag')
            ->willReturn(new Bardag(1, $today));

        $asd = $barcieGateway->GetBardag($today);

        $interactor = new AddBarcieAanwezigheid($barcieGateway, $joomlaGateway);

        $data = (object) [
            "barlidId" => 1,
            "date" => $today->format("Y-m-d"),
            "shift" => 1
        ];


        // act
        $response = $interactor->Execute($data);

        // assert

    }
}
