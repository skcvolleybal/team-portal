<?php

class SendQueuedEmails implements IInteractor
{
    public function __construct($database)
    {
        $this->emailGateway = new EmailGateway($database);
    }

    public function Execute()
    {
        $this->emailGateway->SendQueuedEmails();
    }
}
