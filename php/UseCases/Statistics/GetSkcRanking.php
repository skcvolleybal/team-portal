<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\NevoboGateway;


class GetSkcRanking implements Interactor
{
    
    public function __construct(NevoboGateway $nevoboGateway)
    {
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null): array
    {
        $result = [];

        // $user = $this->wordPressGateway->GetUser();
        // if ($user === null) {
        //     return $result;
        // }

        $result['a'] = 'b'; 
        

        return $result;



        // return $result;
    }
}
