<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\EmailGateway;

class SendQueuedEmails implements Interactor
{
    public function __construct(EmailGateway $emailGateway)
    {
        $this->emailGateway = $emailGateway;
    }

    public function Execute(object $data = null)
    {
        $this->emailGateway->SendQueuedEmails();
    }
}
