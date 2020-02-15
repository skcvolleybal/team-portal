<?php

namespace TeamPortal\Tests\Gateways;

use TeamPortal\Entities\Persoon;

class JoomlaGateway
{
    public function GetUser(?int $userId = null): ?Persoon
    {
        switch ($userId) {
            case null:
                return null;
            case 1:
                return new Persoon(1, "Test Persoon", "test.persoon@example.com");
            default:
                return null;
        }
    }
}
