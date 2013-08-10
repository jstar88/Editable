<?php
/**
 * Intercepting existing function with a function added dynamically
*/
include("../../Editable.php");
include("B.php");


$authorize = function()
{
    echo "authorizing..<br>";  
};

$f = new B();
$f->addPrivateFunction("authorize",$authorize);
$f->interceptFunction(array($f,"action"),array($f,"authorize"));
$f->action();


?>