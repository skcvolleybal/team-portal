<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class BarcieTest extends TestCase
{
    function test_When_BarlidId_is_Null_throw_Exception()
    {
        // arrange
        $database = $this->createMock(Database::class);
        $configuration = include(__DIR__ . "/../../configuration.php");
        $interactor = new AddBarcieAanwezigheid($configuration, $database);
        $data = (object) [
            "barlidId" => 1,
            "date" => "2019-01-10",
            "shift" => 1
        ];

        // act
        $interactor->Execute($data);

        // assert

    }
}
