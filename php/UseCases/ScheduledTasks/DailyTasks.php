<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\Database;
use TeamPortal\Gateways;

/**
 * Daily maintenance task. Currently keeps the locally stored match schedule in
 * sync with Nevobo; match times and locations change fairly often, so this is
 * run every day (and after each deploy).
 */
class DailyTasks implements Interactor
{
    public function __construct(
        Database $database,
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\TelFluitGateway $telFluitGateway
    ) {
        $this->database = $database;
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
    }

    public function Execute(object $data = null): array
    {
        $synchronizeWedstrijden = new SynchronizeWedstrijden(
            $this->database,
            $this->telFluitGateway,
            $this->nevoboGateway
        );

        return $synchronizeWedstrijden->Execute();
    }
}
