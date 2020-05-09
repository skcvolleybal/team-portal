<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use TeamPortal\Entities\Bardag;
use TeamPortal\Entities\Bardienst;
use TeamPortal\Entities\Persoon;
use TeamPortal\UseCases\AddBarcieAanwezigheid;
use TeamPortal\UseCases\IBarcieGateway;
use TeamPortal\UseCases\IJoomlaGateway;

class BarcieTest extends TestCase
{
    function test_When_BarlidId_is_Null_throw_Exception()
    {
        // arrange     
        $today = new DateTime();

        $barcieGateway = $this->createMock(IBarcieGateway::class);
        $barcieGateway->method('GetBardag')->willReturn(new Bardag(1, $today));
        $barcieGateway->method('GetBardienst')->willReturn(new Bardienst(
            new Bardag(1, new DateTime()),
            new Persoon(1, "Thomas", "thomas@ghpomasd.asd"),
            null, null
        ));

        $joomlaGateway = $this->createMock(IJoomlaGateway::class);
        $joomlaGateway->method('GetUser')->willReturn(new Persoon(1, "Sjon", "sjons@sjons.clm"));

        $asd = $barcieGateway->GetBardag($today);

        $interactor = new AddBarcieAanwezigheid($joomlaGateway, $barcieGateway);

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
