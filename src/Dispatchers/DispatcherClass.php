<?php
namespace Kambo\Router\Dispatchers;

use Kambo\Router\Dispatchers\Interfaces\DispatcherInterface;

use Kambo\Router\Route\Route;

/**
 * Class dispatcher with module/controller/action support
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Dispatchers
 * @package  Router
 * 
 */
class DispatcherClass implements DispatcherInterface
{
    const CONTROLLER = 'controler';
    const MODULE     = 'module';
    const ACTION     = 'action';

    /**
     * Not found handler which will be called if nothing has been found.
     *
     * @var mixed
     */
    private $_notFoundHandler;

    /**
     * Base namespace for all dispatched classes.
     *
     * @var string
     */
    private $_baseNamespace = null;

    /**
     * Name of class that will be used for constructing a namespace for proper class resolve.
     *
     * @var string
     */
    private $_controllerName = 'Controllers';

    /**
     * Name of module that willbe used for constructing a namespace for proper class resolve.
     *
     * @var string
     */
    private $_moduleName = 'Modules';

    /**
     * Prefix for action method.
     * Target method is allways called with this prefix.
     *
     * @var string
     */
    private $_actionName = 'action';

    /**
     * Dispatch found route with given parameters
     * 
     * @param Route $route      found route
     * @param mixed $parameters parameters for route
     *
     * @return mixed
     */
    public function dispatchRoute(Route $route, array $parameters) {
        $handler = $route->getHandler();
        if (isset($handler['controler']) && isset($handler['action'])) {
            $routeParameters = $route->getParameters();
            list($controllerName, $action) = $this->_resolveControlerAction($parameters, $routeParameters, $handler);
            $paramMap       = $this->_getMethodParametersNames($controllerName, $this->_actionName.$action);
            $controller     = new $controllerName();
            $callparameters = $this->_getFunctionArgumentsControlers($paramMap, $parameters, $routeParameters, $handler);
            
            return call_user_func_array(array($controller, $this->_actionName.$action), $callparameters);
        } else {
            return $this->dispatchNotFound();
        }
    }

    /**
     * Called if nothing has been not found 
     * 
     * @return mixed
     */
    public function dispatchNotFound() {
        if (isset($this->_notFoundHandler)) {
            $notFoundHandler    = $this->_notFoundHandler;
            $controllerName     = $this->_baseNamespace.'\\'.$this->_controllerName.'\\'.$notFoundHandler['controler'];
            $controllerInstance = new $controllerName();

            return call_user_func([$controllerInstance, $this->_actionName.$notFoundHandler['action']]);
        }    
    }

    /**
     * Set base namespace to allow proper resolve of class name
     *
     * @param string $baseNamespace base namespace
     * 
     * @return self for fluent interface
     */
    public function setBaseNamespace($baseNamespace) {
        $this->_baseNamespace = $baseNamespace;

        return $this; 
    }

    /**
     * Set not found handler
     *
     * @param string $handler handler that will be excuted if nothing has been found 
     * 
     * @return self for fluent interface
     */
    public function setNotFoundHandler($handler) {
        $this->_notFoundHandler = $handler;

        return $this;
    }

    // ------------ PRIVATE METHODS 

    /**
     * Resolve target name of class (controller) and method (action)
     * 
     * @param mixed $matches    found matched variables
     * @param mixed $parameters route parameters  
     * @param mixed $handler    handler that should be executed
     *
     * @return mixed
     */
    private function _resolveControlerAction($matches, $parameters, $handler) {
        $controler = $handler['controler'];
        $action    = $handler['action'];

        if ($this->_isPlaceholder($controler) && $this->_isPlaceholder($action)) {
            $matches = array_values($matches); 

            $transformed = [];
            foreach ($handler as $target => $placeholder) {
                foreach ($parameters as $key => $parameterName) {
                    if ($parameterName[0][0] == $placeholder) {
                        if ($target == 'controler') {
                            $transformed[$target] = $this->_resolveNamespace($parameters, $handler, $matches).'\\'.$matches[$key].'Controler';
                        } else {
                            $transformed[$target] = $matches[$key];    
                        }
                    }
                }
            }

            return [$transformed['controler'], $transformed['action']];   
        } else if  ($this->_isPlaceholder($action)) {
            $matches = array_values($matches);     

            $transformed = [];
            foreach ($handler as $target => $placeholder) {
                foreach ($parameters as $key => $parameterName) {
                    if ($parameterName[0][0] == $placeholder) {
                        if ($target == 'controler') {
                            $transformed[$target] = $matches[$key].$this->_controllerName;
                        } else {
                            $transformed[$target] = $matches[$key];    
                        }
                    }
                }
            }
                        
            return [$this->_resolveNamespace($parameters, $handler, $matches).'\\'.$controler, $transformed['action']];  // naive solution...    
        } else {
            return [$this->_resolveNamespace($parameters, $handler, $matches).'\\'.$controler, $action];
        }
    }

    /**
     * Resolve proper namespace according parameters, handler and matches
     * 
     * @param mixed $parameters route parameters  
     * @param mixed $handler    handler that should be executed
     * @param mixed $matches    found matched variables

     * @return mixed
     */
    private function _resolveNamespace($parameters, $handler, $matches) {
        if (isset($handler['module'])) {
            $moduleName = $handler['module'];
            if  ($this->_isPlaceholder($moduleName)) {
                foreach ($handler as $target => $placeholder) {
                    foreach ($parameters as $key => $parameterName) {
                        if ($parameterName[0][0] == $placeholder) {
                            if ($target == 'module') {
                                $moduleName = $matches[$key];    
                            }
                        }
                    }
                }
            }

            return $this->_baseNamespace.'\\'.$this->_moduleName.'\\'.$moduleName.'\\'.$this->_controllerName;
        }

        return $this->_baseNamespace.'\\'.$this->_controllerName;
    }

    /**
     * Check if the variable is placeholder
     * 
     * @param string $value  found route
     *
     * @return boolean true if value should be transfered
     */
    private function _isPlaceholder($value) {
        if (strrchr($value, '}') && (0 === strpos($value, '{'))) {
            return true;
        }

        return false;
    }

    /**
     * Get function arguments for controler
     * 
     * @param mixed $paramMap   parameter map
     * @param mixed $matches    found matched variables
     * @param mixed $parameters route parameters  
     * @param mixed $handlers   handler that should be executed

     * @return mixed
     */
    private function _getFunctionArgumentsControlers($paramMap, $matches, $parameters, $handlers) {
        $output  = [];
        $matches = array_values($matches);

        if (isset($parameters)) {
            foreach ($handlers as $placeholder) {
                if ($this->_isPlaceholder($placeholder)) {
                    foreach ($parameters as $key => $parameterName) {
                        if ($parameterName[0][0] == $placeholder) {
                            unset($parameters[$key]);
                            unset($matches[$key]);
                        }
                    }                        
                }
            }

            $parameters = array_values($parameters);
            $matches    = array_values($matches);

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
     * Get names of parameters for provided class and method
     * 
     * @param class  $class      name of class
     * @param string $methodName name of method
     *
     * @return array 
     */
    private function _getMethodParametersNames($class, $methodName) {
        $methodReflection = new \ReflectionMethod($class, $methodName);
        $parametersName   = [];
        foreach ($methodReflection->getParameters() as $parameter) {
            $parametersName[] = $parameter->name;   
        }

        return $parametersName;
    }    
}