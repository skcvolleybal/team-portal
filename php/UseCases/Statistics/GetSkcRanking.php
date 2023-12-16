<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\NevoboGateway;


class GetSkcRanking implements Interactor
{

    
    // public function __construct()
    // {
    // }

    public function Execute(object $data = null): array
    {
        $string = file_get_contents("skc-teams.json");
        $teams = json_decode($string);

        $nevoboGateway = new NevoboGateway();
        $standen = $nevoboGateway->GetVerenigingsStanden();
        
        return $standen;

    }
}
