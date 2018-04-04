<?php
$arrpass=file('passhash.txt');
// To check the number of lines
foreach($arrpass as $pass)
{
   echo decrypt($pass) . "<br />";
}


function decrypt ($string) {
    return base64_decode($string);
}
?>
