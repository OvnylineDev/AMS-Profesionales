<?php

$fp = fopen("41f72cba96.json", "r+");

if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock
     echo "sleep";
     sleep(15);
     flock($fp, LOCK_UN);    // release the lock
} else {
     echo "Couldn't get the lock!";
}

fclose($fp);

?>
