<?php
$arrpass=file('users.txt');
// To check the number of lines
foreach($arrpass as $pass)
{
   echo encrypt($pass) . "<br />";
}

function encrypt ($string){
     return base64_encode($string);
}
?>
