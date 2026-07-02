<?php
class certificadoSeguroPraxisMedica {

	function obtenerPdfPorIdColegiado($idColegiado) {
		$colegiadoLogic = new colegiadoLogic();
		$resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
            $sexo = $colegiado['sexo'];
            $numeroDocumento = $colegiado['numeroDocumento'];

	        $colegiado_seguro_Logic = new colegiado_seguro_Logic();
	        $resSeguro = $colegiado_seguro_Logic->obtenerSeguroPorColegiado($idColegiado);
	        if ($resSeguro['estado']) {
	            $seguro = $resSeguro['datos'];
	            if (isset($seguro) && sizeof($seguro) > 0) {
	                if ($seguro['origen'] == 'COLEGIO') {
	                    // armamaos el path donde se va a guardar el pdf
	                    $subCarpeta = $seguro['pathArchivo'];
                    	if ((isset($subCarpeta) && $subCarpeta <> "") || (date('Y-m-d') > $subCarpeta.'-07-01')) {
                    		$subCarpeta = 'seguro/'.PERIODO_ACTUAL;
                    	} 
	                    $camino = $_SERVER['DOCUMENT_ROOT'];
	                    $camino .= '/'.PATH_PDF.'/archivos/'.$subCarpeta.'/';
	                    
	                    if (isset($seguro['nombreArchivo']) && $seguro['nombreArchivo'] <> "") {
	                        $nombreArchivo = $camino.$seguro['nombreArchivo'];
	                    } else {
	                        $nombreArchivo = NULL;
	                    }
	                    $nombreArchivoCompleto = $camino.$seguro['nombreArchivoCompleto'];

	                    //hago una prueba por si existe mas de una vez la matricula
	                    $caminoArchivos = $_SERVER['DOCUMENT_ROOT'].'/'.PATH_PDF.'/archivos/'.$subCarpeta.'/';
	                    $buscarArchivos = $caminoArchivos.$matricula.'*.pdf';
	                    foreach(glob($buscarArchivos) as $archivos_carpeta3){
	                        $archivo_array = explode('/', $archivos_carpeta3);
                            $nombreArchivoCompleto = $archivos_carpeta3;
	                    }
	                    $archivo = end($archivo_array); 
	                    //fin

	                    $certificadoPDF = NULL;
	                    $nombreArchivoCompleto=utf8_decode($nombreArchivoCompleto);
	                    if (file_exists($nombreArchivo) || file_exists($nombreArchivoCompleto)) {
	                        $existe = FALSE;
	                        if (file_exists($nombreArchivo)) {
	                            $pdf_content = file_get_contents($nombreArchivo);        
	                            $existe = TRUE;
	                        } else {
	                            if (file_exists($nombreArchivoCompleto)) {
	                                $pdf_content = file_get_contents($nombreArchivoCompleto);        
	                                $existe = TRUE;
	                            }   
	                        }
	                        if ($existe) {
	                            $certificadoPDF = base64_encode($pdf_content);   
					            $resultado['estado'] = TRUE;
					            $resultado['certificadoPDF'] = $certificadoPDF;
					            $resultado['archivo'] = $archivo;
	                        } else {
					            $resultado['estado'] = FALSE;
					            $resultado['mensaje'] = 'CERTIFICADO NO EXISTE UNO.';
	                        }
	                        //lo fuerzo para que vaya a buscar uno nuevo
				            $resultado['estado'] = FALSE;
	                    } else {
				            $resultado['estado'] = FALSE;
				            $resultado['mensaje'] = 'CERTIFICADO NO EXISTE DOS.';
	                    }
	                } else {
			            $resultado['estado'] = FALSE;
			            $resultado['mensaje'] = 'COBERTURA ERRONEA -> '.$seguro['origen'];
	                }
	                if (!$resultado['estado']) {
	                    //si no se encuentra el pdf en el servidor lo vamos a buscar por ws de la compañia
	                    $resWS = $colegiado_seguro_Logic->obtenerCertificadoPorWS($matricula, $numeroDocumento, $caminoArchivos);
	                    //var_dump($resWS);
	                    if ($resWS['estado']) {
	                        $archivo = $resWS['archivo'];
	                        if (isset($archivo)) {
	                            $nombreArchivoCompleto = '../archivos/'.$subCarpeta.'/'.$archivo;
	                            $pdf_content = file_get_contents($nombreArchivoCompleto);       
	                            $certificadoPDF = base64_encode($pdf_content); 
					            $resultado['estado'] = TRUE;
					            $resultado['certificadoPDF'] = $certificadoPDF;
					            $resultado['archivo'] = $archivo;
	                        } else {
					            $resultado['estado'] = FALSE;
					            $resultado['mensaje'] = 'ERROR AL OBTENER EL CERTIFICADO EN LA COMPAÑIA. ('.$resWS['mensaje'].')';
	                        }
                        } else {
				            $resultado['estado'] = FALSE;
				            $resultado['mensaje'] = 'ERROR AL OBTENER EL CERTIFICADO EN LA COMPAÑIA. ('.$resWS['mensaje'].')';
                        }
	                }
	            } else {
		            $resultado['estado'] = FALSE;
		            $resultado['mensaje'] = 'NO SE ENCONTRARON DATOS DE COBERTURA';
	            }
	        } else {
	            $resultado['estado'] = FALSE;
	            $resultado['mensaje'] = 'ERROR AL ACCEDER A LOS DATOS: '.$resSeguro['mensaje'];
	        }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = 'ERROR AL ACCEDER A LOS DATOS: '.$resColegiado['mensaje'];
        }
		return $resultado;
	}

}
