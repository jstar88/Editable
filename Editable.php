<?php

require "patchwork.phar";
class Editable
{

    // ------- functions managment -------
    private $privateFunctions = array();
    private $publicFunctions = array();
    private $closuring = 0;
    private $replacedFunctions = array();

    private function setPublicFunction($name, callable $f, $resetInterceptor = true)
    {
        if ($f instanceof Closure)
        {
            $f = $f->bindTo($this, $this);
        }
        $this->publicFunctions[$name]["func"] = $f;
        if ($resetInterceptor)
        {
            $this->publicFunctions[$name]["pre"] = "";
        }
    }
    private function setPrivateFunction($name, callable $f, $resetInterceptor = true)
    {
        if ($f instanceof Closure)
        {
            $f = $f->bindTo($this, $this);
        }
        $this->privateFunctions[$name]["func"] = $f;
        if ($resetInterceptor)
        {
            $this->privateFunctions[$name]["pre"] = "";
        }
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
                $func = $this->publicFunctions[$method]["func"];
                $interceptor = $this->publicFunctions[$method]["pre"];
            }
            elseif (isset($this->privateFunctions[$method]))
            {
                $func = $this->privateFunctions[$method]["func"];
                $interceptor = $this->privateFunctions[$method]["pre"];
            }
            else
            {
                throw new Exception("Function \"$method\" not exist");
            }
            $this->closuring++;
            if (!empty($interceptor))
            {
                call_user_func_array($interceptor[0], $interceptor[1]);
            }
            $return = call_user_func_array($func, $args);
            $this->closuring--;
            return $return;
        }
        //outside
        else
        {
            if (isset($this->publicFunctions[$method]))
            {
                $func = $this->publicFunctions[$method]["func"];
                $interceptor = $this->publicFunctions[$method]["pre"];
            }
            elseif (isset($this->privateFunctions[$method]) || method_exists($this, $method))
            {
                throw new Exception("Trying accessing private function \"$method\" outside class");
            }
            else
            {
                throw new Exception("Function \"$method\" not exist");
            }
            $this->closuring++;
            if (!empty($interceptor))
            {
                call_user_func_array($interceptor[0], $interceptor[1]);
            }
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
            $this->setPrivateFunction($method, $new, false);
        }
        elseif (isset($this->publicFunctions[$method]))
        {
            $this->setPublicFunction($method, $new, false);
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
        $method = $target[1];
        if (isset($this->privateFunctions[$method]))
        {
            $this->privateFunctions[$method]["pre"] = array($intercept, $args);
        }
        elseif (isset($this->publicFunctions[$method]))
        {
            $this->publicFunctions[$method]["pre"] = array($intercept, $args);
        }
        elseif (method_exists($this, $method))
        {
            $this->replacedFunctions[$intercept[1]] = true;
            Patchwork\replace($target, function ()use($intercept, $args)
            {
                call_user_func_array($intercept, $args); Patchwork\pass(); }
            );
        }
        else
        {
            throw new Exception("Function \"$method\" not exist");
        }
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

    public function addPrivateVariable($name, $value, $handler = array(), $handlerArgs = array())
    {
        if (isset($this->privateVariables[$name]) || isset($this->publicVariables[$name]))
        {
            throw new Exception("Variable \"$name\" already exist");
        }
        $this->privateVariables[$name] = array(
            'value' => $value,
            'handler' => $handler,
            'handlerArgs' => $handlerArgs);
    }
    public function addPublicVariable($name, $value, $handler)
    {
        if (isset($this->privateVariables[$name]) || isset($this->publicVariables[$name]))
        {
            throw new Exception("Variable \"$name\" already exist");
        }
        $this->publicVariables[$name] = array(
            'value' => $value,
            'handler' => $handler,
            'handlerArgs' => $handlerArgs);
    }

    public function __get($name)
    {
        $var = null;
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
        }
        return $var['value'];
    }
    public function __set($name, $value)
    {
        $var = null;
        //inside access
        if ($this->inside())
        {
            if (isset($this->privateVariables[$name]))
            {
                $var = $this->privateVariables[$name];
                $this->privateVariables[$name]['value'] = $value;
            }
            elseif (isset($this->publicVariables[$name]))
            {
                $var = $this->publicVariables[$name];
                $this->publicVariables[$name]['value'] = $value;
            }
            else
            {
                throw new Exception("Variable \"$name\" not exist");
            }
        }
        //outside
        else
        {
            if (isset($this->publicVariables[$name]))
            {
                $var = $this->publicVariables[$name];
                $this->publicVariables[$name]['value'] = $value;
            }
            elseif (isset($this->privateVariables[$name]) || property_exists($this, $name))
            {
                throw new Exception("Trying accessing private variable \"$name\" outside class");
            }
            else
            {
                throw new Exception("variable \"$name\" not exist");
            }
        }
        call_user_func_array($var['handler'], $var['handlerArgs']);
    }
    private function inside()
    {
        $debug = debug_backtrace();
        $return = $this->closuring > 0 || (isset($debug[12]["function"]) && isset($this->replacedFunctions[$debug[12]["function"]])) || (isset($debug[2]["function"]) && isset($this->replacedFunctions[$debug[2]["function"]]));
        return $return;
    }
}

?>