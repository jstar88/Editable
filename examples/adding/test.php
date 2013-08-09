<?php
include("../../Editable.php");
include("B.php");

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