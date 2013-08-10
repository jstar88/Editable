<?php
/**
 * Intercepting dynamic function with another 
*/
include("../../Editable.php");
include("B.php");


$authorize = function()
{
    echo "authorizing..<br>";  
};
$actionB = function()
{
    echo "actionB done";
};
$actionBnew = function()
{
    echo "actionBnew done";
};

$f = new B();
//adding new private dynamic function(will be the interceptor implementation).
$f->addPrivateFunction("authorize",$authorize);
//add the new action
$f->addPublicFunction("actionB",$actionB);
//intercept any call to function "actionB" invoking "authorize"
$f->interceptFunction(array($f,"actionB"),array($f,"authorize"));
//update the action
$f->replaceFunction(array($f,"actionB"),$actionBnew);
//run the action
$f->actionB();


?>