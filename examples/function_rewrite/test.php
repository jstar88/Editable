<?php
include("../../Editable.php");
include("B.php");

//---- replacing existing code ----
$b = new B();
$b->replaceFunction(array($b,"hello"),function(){return "world";});
$b->say();
//---- end ----


echo "<br>---<br>";

//---- replacing live code ----
$b = new B();
$b->addPrivateFunction("world",function(){return "";});
$b->replaceFunction(array($b,"world"),function(){return "World";});
$b->replaceFunction(array($b,"say"),function(){return $this->hello().$this->world() ;});
echo $b->say();
//$b->hello();
//---- end ----


?>