<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/sisaLogic.php');
$sisaLogic = new sisaLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id']) {
    $idSisaExportacion = $_GET['id'];
	$resExportacion = $sisaLogic->obtenerSisaExportacionPorId($idSisaExportacion);
	if ($resExportacion['estado']) {
		$exportacion = $resExportacion['datos'];
		$periodoProceso = $exportacion['periodoProceso'];
		$nombreArchivoColegiados = $exportacion['nombreArchivoColegiados'];
		$nombreArchivoEspecialistas = $exportacion['nombreArchivoEspecialistas'];
		$path = $exportacion['pathArchivo'];
	} else {
		$continua = FALSE;
		$mensaje .= $resExportacion['mensaje'];
	}
} else {
    $continua = FALSE;
    $mensaje .= 'idEnvioDebito no ingresado - ';
}
if (isset($_GET['archivo']) && $_GET['archivo'] <> "") {
	$archivo = $_GET['archivo'];
} else {
	$continua = FALSE;
    $mensaje .= 'archivo no ingresado - ';
}
if ($continua) {
	$file = $path;
	switch ($archivo) {
		case 'COLEGIADOS':
			$nombreArchivo = $nombreArchivoColegiados;
			break;
		
		case 'ESPECIALISTAS':
			$nombreArchivo = $nombreArchivoEspecialistas;
			break;
		
		default:
			$nombreArchivo = NULL;
			break;
	}
	if (isset($nombreArchivo)) {
		$file = $file.'/'.$nombreArchivo;
		$fileDescarga = $nombreArchivo;
		if (isset($file)) {
			//echo "file->".$file;
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $fileDescarga . '"');
			header('Content-Length: ' . filesize($file));

			readfile($file);
		}
	}
}