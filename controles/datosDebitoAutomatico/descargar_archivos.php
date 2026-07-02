<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/debitoAutomaticoLogic.php');
$debitoAutomaticoLogic = new debitoAutomaticoLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id']) {
    $idEnvioDebito = $_GET['id'];
    $resArchivo = $debitoAutomaticoLogic->obtenerEnvioDebitoPorId($idEnvioDebito);
    if ($resArchivo['estado']) {
        $envioDebito = $resArchivo['datos'];
        $nombreArchivo = $envioDebito['nombreArchivo'];
        $path = $envioDebito['pathArchivo'];
    } else {
        $continua = FALSE;
    	$mensaje .= $resArchivo['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'idEnvioDebito no ingresado - ';
}
if ($continua) {
	$file = '../../'.$path.'/'.$nombreArchivo;
	$fileDescarga = $nombreArchivo;
	if (isset($file)) {
		//echo "file->".$file;
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $fileDescarga . '"');
		header('Content-Length: ' . filesize($file));

		readfile($file);
	}
}