<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use PHPUnit\Framework\TestCase;
use TeamPortal\Common\Database;

class BarcieTest extends TestCase
{
    function test_When_BarlidId_is_Null_throw_Exception()
    {
        // arrange
        $database = $this->createMock(Database::class);
        
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
