<?php

class UpdateFluitBeschikbaarheid implements IInteractorWithData
{
    public function __construct(
        FluitBeschikbaarheidGateway $fluitBeschikbaarheidGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->fluitBeschikbaarheidGateway = $fluitBeschikbaarheidGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data)
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

        $user = $this->joomlaGateway->GetUser($userId);
        $beschikbaarheid = $this->fluitBeschikbaarheidGateway->GetFluitBeschikbaarheid($userId, $date) ??
            new Beschikbaarheid(
                null,
                new Persoon($user->id, $user->naam),
                $date,
                $isBeschikbaar
            );
        $beschikbaarheid->isBeschikbaar = $isBeschikbaar;
        if ($beschikbaarheid->id === null) {
            if ($beschikbaarheid->isBeschikbaar !== null) {
                $this->fluitBeschikbaarheidGateway->Insert($beschikbaarheid);
            }
        } else {
            if ($beschikbaarheid->isBeschikbaar === null) {
                $this->fluitBeschikbaarheidGateway->Delete($beschikbaarheid);
            } else {
                $this->fluitBeschikbaarheidGateway->Update($beschikbaarheid);
            }
        }
    }
}
