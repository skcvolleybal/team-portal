<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class GetBarcieRooster implements Interactor
{

    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\BarcieGateway $barcieGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
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
