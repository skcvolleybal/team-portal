<?php

class GetCurrentUser implements Interactor
{
    public function __construct(JoomlaGateway $joomlaGateway)
    {
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        return $this->joomlaGateway->GetUser();
    }
}
