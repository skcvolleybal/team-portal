<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\JoomlaGateway;

class GetGroups implements Interactor
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null): array
    {
        $result = [];

        $user = $this->joomlaGateway->GetUser();
        if ($user === null) {
            return $result;
        }
        
        $dwfTeams = ["SKC HS 1", "SKC HS 2", "SKC DS 1", "SKC DS 2", "SKC DS 4"];
        if (($user->team && in_array($user->team->naam, $dwfTeams)) || $this->joomlaGateway->IsTeamcoordinator($user)) {
            $result[] = "statistieken";
        }

        if ($this->joomlaGateway->IsScheidsrechter($user)) {
            $result[] = "scheidsrechter";
        }
        if ($this->joomlaGateway->IsBarcie($user)) {
            $result[] = "barcie";
        }
        if ($this->joomlaGateway->IsTeamcoordinator($user)) {
            $result[] = "teamcoordinator";
        }
        if ($this->joomlaGateway->IsWebcie($user)) {
            $result[] = "webcie";
        }

        return $result;
    }
}
