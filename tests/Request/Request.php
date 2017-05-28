<?php

namespace Kambo\Tests\Router\Request;

class Request
{
    private $serverVariables;
    private $uri;
    private $enviroment;
    private $queryParams;

    public function __construct($enviroment = null)
    {
        $this->enviroment = $enviroment;
        $this->uri = (new Uri($this->enviroment));
    }

    public function createFromServerVariables($serverVariables)
    {
        $this->serverVariables = $serverVariables;
    }

    public function getMethod()
    {
        return $this->enviroment->getRequestMethod();
    }

    public function getQueryParams()
    {
        if ($this->queryParams) {
            return $this->queryParams;
        }

        parse_str($this->uri->getQuery(), $this->queryParams);

        return $this->queryParams;
    }

    public function getUri()
    {
        return $this->uri;
    }
}
