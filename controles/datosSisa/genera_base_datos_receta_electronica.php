<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/sisaLogic.php');
$sisaLogic = new sisaLogic();
set_time_limit(0);
$continua = TRUE;
$mensaje = "";
if ($continua) {
if ($continua) {
$inicio = 0;
$limite = 1000;
$hayColegiados = true;
while ($hayColegiados) {
$resColegiados = $sisaLogic->obtenerColegiadoParaExportacion($inicio, $limite);
			if ($resColegiados['estado']) {
				//se genera la liquidacion en las tablas linkpagos y linkpagosdetalle
			    foreach ($resColegiados['datos'] as $colegiado) {
			    	$nombre = str_pad(utf8_decode($colegiado['nombre']), 100);
		            $apellido = str_pad(utf8_decode($colegiado['apellido']), 100);
		            $tipoDocumento = $colegiado['tipoDocumento'];
			        switch ($tipoDocumento) {
			        	case '1':
			        		$tipoDocumento = 3;
			        		break;
			        	
			        	case '2':
			        		$tipoDocumento = 2;
			        		break;
			        	
			        	case '3':
			        		$tipoDocumento = 1;
			        		break;
			        	
			        	case '4':
			        		$tipoDocumento = 4;
			        		break;
			        	
			        	case '5':
			        		$tipoDocumento = 5;
			        		break;
			        	
			        	default:
			        		$tipoDocumento = 6;
			        		break;
			        }
		            $numeroDocumento = rellenarCeros($colegiado['numeroDocumento'], 9);
		            $sexo = $colegiado['sexo'];
			        if ($colegiado['fechaNacimiento'] == '') {
			            $fechaNacimiento = '  -  -    ';
			        } else {
		            	$fechaNacimiento = date('d-m-Y', strtotime($colegiado['fechaNacimiento']));	        	
			        }
					$paisNacimiento = '999';
		        	$provinciaNacimiento = '99';
		        	$partidoNacimiento = '999';
		        	$localidadNacimiento = '99';        
		            $nacionalidad = $colegiado['codigoPais'];
		            $estadoMatricular = $colegiado['estadoMatricular'];
		        	if ($estadoMatricular == 7) {
		            	$fallecido = 'SI';
			            if ($colegiado['fechaFallecimiento'] <> '') {
			            	$fechaFallecimiento = date('d-m-Y', strtotime($colegiado['fechaFallecimiento']));        	
			            } else {
			               $fechaFallecimiento='  -  -    ';
			            }
			        } else {
			            $fallecido = 'NO';
			            $fechaFallecimiento = '  -  -    ';
			        }
			        //datos de formacion de grado
			        $tipoFormacion = '3';
			        $profesion = '  1';
			        $titulo = str_pad('MEDICO/A', 200);
		            $institucionFormadora = str_pad($colegiado['codigoInstitucionFormadora'], 4);
			        if ($institucionFormadora == '') {
			            $institucionFormadora = '    ';
			        }
			        $sede = str_pad('', 100);
		            $fechaTitulo = $colegiado['fechaTitulo'];
			        if ($fechaTitulo <> '') {
			            $fechaEgreso = date('d-m-Y', strtotime($fechaTitulo));
			            $fechaTitulo = date('d-m-Y', strtotime($fechaTitulo));
			        } else {
			            $fechaEgreso = '  -  -    ';
			            $fechaTitulo = '  -  -    ';
			        }
		        	$revalidaTitulo = 'NO';
		        	$institucionRevalida = '    ';
		        	$fechaRevalida = '  -  -    ';
			        //datos de matriculacion
			    	$numeroMatricula = rellenarCeros($colegiado['matricula'], 9);
		            $fechaMatricula = $colegiado['fechaMatriculacion'];
			        if ($fechaMatricula <> '') {
			            $fechaMatricula = date('d-m-Y', strtotime($fechaMatricula));
			        } else {
			            $fechaMatricula = '  -  -    ';
			        }
		        	$entidadExpedicion = '117980';
		            $libro = rellenarCeros($colegiado['tomo'], 4);
		            $folio = rellenarCeros( $colegiado['folio'], 4);
			        $acta = '    ';
		        	$expediente = '    ';
		            $codigoEstado = $colegiado['codigoEstado'];
		            $fechaUltimoMovimiento = $colegiado['fechaUltimoMovimiento'];
			        switch ($codigoEstado) {
			        	case 'C':
			                $matriculaActiva = 'NO';
				            $temporalidadMatInactiva = $colegiado['temporalidad'];
			                if ($fechaUltimoMovimiento <> '') {
			                    $fechaInactividadMatricula=  date('d-m-Y', strtotime($fechaUltimoMovimiento));
			                } else {
			                    $fechaInactividadMatricula = '  -  -    ';
			                }
				            $motivoInactividad = $colegiado['motivoInactividad'];
			        		break;
			        	
			        	case 'F':
			                $matriculaActiva = 'NO';
				            $temporalidadMatInactiva = 'P';
		                    $fechaInactividadMatricula = $fechaFallecimiento;
				            $motivoInactividad = 'A';
			        		break;
			        	
			        	case 'J':
			                $matriculaActiva = 'NO';
				            $temporalidadMatInactiva = 'P';
			                if ($fechaUltimoMovimiento <> '') {
			                    $fechaInactividadMatricula=  date('d-m-Y', strtotime($fechaUltimoMovimiento));
			                } else {
			                    $fechaInactividadMatricula = '  -  -    ';
			                }
				            $motivoInactividad = 'A';
			        		break;
			        	
			        	default:
			                $matriculaActiva=            'SI';
			                $temporalidadMatInactiva=    ' ';
			                $fechaInactividadMatricula=  '  -  -    ';
			                $motivoInactividad=          ' ';
			        		break;
			        }

		        	//datos del domicilio declarado
		            $calle = str_pad(utf8_decode($colegiado['calle']), 200);
		            $numero = str_pad($colegiado['numero'], 10);
		            $piso = str_pad(utf8_decode($colegiado['piso']), 10);
		            $depto = str_pad(utf8_decode($colegiado['departamento']), 10);
		            $localidadDomicilio = str_pad($colegiado['codigoLocalidadSisa'], 2);
		            $partidoDomicilio = str_pad($colegiado['codigoPartidoSisa'], 3);
			        if ($localidadDomicilio <> '' && $partidoDomicilio <> '') {
			            $provinciaDomicilio = str_pad($colegiado['codigoProvinciaSisa'], 2);
			            $paisDomicilio = '200';
			        } else {
			            $localidadDomicilio = '99';
			            $partidoDomicilio = '999';
			            $provinciaDomicilio = '99';
			            $paisDomicilio = '999';
			        }
		        	$codigoPostalDomicilio = str_pad($colegiado['codigoPostal'], 8);
			        $tipoTelefono1 = '01';
		            $numeroTelefono1 = str_pad(trim($colegiado['telefono1']), 40);
			        $tipoTelefono2 = '02';
		            $numeroTelefono2 = str_pad(trim($colegiado['telefono2']), 40);
		            $email1 = str_pad(' ', 100);
		            $email2 = str_pad($colegiado['correoElectronico'], 100);
			        $relleno = str_pad(' ', 22);

		        	//carga linea en el archivo
		        	$linea = $nombre.$apellido.$tipoDocumento.$numeroDocumento.$sexo.$fechaNacimiento.$paisNacimiento.$provinciaNacimiento.$partidoNacimiento.$localidadNacimiento.$nacionalidad.$fallecido.$fechaFallecimiento.$tipoFormacion.$profesion.$titulo.$institucionFormadora.$sede.$fechaEgreso.$fechaTitulo.$revalidaTitulo.$institucionRevalida.$fechaRevalida.$numeroMatricula.$fechaMatricula.$entidadExpedicion.$libro.$folio.$acta.$expediente.$matriculaActiva.$temporalidadMatInactiva.$fechaInactividadMatricula.$motivoInactividad.$calle.$numero.$piso.$depto.$localidadDomicilio.$partidoDomicilio.$provinciaDomicilio.$paisDomicilio.$codigoPostalDomicilio.$tipoTelefono1.$numeroTelefono1.$tipoTelefono2.$numeroTelefono2.$email1.$email2;
		        	//.$relleno;

					//inserto el colegiado
			        if (fwrite($fileColegiados, $linea."\n") === FALSE) {
			            $claseMensaje="alert alert-danger";
			            $mensaje="NO SE PUDO GENERAR EL ARCHIVO colegiado.";
			            $continua = FALSE;
			            $hayColegiados = false;
			        }
		    	}
		   	} else {
		   		if (!isset($resColegiados['cantidad'])) {
					$continua = FALSE;
					$mensaje .= $resColegiados['mensaje'];
				}
				$hayColegiados = false;
			}
			$inicio += $limite;
		}
		if ($continua) {
	    	fclose($fileColegiados);
	    }

	    //ESPECIALISTA
		$inicio = 0;
		$limite = 1000;
		$hayColegiados = true;
		while ($hayColegiados) {
			//echo 'inicio->'.$inicio.' - limite->'.$limite.'<br>';
			$resColegiados = $sisaLogic->obtenerEspecialistasParaExportacion($inicio, $limite);
			if ($resColegiados['estado']) {
				//se genera la liquidacion en las tablas linkpagos y linkpagosdetalle
			    foreach ($resColegiados['datos'] as $colegiado) {
		            $tipoDocumento = $colegiado['tipoDocumento'];
			        switch ($tipoDocumento) {
			        	case '1':
			        		$tipoDocumento = 3;
			        		break;
			        	
			        	case '2':
			        		$tipoDocumento = 2;
			        		break;
			        	
			        	case '3':
			        		$tipoDocumento = 1;
			        		break;
			        	
			        	case '4':
			        		$tipoDocumento = 4;
			        		break;
			        	
			        	case '5':
			        		$tipoDocumento = 5;
			        		break;
			        	
			        	default:
			        		$tipoDocumento = 6;
			        		break;
			        }
		            $numeroDocumento = rellenarCeros($colegiado['numeroDocumento'], 9);
			    	$numeroMatricula = rellenarCeros($colegiado['matricula'], 9);
			    	$especialidad = $colegiado['codigoRes62707'];
		            $fechaCertificacion = $colegiado['fechaEspecialista'];
			        if ($fechaCertificacion <> '' && $fechaCertificacion <> '0000-00-00') {
			            $fechaCertificacion = date('d-m-Y', strtotime($fechaCertificacion));
			        } else {
			            $fechaCertificacion = '  -  -    ';
			        }
		            $fechaVigencia = $colegiado['fechaVencimiento'];
			        if ($fechaVigencia <> '' && $fechaVigencia <> '0000-00-00') {
			            $fechaVigencia = date('d-m-Y', strtotime($fechaVigencia));
			        } else {
			            $fechaVigencia = '  -  -    ';
			        }
	        		$modalidad = '5';
	        		$fechaModalidad = '  -  -    ';
	        		$institucionFormadora = '    ';
	        		$sociedadCientifica = '  ';
	        		$numeroCertificacion = str_pad(' ', 20);
	        		$folio = str_pad(' ', 20);
	        		$acta = str_pad(' ', 20);
	        		$expediente = str_pad(' ', 20);
	        		$comentarios = str_pad(' ', 200);

			        $linea = $tipoDocumento.$numeroDocumento.$numeroMatricula.$especialidad.$fechaCertificacion.$fechaVigencia.$modalidad.$fechaModalidad.$institucionFormadora.$sociedadCientifica.$numeroCertificacion.$folio.$acta.$expediente.$comentarios;

					//inserto el colegiado
			        if (fwrite($fileEspecialistas, $linea."\n") === FALSE) {
			            $claseMensaje="alert alert-danger";
			            $mensaje="NO SE PUDO GENERAR EL ARCHIVO especialidad.";
			            $continua = FALSE;
			            $hayColegiados = false;
			        }
		    	}
		   	} else {
		   		if (!isset($resColegiados['cantidad'])) {
					$continua = FALSE;
					$mensaje .= $resColegiados['mensaje'];
				}
				$hayColegiados = false;
			}
			$inicio += $limite;
		}
		if ($continua) {
	    	fclose($fileEspecialistas);
	    }
	}
} else {
       $resultado['clase'] = 'alert alert-danger'; 
       $resultado['icono'] = 'glyphicon glyphicon-remove';
}
/*
	echo '<br>';
	var_dump($continua);
	echo '<br>'.$mensaje;
	exit;
*/
$resultado['estado'] = $continua;
$resultado['mensaje'] = $mensaje;
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../sisa_exportaciones.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>
