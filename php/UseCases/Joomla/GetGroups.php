<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\WordPressGateway;

class GetGroups implements Interactor
{
    public function __construct(WordPressGateway $wordPressGateway)
    {
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null): array
    {
        $result = [];

        $user = $this->wordPressGateway->GetUser();
        if ($user === null) {
            return $result;
        }

        if ($this->wordPressGateway->IsScheidsrechter($user)) {
            $result[] = "scheidsrechter";
        }

        if ($this->wordPressGateway->IsBarcie($user)) {
            $result[] = "barcie";
        }

        if ($this->wordPressGateway->IsTeamcoordinator($user)) {
            $result[] = "teamcoordinator";
        }

        if ($this->wordPressGateway->IsWebcie($user)) {
            $result[] = "webcie";
        }

        return $result;
    }
}
