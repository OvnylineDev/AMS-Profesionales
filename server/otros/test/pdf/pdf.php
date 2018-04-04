<?php
require_once "dompdf/autoload.inc.php";
// reference the Dompdf namespace
use Dompdf\Dompdf;

savepdf(file_get_contents("partes/albanil.html"), 'albanil');
savepdf(file_get_contents("partes/aluminio.html"), 'aluminio');
savepdf(file_get_contents("partes/carpintero.html"), 'carpintero');
savepdf(file_get_contents("partes/cerrajero.html"), 'cerrajero');
savepdf(file_get_contents("partes/cristalero.html"), 'cristalero');
savepdf(file_get_contents("partes/electricista.html"), 'electricista');
savepdf(file_get_contents("partes/fontanero.html"), 'fontanero');
savepdf(file_get_contents("partes/generico.html"), 'generico');
savepdf(file_get_contents("partes/parquet.html"), 'parquet');
savepdf(file_get_contents("partes/pintor.html"), 'pintor');

function savepdf($filedata, $filename){
	$folder = "";

	//$filedata=str_replace("\\/", "/", $img);
	$folder = "result/";
	// $folder = UPLOAD_DIR;
	if (!is_dir($folder)) {
		mkdir($folder);
	}

	$folder .= $filename . ".pdf";

     // instantiate and use the dompdf class
     $dompdf = new Dompdf();
     $dompdf->loadHtml($filedata);

     // (Optional) Setup the paper size and orientation
     $dompdf->setPaper('A4', 'portrait');

     // Render the HTML as PDF
     $dompdf->render();

	$output = $dompdf->output();

	file_put_contents($folder, $output);

	return $folder;
}
?>
