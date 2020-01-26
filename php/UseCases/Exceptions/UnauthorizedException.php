<?php

class UnauthorizedException extends Exception
{
    public function __construct($message = null)
    {
        parent::__construct($message ?? "Je bent niet ingelogd", 401, null);
    }
}
