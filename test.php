<?php

include("Editable.php");

class B extends Editable
{
  private $ciao = "Hello world";   
}

$f = new B();
$a = function ()
{
    echo $this->ciao;
}
;
$f->addPublicFunction("test", $a);
$f->test();



?>