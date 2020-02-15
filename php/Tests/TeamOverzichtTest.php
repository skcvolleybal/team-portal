<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use PHPUnit\Framework\TestCase;
use TeamPortal\UseCases\GetTeamoverzicht;

class TeamoverzichtTest extends TestCase
{
    function test_That_it_works()
    {
        // arrange
        $interactor = new GetTeamoverzicht();
        $data = (object)[

        ];
        
        $this->expectExceptionMessage("Teamnaam is leeg");
        // act
        $result = $interactor->Execute($data);

        // assert

    }
}
