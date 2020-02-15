<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use TeamPortal\Entities\Bardag;
use TeamPortal\Gateways\BarcieGateway;
use TeamPortal\Tests\Gateways\JoomlaGateway;
use TeamPortal\UseCases\AddBarcieAanwezigheid;

class BarcieTest extends TestCase
{
    function test_When_BarlidId_is_Null_throw_Exception()
    {
        // arrange
        $barcieGateway = $this->createMock(BarcieGateway::class);
        $joomlaGateway = $this->createMock(JoomlaGateway::class);

        $today = new DateTime();
        $barcieGateway->method('GetBardag')
            ->willReturn(new Bardag(1, $today));

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
