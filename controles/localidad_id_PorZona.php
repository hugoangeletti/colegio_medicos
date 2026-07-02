<?php
require_once ('../dataAccess/config.php');
include_once '../dataAccess/funcionesConector.php';
//include_once '../dataAccess/funcionesPhp.php';
include_once '../dataAccess/localidadLogic.php';

$idZona = $_GET['idZona'];
if (isset($_GET['idLocalidad']) && $_GET['idLocalidad'] <> "") {
	$idLocalidad = $_GET['idLocalidad'];
} else {
	$idLocalidad = NULL;
}
$resLocalidades = $localidadLogic->obtenerLocalidadesConIdPorZona($idZona);
if ($resLocalidades['estado']) {
	echo '<option value = "">Selecciona una Localidad</option>';
	foreach ($resLocalidades['datos'] as $localidad) {
		if (isset($idLocalidad) && $localidad['id'] == $idLocalidad) {
			echo '<option value = "'.$localidad['id'].'" selected>'.$localidad['nombre'].'</option>';	
		} else {
			echo '<option value = "'.$localidad['id'].'">'.$localidad['nombre'].'</option>';
		}
	}
} else {
	echo $resLocalidades['mensaje'];
}