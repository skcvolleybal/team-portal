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
        $result = [];

        $string = file_get_contents("skc-teams.json");
        $teams = json_decode($string);

        $nevoboGateway = new NevoboGateway();
        $nevoboGateway->GetVerenigingsStanden();
        


        return $result;



        // return $result;
    }
}
