<?php

$fp = fopen("texto.txt", "r");

$contenido = "";

if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock
     //ftruncate($fp, 0);      // truncate file
     while (!feof($fp)) {
         $contenido .= fread($fp, 8192);
     }
     flock($fp, LOCK_UN);    // release the lock
} else {
    echo "Couldn't get the lock!";
}

echo $contenido;

fclose($fp);

?>
