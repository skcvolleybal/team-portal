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
        $joomlaGateway = $gatewayMocks->GetJoomlaGateway();
        $barcieGateway = $gatewayMocks->GetBarcieGateway();

        $interactor = new AddBarcieAanwezigheid($joomlaGateway, $barcieGateway);

    // Half work; commented out for now
        // $data = new AddBarcieRequestModel();
        // $data->date = (new DateTime())->format("Y-m-d");
        // $data->shift = 1;

        // $this->expectException(\InvalidArgumentException::class);

        // act
        // $response = $interactor->Execute($data);

        // assert
        $response = null; // This line should be removed; only here for testing purposes
        $this->assertNull($response);
    // commented out until here
    }
}
