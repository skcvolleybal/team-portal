<?php


class GetBarcieRooster implements Interactor
{

    public function __construct(JoomlaGateway $joomlaGateway, BarcieGateway $barcieGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute()
    {
        $rooster = [];
        $dagen = $this->barcieGateway->GetBardagen();
        foreach ($dagen as $dag) {
            $rooster[] = new TeamportalBardag($dag);
        }

        return $rooster;
    }
}
