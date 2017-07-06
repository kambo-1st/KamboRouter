<?php

namespace Kambo\Tests\Router\Request;

class Enviroment
{
    private $enviromentData = null;

    public function __construct($enviromentData = null)
    {
        $this->fromArray($enviromentData);
    }

    public function fromArray($enviromentData)
    {
        $this->enviromentData = $enviromentData;
    }

    public function getQueryString()
    {
        return $this->enviromentData['QUERY_STRING'];
    }

    public function getRequestMethod()
    {
        return $this->enviromentData['REQUEST_METHOD'];
    }

    public function getRequestUri()
    {
        return $this->enviromentData['REQUEST_URI'];
    }
}
