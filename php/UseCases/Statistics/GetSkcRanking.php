<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\Gateways\BeschikbaarheidGateway;

class GetSkcRanking implements Interactor
{
    private $nevoboGateway;
    private $beschikbaarheidGateway;

    public function __construct(
        NevoboGateway $nevoboGateway, 
        BeschikbaarheidGateway $beschikbaarheidGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->beschikbaarheidGateway = $beschikbaarheidGateway;
    }

    public function Execute(object $data = null): array
    {
        $return_arrays = array();

        // Load the teams from JSON, with basic error handling
        $filePath = "skc-teams.json";
        if (!file_exists($filePath)) {
            throw new \Exception("JSON file not found: " . $filePath);
        }

        $string = file_get_contents($filePath);
        $teams = json_decode($string);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error decoding JSON: " . json_last_error_msg());
        }

        // Execute queries and store results
        $return_arrays['verenigings_standen'] = $this->nevoboGateway->GetVerenigingsStanden();
        $return_arrays['scheidsrechter_shifts'] = $this->beschikbaarheidGateway->GetScheidsrechterShifts();
        $return_arrays['teller_shifts'] = $this->beschikbaarheidGateway->GetTellerShifts();
        $return_arrays['bhv_shifts'] = $this->beschikbaarheidGateway->GetBHVShifts();
        $return_arrays['bar_personnel_shifts'] = $this->beschikbaarheidGateway->GetBarPersonnelShifts();

        return $return_arrays;
    }
}
