<?php


class GetBarcieRooster implements IInteractor
{

    public function __construct(JoomlaGateway $joomlaGateway, BarcieGateway $barcieGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if (!$this->joomlaGateway->isBarcie($userId)) {
            throw new UnexpectedValueException("Je bent geen barcie");
        }

        $rooster = [];
        $dagen = $this->barcieGateway->GetBarciedagen();
        foreach ($dagen as $dag) {
            $rooster[] = new TeamportalBarciedag($dag);
        }

        return $rooster;
    }
}
