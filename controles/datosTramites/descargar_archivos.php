<?php
$continua = TRUE;
$mensaje = "";
if (!isset($idEnviosCajaMedicos)) {
	require_once ('../../dataAccess/config.php');
	permisoLogueado();
	require_once ('../../dataAccess/funcionesConector.php');
	require_once ('../../dataAccess/funcionesPhp.php');
	require_once ('../../dataAccess/envios_caja_medicosLogic.php');

	if (isset($_GET['id']) && $_GET['id']) {
		$idEnviosCajaMedicos = $_GET['id'];
	} else {
	    $continua = FALSE;
	    $mensaje .= 'idEnvioDebito no ingresado - ';
	}
}
if ($continua) {
    $envioLogic = new enviosCajaMedicosLogic();
    $resTramites = $envioLogic->obtenerEnvioDetalle($idEnviosCajaMedicos);
    if ($resTramites['estado']) {
		$path = '/archivos/caja_medicos/';
		$camino = $_SERVER['DOCUMENT_ROOT'].'/'.PATH_PDF.$path;
	    $nombreXls = 'NovedadesDistritoI_'.date('Ymd').'.xls';
	    $nombreCsv = 'NovedadesDistritoI_'.date('Ymd').'.csv';
	    //$nombreArchivo = $camino.$nombreXls;
	    $nombreArchivo = $camino.$nombreCsv;
	    if (!file_exists($camino)) {
	    	mkdir($camino, 0777, true);
	    }
	    if (isset($_GET['descargar'])) {
		    header('Content-type: application/vnd.ms-excel');
		    header('Content-disposition: attachment; filename='.$nombreXls);
		    $isPrintHeader = false;
		    $dato_archivo = array();
		    foreach ($resTramites['datos'] as $dato){
			    $dato_archivo['Matricula'] = $dato['matricula'];
			    $dato_archivo['Apellido_Nombre'] = utf8_decode(trim($dato['apellido']).' '.trim($dato['nombre']));
			    $dato_archivo['Fecha_Novedad'] = cambiarFechaFormatoParaMostrar($dato['fecha']);
			    $dato_archivo['Fecha_Actualizacion'] = cambiarFechaFormatoParaMostrar($dato['fechaActualizacion']);
			    $dato_archivo['Tipo_Novedad'] = $dato['tipoNovedad'];
			    $dato_archivo['Novedad'] = utf8_decode($dato['nombreMovimiento']);
			    $dato_archivo['Distrito_Cambio'] = $dato['distritoCambio'];
			    if ($dato['telefonoFijo'] == "-" || strtoupper($dato['telefonoFijo']) == "NR") {
			    	$dato_archivo['Telefono_Fijo'] = "";	
			    } else {
			        $dato_archivo['Telefono_Fijo'] = $dato['telefonoFijo'];
			    }
			    if ($dato['telefonoMovil'] == "-" || strtoupper($dato['telefonoMovil']) == "NR") {
			    	$dato_archivo['Telefono_Movil'] = "";	
			    } else {
			    	$dato_archivo['Telefono_Movil'] = $dato['telefonoMovil'];
			    }
			    $dato_archivo['Correo_Electronico'] = $dato['correoElectronico'];
		        if (! $isPrintHeader ) {
		            echo implode("\t", array_keys($dato_archivo)) . "\n";
		            $isPrintHeader = true;
		        }
		        echo implode("\t", array_values($dato_archivo)) . "\n";
		    }
		} else {
			$rutaServidor = $nombreArchivo;
			$archivo = fopen($rutaServidor, 'w');
			$isPrintHeader = false;
			$dato_archivo = array();
			foreach ($resTramites['datos'] as $dato) {
			    $idEnviosCajaMedicosDetalle = $dato['idEnviosCajaMedicosDetalle'];
			    $dato_archivo['Apellido_Nombre'] = utf8_decode(trim($dato['apellido']).' '.trim($dato['nombre']));
			    $dato_archivo['Matricula'] = $dato['matricula'];
			    $dato_archivo['Fecha_Novedad'] = cambiarFechaFormatoParaMostrar($dato['fecha']);
			    $dato_archivo['Fecha_Actualizacion'] = cambiarFechaFormatoParaMostrar($dato['fechaActualizacion']);
			    $dato_archivo['Novedad'] = utf8_decode($dato['nombreMovimiento']);
			    $dato_archivo['Distrito_Cambio'] = $dato['distritoCambio'];
			    
			    $dato_archivo['Telefono_Fijo'] = ($dato['telefonoFijo'] == "-" || strtoupper($dato['telefonoFijo']) == "NR") ? "" : $dato['telefonoFijo'];
			    $dato_archivo['Telefono_Movil'] = ($dato['telefonoMovil'] == "-" || strtoupper($dato['telefonoMovil']) == "NR") ? "" : $dato['telefonoMovil'];
			    $dato_archivo['Correo_Electronico'] = $dato['correoElectronico'];

			    if (!$isPrintHeader) {
			        fwrite($archivo, implode("\t", array_keys($dato_archivo)) . "\n");
			        $isPrintHeader = true;
			    }
			    fwrite($archivo, implode("\t", array_values($dato_archivo)) . "\n");
			}
			fclose($archivo);
		    $resCertificadoPdf = $envioLogic->guardarEnvioArchivo($idEnviosCajaMedicos, $path, $nombreCsv, 'csv');
		    if ($resCertificadoPdf['estado']) {
		        $resultado['mensaje'] = $resCertificadoPdf['mensaje'];
		    } else {
		        $resultado['mensaje'] = $resCertificadoPdf['mensaje'];
		    }
		}
	/*
	if (isset($_GET['descargar'])) {
		// Verificamos que el archivo exista antes de enviarlo
		if (file_exists($rutaServidor)) {
		    // Definimos el nombre que verá el usuario al descargar
		    $nombreDescarga = basename($rutaServidor);

		    // Limpiamos el búfer de salida para evitar caracteres extraños
		    ob_clean();
		    flush();

		    // Encabezados para forzar la descarga
		    header('Content-Description: File Transfer');
		    header('Content-Type: text/plain'); // O 'text/csv' si es un CSV
		    header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($rutaServidor));

		    // Lee el archivo y lo envía al navegador
		    readfile($rutaServidor);
		    
		    // Opcional: eliminar el archivo del servidor después de la descarga
		    // unlink($rutaServidor); 
		    
		    exit;
		} else {
		    die("Error: El archivo no se pudo generar.");
		}
	}
	*/
    } else {
        $continua = FALSE;
        $mensaje .= $resEnvio['mensaje'];
        $clase = $resEnvio['clase'];
    }
}
?>