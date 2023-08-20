<?php

namespace TeamPortal\UseCases;

use DateTime;
use InvalidArgumentException;
use TeamPortal\Gateways;

class UpdateBeschikbaarheid implements Interactor
{
    public function __construct(
        Gateways\BeschikbaarheidGateway $beschikbaarheidGateway,
        Gateways\WordPressGateway $wordPressGateway
    ) {
        $this->beschikbaarheidGateway = $beschikbaarheidGateway;
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null)
    {
        $datum = $data->datum;
        $tijd = $data->tijd;
        $isBeschikbaar = $data->isBeschikbaar;

        $date = DateTime::createFromFormat('Y-m-d H:i', $datum . " " . $tijd);
        if ($date === false) {
            throw new InvalidArgumentException("Tijd niet goed: $datum $tijd");
        }

        if (!in_array($isBeschikbaar, [true, false, null])) {
            throw new InvalidArgumentException("Unknown beschikbaarheid: $isBeschikbaar");
        }

        $user = $this->wordPressGateway->GetUser();
        $beschikbaarheid = $this->beschikbaarheidGateway->GetBeschikbaarheid($user, $date);
        $beschikbaarheid->isBeschikbaar = $isBeschikbaar;
        if ($beschikbaarheid->id === null) {
            if ($beschikbaarheid->isBeschikbaar !== null) {
                $this->beschikbaarheidGateway->Insert($beschikbaarheid);
            }
        } else {
            if ($beschikbaarheid->isBeschikbaar === null) {
                $this->beschikbaarheidGateway->Delete($beschikbaarheid);
            } else {
                $this->beschikbaarheidGateway->Update($beschikbaarheid);
            }
        }
    }
}
