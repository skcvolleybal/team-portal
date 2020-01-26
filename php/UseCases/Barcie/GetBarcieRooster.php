<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class GetBarcieRooster implements Interactor
{

    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\BarcieGateway $barcieGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data = null)
    {
        $rooster = [];
        $dagen = $this->barcieGateway->GetBardagen();
        foreach ($dagen as $dag) {
            $rooster[] = new BardagModel($dag);
        }

        return $rooster;
    }
}
