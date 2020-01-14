<?php

class PutRoute
{
    public function __construct($route, $interactor)
    {
        $this->route = $route;
        $this->interactor = $interactor;
    }
}
