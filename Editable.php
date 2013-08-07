<?php

class Editable
{


    private $privateFunctions = array();
    private $publicFunctions = array();

    public function addPublicFunction($name, callable $f)
    {
        if (isset($this->privateFunctions[$name]) || isset($this->publicFunctions[$name]))
        {
            throw new Exception("Function \"$name\" already exist");
        }
        $f = $f->bindTo($this, $this);
        $this->publicFunctions[$name] = $f;
    }
    public function addPrivateFunction($name, callable $f)
    {
        if (isset($this->privateFunctions[$name]) || isset($this->publicFunctions[$name]))
        {
            throw new Exception("Function \"$name\" already exist");
        }
        $f = $f->bindTo($this, $this);
        $this->privateFunctions[$name] = $f;
    }

    public function __call($method, $args)
    {
        //inside access
        if (get_class($this) == get_class())
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
                throw new Exception("Function not exist");
            }
            return call_user_func_array($func, $args);
        }
        //outside
        else
        {
            if (isset($this->privateFunctions[$method]))
            {
                throw new Exception("Trying accessing private function outside class");
            }
            if (isset($this->publicFunctions[$method]))
            {
                $func = $this->publicFunctions[$method];
            }
            else
            {
                throw new Exception("Function not exist");
            }
            return call_user_func_array($func, $args);
        }
    }


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
        if (get_class($this) == get_class())
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
            if (isset($this->privateVariables[$name]))
            {
                throw new Exception("Trying accessing private variable \"$name\" outside class");
            }
            if (isset($this->publicVariables[$name]))
            {
                $var = $this->publicVariables[$name];
            }
            else
            {
                throw new Exception("variable \"$name\" not exist");
            }
            return $var;
        }

    }
}

?>