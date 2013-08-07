<?php

include("Editable.php");

class B extends Editable
{ 

}

$f = new B();
$f->addPrivateVariable("ciao","Hello World");
$a = function ()
{
    echo $this->ciao;
}
;
$f->addPublicFunction("test", $a);
$f->test();







?>