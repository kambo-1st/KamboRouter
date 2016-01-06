<?php

class Request
{
    private $_serverVariables;
    private $_uri;
    private $_enviroment;
    private $_queryParams;

    function __construct($enviroment = null) {
        $this->_enviroment = $enviroment;
        $this->_uri = (new Uri($this->_enviroment));
    }

    public function createFromServerVariables($serverVariables) {
        $this->_serverVariables = $serverVariables;
    }

    public function getMethod() {
        return $this->_enviroment->getRequestMethod();
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams() {
        if ($this->_queryParams) {
            return $this->_queryParams;
        }

        parse_str($this->_uri->getQuery(), $this->_queryParams);

        return $this->_queryParams;
    }

    public function getUri() {
        return $this->_uri;  
    }    
}

class Uri
{
    private $_enviroment;

    function __construct($enviroment = null) {
        $this->_enviroment = $enviroment;
    }

    public function getPath() {
        return $this->_enviroment->getRequestUri();    
    }

    public function getQuery() {
        return $this->_enviroment->getQueryString();    
    }
}



class Enviroment
{
    private $_enviromentData = null;

    function __construct($enviromentData = null) {
        $this->fromArray($enviromentData);
    }    

    public function fromArray($enviromentData) {
        $this->_enviromentData = $enviromentData;    
    }

    public function getQueryString() {
        return $this->_enviromentData['QUERY_STRING'];
    }

    public function getRequestMethod() {
        return $this->_enviromentData['REQUEST_METHOD'];
    }

    public function getRequestUri() {
        return $this->_enviromentData['REQUEST_URI'];
    }      
}

class EnviromentFactory 
{
    static function getEnviroment($provider) {
        return (new $provider)->get();
    }
}

interface EnviromentProvider 
{
    public function get();
}

class ServerProvider implements EnviromentProvider
{
    public function get() {
        return new Enviroment($_SERVER);
    }
}