<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/homeBankingLogic.php');
$homeBankingLogic = new homeBankingLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id']) {
    $idHomeBanking = $_GET['id'];
    $resArchivo = $homeBankingLogic->obtenerHomaBankingPorId($idHomeBanking);
    if ($resArchivo['estado']) {
        $homeBankingArchivo = $resArchivo['datos'];
		$control = $homeBankingArchivo['control'];
		$refresh = $homeBankingArchivo['refresh'];
		$total = $homeBankingArchivo['importe'];
		$pagoMisCuentas = $homeBankingArchivo['pagoMisCuentas'];
		$path = $homeBankingArchivo['pathArchivo'];

	    $pathLINK = "../../".$path.'/LINK/';
	    $pathPMC = "../../".$path.'/PMC/';
    } else {
        $continua = FALSE;
    	$mensaje .= $homeBankingArchivo['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'idHomeBanking no ingresado - ';
}
if ($continua) {
	$file = NULL;
	if ($_GET['origen'] == 'LINK') {
		$file = $pathLINK;
		if ($_GET['tipo'] == 'control') {
			$file .= $control;
			$fileDescarga = $control;
		} else {
			$file .= $refresh;
			$fileDescarga = $refresh;
		}
	} else {
		if ($_GET['origen'] == 'PMC') {
			$file = $pathPMC.$pagoMisCuentas;
			$fileDescarga = $pagoMisCuentas;
		}
	}
	if (isset($file)) {
		//echo "file->".$file;
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $fileDescarga . '"');
		header('Content-Length: ' . filesize($file));

		readfile($file);
	}
}