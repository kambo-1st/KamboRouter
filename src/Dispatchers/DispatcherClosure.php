<?php
namespace Kambo\Router\Dispatchers;

use Kambo\Router\Interfaces\DispatcherInterface;

/**
 * Dispatcher with closure support
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Dispatchers
 * @package  Router
 * 
 */
class DispatcherClosure implements DispatcherInterface
{
    /**
     * Not found handler will be called if nothing has been found.
     *
     * @var mixed
     */
    private $_notFoundHandler;

    /**
     * Dispatch found route with given parameters
     * 
     * @param mixed $route      found route
     * @param mixed $parameters parameters for route
     *
     * @return mixed
     */
    public function dispatchRoute(array $route, array $parameters) {
        $handler = $route['handler'];
        if ($this->_isClosure($handler)) {
            $paramMap  = $this->_getFunctionArgumentsNames($handler); 
            $arguments = $this->_getFunctionArguments($paramMap, $parameters, $route["parameters"]);
            return call_user_func_array($handler, $arguments);
        } else {
            return $this->dispatchNotFound();
        }
    }

    /**
     * Called if nothing was not found.
     * Can call a a defined handler or raise exception if the handler will not be specified.
     * 
     * @return mixed
     */
    public function dispatchNotFound() {
        if (isset($this->_notFoundHandler)) {
            if ($this->_isClosure($this->_notFoundHandler)) {
                return call_user_func($this->_notFoundHandler);    
            } 
        } else {
            throw new \Exception('Nothing was found');            
        }        
    }

    /**
     * Set not found handler
     * 
     * @return self for fluent interface
     */
    public function setNotFoundHandler($handler) {
        $this->_notFoundHandler = $handler;
        return $this;  
    }

    // ------------ PRIVATE FUNCTIONS 

    /**
     * Check if variable is closure
     * 
     * @param mixed $type variable to check
     * 
     * @return boolean return true if is
     */
    private function _isClosure($type) {
        return is_object($type) && ($type instanceof \Closure);
    }

    /**
     * Get arguments for closure function in proper order from provided parameters
     * 
     * @param mixed $paramMap   parameter map - that will be used for getting proper order
     * @param mixed $matches    parameters from request
     * @param mixed $parameters expected parameters from route

     * @return array parametrs in right order, if there are not any parametrs an empty array is returned
     */
    private function _getFunctionArguments($paramMap, $matches, $parameters) {
        $output  = [];
        $matches = array_values($matches);
        if (isset($parameters)) {
            foreach ($parameters as $key => $valueName) {
                foreach ($paramMap as $possition => $value) {
                    if ($value == $valueName[1][0]) {
                        $output[] = $matches[$possition];
                    }
                } 
            }
        }

        return $output;
    }

    /**
     * Get name of parameters for provided closure
     * 
     * @param \Closure $closure 
     *
     * @return array 
     */
    private function _getFunctionArgumentsNames($closure) {
        $closureReflection = new \ReflectionFunction($closure);
        $result            = [];
        foreach ($closureReflection->getParameters() as $param) {
            $result[] = $param->name;   
        }

        return $result;
    }    
}