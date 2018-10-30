<?php
include 'IInteractor.php';
include 'DwfGateway.php';

class WedstrijdenImporteren implements IInteractor
{
    public function __construct($database)
    {
        $this->dwfGateway = new DwfGateway();
    }

    public function Execute()
    {

        // $gespeeldeWedstrijden = $this->dwfGateway->GetGespeeldeWedstrijden(1);
        // foreach ($gespeeldeWedstrijden as $wedstrijd) {
        //     $wedstrijdVerloop = $this->dwfGateway->GetWedstrijdVerloop($wedstrijd['id']);
        //     echo json_encode($wedstrijdVerloop, JSON_PRETTY_PRINT);
        //     echo "<br />";
        //     echo "<br />";
        //     echo "<br />";
        //     echo "<br />";
        // }
        // exit;

        $this->dwfGateway->Login();
    }
}
