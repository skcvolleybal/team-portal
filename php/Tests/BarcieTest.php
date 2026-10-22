<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Teamportal\Tests\GatewayMocks;
use TeamPortal\UseCases\AddBarcieAanwezigheid;

class BarcieTest extends TestCase
{
    /** @test */
    public function TestWhenBarlidIdIsNullThrowException()
    {
        // Arrange
        $gatewayMocks = new GatewayMocks();
        $wordPressGateway = $gatewayMocks->GetWordPressGateway();
        $barcieGateway = $gatewayMocks->GetBarcieGateway();

        $interactor = new AddBarcieAanwezigheid(
            $wordPressGateway,
            $barcieGateway,
        );

        $data = new \stdClass();
        $data->barlidId = null;
        $data->date = new DateTime()->format("Y-m-d");
        $data->shift = 1;

        $this->expectException(\InvalidArgumentException::class);

        // Act
        $interactor->Execute($data);
    }

    /** @test */
    public function TestWhenDateIsNullThrowException()
    {
        // Arrange
        $gatewayMocks = new GatewayMocks();
        $wordPressGateway = $gatewayMocks->GetWordPressGateway();
        $barcieGateway = $gatewayMocks->GetBarcieGateway();

        $interactor = new AddBarcieAanwezigheid(
            $wordPressGateway,
            $barcieGateway,
        );

        $data = new \stdClass();
        $data->barlidId = 1;
        $data->date = null;
        $data->shift = 1;

        $this->expectException(\InvalidArgumentException::class);

        // Act
        $interactor->Execute($data);
    }

    /** @test */
    public function TestWhenShiftIsNullThrowException()
    {
        // Arrange
        $gatewayMocks = new GatewayMocks();
        $wordPressGateway = $gatewayMocks->GetWordPressGateway();
        $barcieGateway = $gatewayMocks->GetBarcieGateway();

        $interactor = new AddBarcieAanwezigheid(
            $wordPressGateway,
            $barcieGateway,
        );

        $data = new \stdClass();
        $data->barlidId = 1;
        $data->date = new DateTime()->format("Y-m-d");
        $data->shift = null;

        $this->expectException(\InvalidArgumentException::class);

        // Act
        $interactor->Execute($data);
    }

    /** @test */
    public function TestWhenEverythingIsFilledTheDienstIsStored()
    {
        // Arrange
        $gatewayMocks = new GatewayMocks();
        $wordPressGateway = $gatewayMocks->GetWordPressGateway();
        $barcieGateway = $gatewayMocks->GetBarcieGateway();

        $barcieGateway->expects($this->once())->method("InsertBardienst");

        $interactor = new AddBarcieAanwezigheid(
            $wordPressGateway,
            $barcieGateway,
        );

        $data = new \stdClass();
        $data->barlidId = 1;
        $data->date = new DateTime()->format("Y-m-d");
        $data->shift = 1;

        // Act
        $interactor->Execute($data);
    }
}
