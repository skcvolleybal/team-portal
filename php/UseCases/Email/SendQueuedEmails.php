<?php

class SendQueuedEmails implements Interactor
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
