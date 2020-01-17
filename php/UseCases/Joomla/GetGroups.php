<?php

class GetGroups
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(): array
    {
        $result = [];
        
        $user = $this->joomlaGateway->GetUser();
        if ($user === null) {
            return $result;
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
