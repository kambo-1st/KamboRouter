<?php
namespace Kambo\Router\Dispatcher;

// \Spl
use ReflectionMethod;

// \Kambo\Router
use Kambo\Router\Dispatcher;
use Kambo\Router\Route\Route\Parsed;

/**
 * Class dispatcher with module/controller/action support
 *
 * @package Kambo\Router\Dispatcher
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 */
class ClassAutoBind implements Dispatcher
{
    /**
     * Not found handler which will be called if nothing has been found.
     *
     * @var mixed
     */
    private $notFoundHandler;

    /**
     * Base namespace for all dispatched classes.
     *
     * @var string
     */
    private $baseNamespace = null;

    /**
     * Name of class that will be used for constructing a namespace for proper
     * class resolve.
     *
     * @var string
     */
    private $controllerName = 'Controllers';

    /**
     * Name of module that will be used for constructing a namespace for proper
     * class resolve.
     *
     * @var string
     */
    private $moduleName = 'Modules';

    /**
     * Prefix for action method.
     * Target method is allways called with this prefix.
     *
     * @var string
     */
    private $actionName = 'action';

    /**
     * Dispatch found route with given parameters
     *
     * @param \Kambo\Router\Route\Route\Parsed $route      Instance of found and parsed route.
     * @param array                            $parameters Additional parameters which will be passed into
     *                                                     the dispatcher.
     *
     * @return mixed
     */
    public function dispatchRoute(Parsed $route, array $parameters)
    {
        $handler = $route->getHandler();
        if (isset($handler['controler']) && isset($handler['action'])) {
            $routePlaceholders             = $route->getPlaceholders();
            list($controllerName, $action) = $this->resolveControlerAction(
                $route->getParameters(),
                $routePlaceholders,
                $handler
            );

            // Create instance of target class
            $controller = new $controllerName();
            // Create class method name with prefix
            $methodName = $this->actionName.$action;

            $parameterMap = $this->getMethodParameters(
                $controllerName,
                $methodName
            );

            $methodParameters = $this->getFunctionArgumentsControlers(
                $parameterMap,
                $route->getParameters(),
                $routePlaceholders,
                $handler
            );

            return call_user_func_array(
                [
                    $controller,
                    $methodName
                ],
                $methodParameters
            );
        }

        return $this->dispatchNotFound();
    }

    /**
     * Called if any of route did not match the request.
     *
     * @return mixed
     */
    public function dispatchNotFound()
    {
        if (isset($this->notFoundHandler)) {
            $notFoundHandler = $this->notFoundHandler;
            $controllerName  = implode(
                '\\',
                [
                    $this->baseNamespace,
                    $this->controllerName,
                    $notFoundHandler['controler']
                ]
            );

            $controllerInstance = new $controllerName();

            return call_user_func(
                [
                    $controllerInstance,
                    $this->actionName.$notFoundHandler['action']
                ]
            );
        }

        return null;
    }

    /**
     * Set base namespace to allow proper resolve of class name
     *
     * @param string $baseNamespace base namespace
     *
     * @return self for fluent interface
     */
    public function setBaseNamespace($baseNamespace)
    {
        $this->baseNamespace = $baseNamespace;

        return $this;
    }

    /**
     * Sets not found handler
     *
     * @param string $handler handler that will be excuted if nothing has been
     *                        found
     *
     * @return self for fluent interface
     */
    public function setNotFoundHandler($handler)
    {
        $this->notFoundHandler = $handler;

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
    private function resolveControlerAction($matches, $parameters, $handler)
    {
        $controler = $handler['controler'];
        $action    = $handler['action'];
        $namespace = $this->resolveNamespace($parameters, $handler, $matches);

        if ($this->isPlaceholder($action)) {
            $transformed = $this->transformHandler($matches, $parameters, $handler);
            if ($this->isPlaceholder($controler)) {
                $controler = $namespace.'\\'.$transformed['controler'];
            } else {
                $controler = $namespace.'\\'.$controler;
            }

            return [
                $controler,
                $transformed['action']
            ];
        }

        return [
            $namespace.'\\'.$controler,
            $action
        ];
    }

    /**
     * Transform provided handler with variables and parameters
     *
     * @param mixed $matches    found matched variables
     * @param mixed $parameters route parameters
     * @param mixed $handler    handler that should be executed
     *
     * @return mixed
     */
    private function transformHandler($matches, $parameters, $handler)
    {
        $transformed = [];
        foreach ($handler as $target => $placeholder) {
            foreach ($parameters as $key => $parameterName) {
                if ($parameterName[0][0] == $placeholder) {
                    if ($target == 'controler') {
                        $transformed[$target] = $matches[$key].'Controler';
                    } else {
                        $transformed[$target] = $matches[$key];
                    }
                }
            }
        }

        return $transformed;
    }

    /**
     * Resolve proper namespace according parameters, handler and matches
     *
     * @param mixed $parameters route parameters
     * @param mixed $handler    handler that should be executed
     * @param mixed $matches    found matched variables

     * @return mixed
     */
    private function resolveNamespace($parameters, $handler, $matches)
    {
        if (isset($handler['module'])) {
            $moduleName = $handler['module'];
            if ($this->isPlaceholder($moduleName)) {
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

            return implode(
                '\\',
                [
                    $this->baseNamespace,
                    $this->moduleName,
                    $moduleName,
                    $this->controllerName
                ]
            );
        }

        return $this->baseNamespace.'\\'.$this->controllerName;
    }

    /**
     * Check if the variable is placeholder
     *
     * @param string $value  found route
     *
     * @return boolean true if value should be transfered
     */
    private function isPlaceholder($value)
    {
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
    private function getFunctionArgumentsControlers(
        $paramMap,
        $matches,
        $parameters,
        $handlers
    ) {
        $output  = [];
        $matches = array_values($matches);

        if (isset($parameters)) {
            foreach ($handlers as $placeholder) {
                if ($this->isPlaceholder($placeholder)) {
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
    private function getMethodParameters($class, $methodName)
    {
        $methodReflection = new ReflectionMethod($class, $methodName);
        $parametersName   = [];
        foreach ($methodReflection->getParameters() as $parameter) {
            $parametersName[] = $parameter->name;
        }

        return $parametersName;
    }
}
