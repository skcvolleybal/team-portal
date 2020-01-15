<?php

class GetGroupsInteractor
{

    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(): array
    {
        $result = [];
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            return $result;
        }


        if ($this->joomlaGateway->IsScheidsrechter($userId)) {
            $result[] = "scheidsrechter";
        }
        if ($this->joomlaGateway->IsBarcie($userId)) {
            $result[] = "barcie";
        }
        if ($this->joomlaGateway->IsTeamcoordinator($userId)) {
            $result[] = "teamcoordinator";
        }
        if ($this->joomlaGateway->IsWebcie($userId)) {
            $result[] = "webcie";
        }

        return $result;
    }
}
