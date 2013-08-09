<?php

require "patchwork.phar";
class Editable
{

    // ------- functions managment -------
    private $privateFunctions = array();
    private $publicFunctions = array();
    private $closuring = 0;
    private $replacedFunctions = array();

    private function setPublicFunction($name, callable $f)
    {
        if ($f instanceof Closure)
        {
            $f = $f->bindTo($this, $this);
        }
        $this->publicFunctions[$name] = $f;
    }
    private function setPrivateFunction($name, callable $f)
    {
        if ($f instanceof Closure)
        {
            $f = $f->bindTo($this, $this);
        }
        $this->privateFunctions[$name] = $f;
    }

    public function addPublicFunction($name, callable $f)
    {
        if (isset($this->privateFunctions[$name]) || isset($this->publicFunctions[$name]))
        {
            throw new Exception("Function \"$name\" already exist");
        }
        $this->setPublicFunction($name, $f);
    }
    public function addPrivateFunction($name, callable $f)
    {
        if (isset($this->privateFunctions[$name]) || isset($this->publicFunctions[$name]))
        {
            throw new Exception("Function \"$name\" already exist");
        }
        $this->setPrivateFunction($name, $f);
    }

    public function __call($method, $args)
    {
        //inside access
        if ($this->inside())
        {
            if (isset($this->publicFunctions[$method]))
            {
                $func = $this->publicFunctions[$method];
            }
            elseif (isset($this->privateFunctions[$method]))
            {
                $func = $this->privateFunctions[$method];
            }
            else
            {
                throw new Exception("Function \"$method\" not exist");
            }
            $this->closuring++;
            $return = call_user_func_array($func, $args);
            $this->closuring--;
            return $return;
        }
        //outside
        else
        {
            if (isset($this->publicFunctions[$method]))
            {
                $func = $this->publicFunctions[$method];
            }
            elseif (isset($this->privateFunctions[$method]) || method_exists($this, $method))
            {
                //var_dump(debug_backtrace());die();
                throw new Exception("Trying accessing private function \"$method\" outside class");
            }
            else
            {
                throw new Exception("Function \"$method\" not exist");
            }
            $this->closuring++;
            $return = call_user_func_array($func, $args);
            $this->closuring--;
            return $return;
        }
    }

    public function replaceFunction(callable $old, callable $new)
    {
        $method = $old[1];
        if (isset($this->privateFunctions[$method]))
        {
            $this->setPrivateFunction($method, $new);
        }
        elseif (isset($this->publicFunctions[$method]))
        {
            $this->setPublicFunction($method, $new);
        }
        elseif (method_exists($this, $method))
        {
            Patchwork\replace($old, $new);
            $this->replacedFunctions[$method] = true;
        }
        else
        {
            throw new Exception("Function \"$method\" not exist");
        }
    }
    public function interceptFunction(callable $target, callable $intercept, $args = array())
    {
        Patchwork\replace($target, function () use ($intercept,$args)
        {
            call_user_func_array($intercept, $args); Patchwork\pass(); }
        );
    }
    public function getMethodsNotInherited()
    {
        $class = get_class($this);
        $classReflection = new ReflectionClass($class);
        $classMethods = $classReflection->getMethods();

        $classMethodNames = [];
        foreach ($classMethods as $index => $method)
        {
            if ($method->getDeclaringClass()->getName() !== $class)
            {
                unset($classMethods[$index]);
            }
            else
            {
                $classMethodNames[] = $method->getName();
            }
        }
        return $classMethodNames;
    }

    // ------- variables managment -------

    private $privateVariables = array();
    private $publicVariables = array();

    public function addPrivateVariable($name, $value)
    {
        if (isset($this->privateVariables[$name]) || isset($this->publicVariables[$name]))
        {
            throw new Exception("Variable \"$name\" already exist");
        }
        $this->privateVariables[$name] = $value;
    }
    public function addPublicVariable($name, $value)
    {
        if (isset($this->privateVariables[$name]) || isset($this->publicVariables[$name]))
        {
            throw new Exception("Variable \"$name\" already exist");
        }
        $this->publicVariables[$name] = $value;
    }

    public function __get($name)
    {
        //inside access
        if ($this->inside())
        {
            if (isset($this->privateVariables[$name]))
            {
                $var = $this->privateVariables[$name];
            }
            elseif (isset($this->publicVariables[$name]))
            {
                $var = $this->publicVariables[$name];
            }
            else
            {
                throw new Exception("Variable \"$name\" not exist");
            }
            return $var;
        }
        //outside
        else
        {
            if (isset($this->publicVariables[$name]))
            {
                $var = $this->publicVariables[$name];
            }
            elseif (isset($this->privateVariables[$name]) || property_exists($this, $name))
            {
                throw new Exception("Trying accessing private variable \"$name\" outside class");
            }
            else
            {
                throw new Exception("variable \"$name\" not exist");
            }
            return $var;
        }

    }
    private function inside()
    {
        $debug = debug_backtrace();
        return $this->closuring > 0 || (isset($debug[12]["function"]) && isset($this->replacedFunctions[$debug[12]["function"]]));
    }
}

?>