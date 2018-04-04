<?php

define('DATE_FOR_SQL', 'Y-m-d H:i:s');
define('UNKNOWN', 'unknown');

saveSeg  (    uniqid("seg-", true),
               "O",
               "36802",
               "17bfc945-9b18-4fd7-82fa-4d95f04cbd51",
               "El operario ha añadido las siguientes anotaciones: asdf.");

function saveSeg($id, $idSerie, $numeroOrden, $idOperario, $observaciones){
	// Añadir al json la información
	// $arr_seg = array($id => array(
     // 		"id" => $id,
     // 		"IdSerie" => $idSerie,
     // 		"NumeroOrden" => $numeroOrden,
     // 		"IdOperario" => $idOperario,
     // 		"Fecha" => date(DATE_FOR_SQL),
     // 		"Observaciones" => $observaciones,
     // 		"Gestionado" => false,
     // 		"Admin" => ""
     //      )
	// );
	//
     $arr_seg = array(
          $id => array(
     		"id" => $id,
     		"IdSerie" => $idSerie,
     		"NumeroOrden" => $numeroOrden,
     		"IdOperario" => $idOperario,
     		"Fecha" => date(DATE_FOR_SQL),
     		"Observaciones" => $observaciones,
     		"Gestionado" => false,
     		"Admin" => ""
          )
	);

	return writeJSON($arr_seg, "seguimiento");
}

function writeJSON($array, $tipo){
     if ($tipo != UNKNOWN) {
		$flag = true;
		$tempArray = null;
		$json_string = "";
		$json_file = "test" . "seg" . ".json";

		$fp = fopen($json_file, "r+");

		if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock

			$str_datos = "";

			while (!feof($fp)) {
				$str_datos .= fread($fp, 8192);
			}

			rewind($fp);
			ftruncate($fp, 0);

			if ($str_datos === FALSE || $str_datos == "") {
	               $tempArray = array();
			}else{
				$tempArray = json_decode($str_datos, true);
			}

			array_push($tempArray, $array);

			$json_string = json_encode($tempArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

			$flag = fwrite($fp, $json_string);

			flock($fp, LOCK_UN);    // release the lock
		} else {
			$flag = false;
		}

		fclose($fp);

		return $flag;
	}

     return null;
}
?>
