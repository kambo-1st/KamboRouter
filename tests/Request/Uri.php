<?php

namespace Kambo\Tests\Router\Request;

class Uri
{
    private $enviroment;

    public function __construct($enviroment = null)
    {
        $this->enviroment = $enviroment;
    }

    public function getPath()
    {
        return $this->enviroment->getRequestUri();
    }

    public function getQuery()
    {
        return $this->enviroment->getQueryString();
    }
}
