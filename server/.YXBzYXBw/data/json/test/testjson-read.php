<?php

define('DATE_FOR_SQL', 'Y-m-d H:i:s');
define('UNKNOWN', 'unknown');

$array = getSegs();

if ($array != null) {
     foreach ($array as $iter => $subarray) {
          foreach ($subarray as $key => $seg) {
               echo "<br />" . $key . "<br />";
               foreach ($seg as $fieldname => $value) {
                    echo $fieldname . ": " . $value . "<br />";
               }
          }
     }
}

function getSegs(){
	return readJSON("seguimiento");
}

function readJSON($tipo){
	if ($tipo != UNKNOWN) {
		$data = NULL;
		$str_datos = "";
		$json_file = "41f72cba96.json";

		$fp = fopen($json_file, "r+");

		if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock

			while (!feof($fp)) {
				$str_datos .= fread($fp, 8192);
			}

			$data = json_decode($str_datos,true);

			flock($fp, LOCK_UN);    // release the lock
		} else {
			$data = "NULL";
		}

		fclose($fp);

		return $data;
	}

	return NULL;
}
?>
