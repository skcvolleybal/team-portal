<?php


class GetBarcieRooster implements Interactor
{

    public function __construct(JoomlaGateway $joomlaGateway, BarcieGateway $barcieGateway)
    {
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
