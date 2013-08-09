<?php
include("../../Editable.php");
include("B.php");


$authorize = function()
{
    echo "authorizing..<br>";  
};

$f = new B();
$f->interceptFunction(array($f,"action"),$authorize);
$f->action();


?>