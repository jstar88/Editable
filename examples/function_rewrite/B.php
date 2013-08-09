<?php

class B extends Editable
{
    private function hello()
    {
        return "Hello";
    }
    public function say()
    {
        echo $this->hello();   
    }
}

?>