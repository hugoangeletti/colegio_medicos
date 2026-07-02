<?php
//armo el html con el certificado
echo '->'.$idColegiado.'<br>';
$colegiadoLogic = new colegiadoLogic();
$resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
var_dump($resColegiado);

if ($resColegiado['estado'] && $resColegiado['datos']) {
    $colegiado = $resColegiado['datos'];
    $matricula = $colegiado['matricula'];
    $estadoMatricular = $colegiado['estado'];
    $sexo = $colegiado['sexo'];
    switch ($colegiado['tipoEstado']) {
        case 'A':
            $tipoEstadoMatricular = 'ACTIVO';
            $conEstadoTesoreria = TRUE;
            if ($cuotasAdeudadas>=1 && $cuotasAdeudadas<=5 ) {
                $conEstadoTesoreria = FALSE;
            }
            break;

        case 'I':
            $tipoEstadoMatricular = 'Inscripto';
            $conEstadoTesoreria = FALSE;
            break;

        case 'C':
            $tipoEstadoMatricular = 'BAJA('.$colegiado['movimientoCompleto'].')';
            $conEstadoTesoreria = TRUE;
            break;

        case 'F':
        case 'J':
            $tipoEstadoMatricular = 'BAJA('.$colegiado['movimientoCompleto'].')';
            $conEstadoTesoreria = FALSE;
            break;

        default:
            $tipoEstadoMatricular = '-';
            $conEstadoTesoreria = TRUE;
            break;
    } 

    $libro = $colegiado['tomo'];
    $folio = $colegiado['folio'];
    $tipoDocumento = $colegiado['tipoDocumento'];
    $numeroDocumento = $colegiado['numeroDocumento'];
    $fechaMatriculacion = cambiarFechaFormatoParaMostrar($colegiado['fechaMatriculacion']);
    $fechaNacimiento = cambiarFechaFormatoParaMostrar($colegiado['fechaNacimiento']);
        
    $resColegiadoTitulo = $colegiadoLogic->obtenerTitulosPorColegiado($idColegiado);
    if ($resColegiadoTitulo['estado']) {
        $colegiadoTitulo = $resColegiadoTitulo['datos'];
        $fechaTitulo = cambiarFechaFormatoParaMostrar($colegiadoTitulo['fechaTitulo']);
        $tituloColegiado = $colegiadoTitulo['tipoTitulo'];
        $universidad = $colegiadoTitulo['universidad'];
    }
    
    $tieneFotoFirma = FALSE;
    $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '1');
    if ($resArchivos['estado'] && isset($resArchivos['datos'])){
        $archivos = $resArchivos['datos'];
        $fileFoto = trim($archivos['nombre']);
        // insertamos la foto y firma
        $foto = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Fotos/".$fileFoto, "rb");
        if ($foto) {
            $contents=stream_get_contents($foto);
            fclose ($foto);

            $fotoVer = base64_encode($contents);
            $tieneFotoFirma = TRUE;
        } 
        $tieneFotoFirma = TRUE;
    }
        
    //ARMAMOS EL HTML
    $conNota = TRUE;
    switch ($idTipoCertificado) {
        case 6: //a todo efecto
        case 10: //Para la CAJA
        case 11: //Para la AMP
        case 13: //Para el Ministerio de Salud
        case 14: //Para la Superintendencia de Seguros
        case 15: //Para RPP
        case 16: //Para la AMBerisso
        case 17: //Para la AMEnsenada
        case 18: //Para Exterior
        case 19: //Para IOMA
            if ($estadoMatricular == 2 || $estadoMatricular == 3) {
                $html = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                    'A los efectos de ser presentado ante <b>&nbsp;'.$presentado.'</b> se deja expresa constancia que la <b>M.P. '.$matricula.'</b> '.
                    'perteneciente ';
                if ($sexo <> 'F') {
                    $html .= 'al profesional médico';
                } else {
                    $html .= 'a la profesional médica';
                }
                $html .= ', <b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b>'.
                    'se encuentra dada de <b>BAJA</b> en este Colegio de Médicos de la Pcia. de Bs. As. Distrito I. '.
                    'Registrándose a la fecha los siguientes antecedentes: </p>';
            } else {
                $html = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                    'A los efectos de ser presentado ante <b>&nbsp;'.$presentado.'</b> se deja expresa constancia que, ';
                if ($sexo <> 'F') {
                    $html .= 'del profesional médico';
                } else {
                    $html .= 'de la profesional médica';
                }
                $html .= ', <b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b>'.
                    'se registran en este Colegio de Médicos de la Pcia. de Bs. As. Distrito I, a la fecha los siguientes antecedentes: </p>';
            }

            $conDetalle = TRUE;
            if ($idTipoCertificado == 6) {
                $conDomicilio = FALSE;
            } else {
                if ($idTipoCertificado == 5) {
                    //si es para comisiones (5) no se emite la nota al pie
                    $conNota = FALSE;
                }
                $conDomicilio = TRUE;
            }
            $cantidadCertificados = 1;
            break;

        case 9: //FAP completo
            $html = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                'Certifico que ';
                if ($sexo <> 'F') {
                    $html .= 'el profesional médico ';
                } else {
                    $html .= 'la profesional médica ';
                }
                $html .= ' <b>'.$colegiado['nombre'].' '.$colegiado['apellido'].'</b> '.
                'M.P. Nº <b>'.$matricula.'</b>, matriculado en este Distrito I del Colegio de Médicos goza al día de la '.
                'fecha de los beneficios otorgados por el Articulo 5º inciso 17) del Decreto-Ley 5413/58, en '.
                'la redacción dada por la Ley 12043, para eventuales acciones judiciales que tengan como '.
                'causa los efectos del ejercicio profesional y el acto médico personal y particular realizado '.
                'dentro de las normas y reglamentos que regulan el ejercicio de la medicina y los cánones '.
                'médicos habituales, en la jurisdicción de la provincia de Buenos Aires.<br>'.
                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                'Se deja expresa constancia que este certificado revela la situación ';
                if ($sexo <> 'F') {
                    $html .= 'del profesional ';
                } else {
                    $html .= 'de la profesional ';
                }
                $html .= ' al '.
                'día de la fecha para resultar beneficiario del sistema, sin que refleje en forma alguna la '.
                'situación del mismo con anterioridad o posterioridad a la emisión del presente.<br>'.
                '<b> "Artículo 56, Código de Ética Res. C.S. Nº 950/18. Es acto contrario a la ética y '.
                'constituye falta  grave (con  relación '.
                'a un establecimiento privado, a uno estatal o a una institución de derecho público '.
                'no estatal), la acción por la cual el Director médico, el médico propietario, el '.
                'médico que ejerza alguna función jerárquica en la institución o a los médicos '.
                'directores o gerentes exigieren, reclamaren o, de cualquier forma pidieran la '.
                'contratación de un seguro de responsabilidad civil profesional a un colega que '.
                'trabaje o se desempeñe en ese establecimiento o en la órbita de su sistema '.
                'asistencial, cualquiera sea la forma contractual en que se desarrolle ese trabajo '.
                'profesional. También se considerará infracción a la misma exigencia cuando se '.
                'estableciere como requisito para acceder a la relación de trabajo médico o '.
                'locación de servicio. Las actuaciones Sumariales podrán ser promovidas de '.
                'oficio por los Consejos Directivos Distritales. (Res. C.S. Nº540/04).</b></p>';

            $html .= '<br>Para ser presentado ante quien corresponda.';
            $conDetalle = FALSE;
            $conNota = FALSE;
            $cantidadCertificados = 1;
            break;

        default:
            break;
    }

    $alturaLinea = 6;
    $pdf->Ln(5);
    //imprimir QR
    $style = array(
            'border' => true,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );
    $codigoQR = 'http://www.colmed1.com.ar/portal/controls/certificado.php?id='.$idCertificado.'&colegiado='.$idColegiado.'&tipo='.$idTipoCertificado;
    $pdf->write2DBarcode($codigoQR, 'QRCODE,Q', 32,25,25,25, $style, 'N');

    //imprimo la planilla
    $pdf->SetFont('dejavusans', '', 10);
    if ($tieneFotoFirma) {
        $pic = 'data://text/plain;base64,' . base64_encode($contents);
        $pdf->Image($pic , 170 ,25, 25 , 25,'JPG');
        $pdf->Ln(10);
    }
    
    $pdf->SetFont('dejavusans', '', 10);        
    $pdf->MultiCell(0, $alturaLinea, 'Nº '.rellenarCeros($idCertificado, 8), 0, 'L', false, 0, '', '');
    $pdf->MultiCell(0, $alturaLinea, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 1, '50', '');
    $pdf->Ln(5);

    ////
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    $pdf->Ln(5);
    ////
        
    if ($conDetalle) {
        $pdf->SetFont('dejavusans', '', 9);
        $indice = 1;
        if ($sexo == 'F') {
            $html = $indice.'. Médica matriculada bajo el Nº <b>'.$matricula.'</b> , registrado en el Libro <b>'.$libro.'</b> Folio <b>'.$folio.'</b>';
        } else {
            $html = $indice.'. Médico matriculado bajo el Nº <b>'.$matricula.'</b> , registrado en el Libro <b>'.$libro.'</b> Folio <b>'.$folio.'</b>';
        }
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        $indice += 1;
        $html = $indice.'. Tipo y Número de Documento: <b>'.$tipoDocumento.'</b> <b>'.$numeroDocumento.'</b>';
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        $indice += 1;
        $html = $indice.'. Fecha de Matriculación: <b>'.$fechaMatriculacion.'</b>';
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        $indice += 1;
        $html = $indice.'. Fecha de Nacimiento: <b>'.$fechaNacimiento.'</b>';
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        
        //si lleva imprimo los datos del domicilio actual y consultorio
        if ($conDomicilio) {
            $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
            if ($resDomicilio['estado']) {
                $domicilio = $resDomicilio['datos'];
                if ($domicilio['calle']) {
                    $domicilioCompleto = $domicilio['calle'];
                    if ($domicilio['numero']) {
                        $domicilioCompleto .= " Nº ".$domicilio['numero'];
                    }
                    if ($domicilio['lateral']) {
                        $domicilioCompleto .= " e/ ".$domicilio['lateral'];
                    }
                    if ($domicilio['piso'] && strtoupper($domicilio['piso']) != "NR") {
                        $domicilioCompleto .= " Piso ".$domicilio['piso'];
                    }
                    if ($domicilio['depto'] && strtoupper($domicilio['depto']) != "NR") {
                        $domicilioCompleto .= " Dto. ".$domicilio['depto'];
                    }
                }
                
                $domicilioCompleto .= ' - '.$domicilio['nombreLocalidad'].' ('.$domicilio['codigoPostal'].')';
                
                $indice += 1;
                $html = $indice.'. Domicilio: <b>'.$domicilioCompleto.'</b>';
                $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
                //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
            }
            
            //datos de contacto
            $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
            if ($resContacto['estado']) {
                $contacto = $resContacto['datos'];
                $preFijoNacional = '';
                if ($idTipoCertificado == 18) {
                    $preFijoNacional = '54 ';
                }
                $telefonos = '';
                if (isset($contacto['telefonoFijo']) && $contacto['telefonoFijo'] != "") {
                    $telefonos = $preFijoNacional.$contacto['telefonoFijo'].' - '.$contacto['telefonoMovil'];
                }
                if (isset($contacto['telefonoMovil']) && $contacto['telefonoMovil'] != "") {
                    if ($telefonos != '') {
                        $preFijoNacional = ' - '.$preFijoNacional;
                    }
                    $telefonos .= $preFijoNacional.$contacto['telefonoMovil'];
                }
                $mail = $contacto['email'];
                
                $indice += 1;
                $html = $indice.'. Teléfonos: <b>'.$telefonos.'</b>';
                //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 0, '', '', true);
                if ($idTipoCertificado == 10) {
                    //para la caja imprimo el mail
                    $html .= ' - Mail: <b>'.$mail.'</b>';
                    //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '100', '', true);
                } else {
                    //salto la linea
                    //$pdf->MultiCell(0, $alturaLinea, ''.$mail, 0, 'L', false, 1, '', '', true);
                }
                $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
            }
            
            //datos de consultorio
            $resConsultorio = $colegiadoDomicilioLogic->obtenerDomicilioProfesional($idColegiado);
            if ($resConsultorio['estado']) {
                $consultorio = $resConsultorio['datos'];
                $consultorios = $consultorio['domicilio'].' - '.$consultorio['nombreLocalidad'];
                
                $indice += 1;
                $html = $indice.'. Domicilio profesional: <b>'.$consultorios.'</b>';
                $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
            }
        }
        
        $indice += 1;
        $html = $indice.'. Título: <b>'.$tituloColegiado.'</b> Otorgó: <b>'.$universidad.'</b> con fecha: <b>'.$fechaTitulo.'</b>';
        if (isset($bloqueoTitulo)) {
            $html .= ' Con Bloqueo de Título desde el  <b>'.$fechaBloqueoTitulo.'</b>';
        }                
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);

        $indice += 1;
        $resEspecialidades = $colegiadoEspecialistaLogic->obtenerEspecialidadesPorIdColegiado($idColegiado);
        if ($resEspecialidades['estado']) {
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, $alturaLinea, $indice.'. Especialidad: ', 0, 'L', false, 0, '', '', true);
            $pdf->MultiCell(0, $alturaLinea, 'Distrito', 0, 'L', false, 0, '77', '', true);
            $pdf->MultiCell(0, $alturaLinea, 'Con fecha', 0, 'L', false, 0, '90', '', true);
            $pdf->MultiCell(0, $alturaLinea, 'Ult.Recerti.', 0, 'L', false, 0, '108', '', true);
            $pdf->MultiCell(0, $alturaLinea, 'Jerarquizado', 0, 'L', false, 0, '127', '', true);
            $pdf->MultiCell(0, $alturaLinea, 'Consultor', 0, 'L', false, 0, '150', '', true);
            $pdf->MultiCell(0, $alturaLinea, 'Caducidad', 0, 'L', false, 0, '170', '', true);
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->Ln(5);
            
            foreach ($resEspecialidades['datos'] as $row) {
                $pdf->SetFont('dejavusans', 'B', 8);
                $pdf->MultiCell(0, $alturaLinea, $row['nombreEspecialidad'], 0, 'L', false, 0, '', '', true);
                //$pdf->SetFont('dejavusans', '', 8);
                $estadoEspecialidad = $row['estado'];
                $fechaEspecialista = $row['fechaEspecialista'];
                $fechaVencimiento = $row['fechaVencimiento'];
                if (isset($row['fechaRecertificacion']) && $row['fechaRecertificacion'] <> "0000-00-00") {
                    $fechaRecertificacion = $row['fechaRecertificacion'];
                } else {
                    $fechaRecertificacion = "";
                }
                $idEspecialidad = $row['idEspecialidad'];

                if ($estadoEspecialidad == 'A') {
                    if ($row['tipoespecialista'] == 8) {
                        $otorgadaPor = 'Nación';
                    } else {
                        if ($row['distritoOrigen'] == 0) {
                            $row['distritoOrigen'] = 1;
                        }
                        $otorgadaPor = $row['distritoOrigen'];
                    }
                    $pdf->MultiCell(15, $alturaLinea, $otorgadaPor, 0, 'C', false, 0, '77', '', true);
                    $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaEspecialista), 0, 'L', false, 0, '89', '', true);
                    $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaRecertificacion), 0, 'L', false, 0, '108', '', true);
                    $idColegiadoEspecialista = $row['idColegiadoEspecialista'];

                    //verifico si tiene jerarquizado y consultor
                    $verVencimiento = TRUE;
                    $resJerarquizado = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'J');
                    if ($resJerarquizado['estado']) {
                        $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resJerarquizado['fecha']), 0, 'L', false, 0, '128', '', true);
                        $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
                        if ($resConsultor['estado']) {
                            $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resConsultor['fecha']), 0, 'L', false, 0, '150', '', true);
                            $verVencimiento = FALSE;
                        } else {
                            $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '155', '', true);
                        }
                    } else {
                        $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '135', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '155', '', true);
                    }

                    if ($verVencimiento) {
                        if (isset($fechaVencimiento) && $fechaVencimiento != '0000-00-00') {
                            if (date('Y-m-d') > $fechaVencimiento) {
                                //verifica si esta dentro de los 2 años permitidos a Recertificar
                                $fechaLimite = sumarRestarSobreFecha($fechaVencimiento, 2, 'year', '+');
                                if (date('Y-m-d') > $fechaLimite) {
                                    $caduca = 'No Recertificada';
                                } else {
                                    $caduca = 'Recert. en Trámite';
                                }
                            } else {
                                $caduca = 'Recert.hasta: '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                $fechaLimite = sumarRestarSobreFecha($fechaVencimiento, 5, 'year', '-');
                                if ($fechaEspecialista == $fechaLimite) {
                                    $caduca = 'Certif.hasta: '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                }
                            }
                            $pdf->MultiCell(100, $alturaLinea, $caduca, 0, 'L', false, 0, '165', '', true);
                        } else {
                            $caduca = 'No';
                            $pdf->MultiCell(100, $alturaLinea, $caduca, 0, 'C', false, 0, '130', '', true);
                        }
                    }
                } else {
                    //busco la resolucion de la baja
                    $resBaja = $colegiadoEspecialistaLogic->verBajaEspecialista($idColegiado, $idEspecialidad, $fechaEspecialista);
                    if ($resBaja['estado']) {
                        $baja = $resBaja['datos'];
                        $pdf->SetFont('dejavusans', 'B', 8);
                        $pdf->MultiCell(0, $alturaLinea, 'Baja por Res. '.$baja['numero'].' de fecha '. cambiarFechaFormatoParaMostrar($baja['fecha']), 0, 'L', false, 0, '100', '', true);
                    } else {
                        $resBaja['mensaje'];
                    }
                }
                $pdf->Ln(5);
            } //fin foreach especialidades
        } else {
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, $alturaLinea, $indice.'. Especialidad: ', 0, 'L', false, 0, '', '', true);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, $alturaLinea, 'NINGUNA.', 0, 'L', false, 1, '42', '', true);
        }

        //imprimir sanciones
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(0, $alturaLinea, $indice.'. Sanciones éticas disciplinarias: ', 0, 'L', false, 0, '', '', true);
        $conSancion = FALSE;
        $resSanciones = $colegiadoSancionLogic->obtenerSancionesPorIdColegiado($idColegiado);
        if ($resSanciones['estado']) {
            foreach ($resSanciones['datos'] as $row) {
                $fechaDesde = $row['fechaDesde'];
                $fechaHasta = $row['fechaHasta'];
                $ley = $row['ley'];
                $articulo = $row['articulo'];
                $sanciones = NULL;
                switch ($articulo) {
                    case '52c':
                        // le sumo 10 años a la fecha de la sancion para ver si caducó
                        $fechaLimite = sumarRestarSobreFecha($fechaDesde, 10, 'year', '+');
                        if ($fechaDesde >= $fechaLimite) {
                            $sanciones = $ley .' '. cambiarFechaFormatoParaMostrar($fechaDesde) .' al '. cambiarFechaFormatoParaMostrar($fechaHasta) .' Art.:'. $articulo; 
                        }
                        break;

                    case '40c':
                        $fechaActual = date('Y-m-d'); 
                        if ($fechaDesde<=$fechaActual && $fechaHasta>=$fechaActual) {
                            $sanciones = $ley .' '. cambiarFechaFormatoParaMostrar($fechaDesde) .' al '. cambiarFechaFormatoParaMostrar($fechaHasta) .' Art.:'. $articulo; 
                        }
                        break;

                    default:
                        break;
                }
                if (isset($sanciones)) {
                    $pdf->SetFont('dejavusans', 'B', 9);
                    $pdf->MultiCell(0, $alturaLinea, $sanciones, 0, 'L', false, 1, '', '', true);
                    $conSancion = TRUE;
                }
            }
        } 

        if (!$conSancion) {
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, $alturaLinea, 'NINGUNA', 0, 'L', false, 1, '70', '', true);
        }
        $pdf->SetFont('dejavusans', '', 9);

        //imprimir movimientos matriculares
        $indice += 1;
        $resMovimiento = $colegiadoMovimientoLogic->obtenerMovimientosPorIdColegiado($idColegiado);
        if ($resMovimiento['estado']) {
            $pdf->MultiCell(0, $alturaLinea, $indice.'. Movimientos matriculares:', 0, 'L', false, 1, '', '', true);
            
            if (count($resMovimiento['datos'])>1) {
                
                if (count($resMovimiento['datos'])>5) {
                    $pdf->SetFont('dejavusans', '', 8);
                    $alturaLineaMov = 5;
                } else {
                    $pdf->SetFont('dejavusans', '', 9);
                    $alturaLineaMov = 5;
                }
                foreach ($resMovimiento['datos'] as $row) {
                    $elDistrito = $row['distritoCambio'];
                    if (isset($row['romanos'])) {
                        $elDistrito = $row['romanos'];
                    }
                                
                    switch ($row['idTipoMovimietno']) {
                        case 5:
                            $movimiento = 'Colegiado en Distrito I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').' (Baja del Distrito '.$elDistrito.'). ';
                            break;

                        case 6:
                            $movimiento = 'Egreso Definitivo del Distrito I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').', colegiado en Dist.'.$elDistrito.'. ';
                            break;

                        case 8:
                            $movimiento = 'Colegiado en Dist.'.$elDistrito.' Inscripto en Dist.I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').'. ';
                            break;

                        case 9:
                            $movimiento = 'Cancelación de matrícula por Art.40 inc C Decreto Ley 5413/58. Desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y');
                            if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($row['fechaHasta'],'d-m-y').'.-';
                            }
                            break;

                        case 10:
                            $movimiento = 'Colegiado en Dist.I, Inscripto en Dist.'.$elDistrito.' desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y');
                            if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                $movimiento .= ' hasta el '.cambiarFechaFormatoParaMostrar($row['fechaHasta'],'d-m-y').'.-';
                            }
                            break;

                        default:
                            $movimiento = $row['detalleMovimiento'].' desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde']);
                            if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($row['fechaHasta']).'.-';
                            }
                            break;
                    }
                    $pdf->MultiCell(0, $alturaLineaMov,$movimiento, 0, 'L', false, 1, '20', '', true);
                }
            } else {
                $unMovimiento = $resMovimiento['datos'][0];
                $elDistrito = $unMovimiento['distritoCambio'];
                if (isset($unMovimiento['romanos'])) {
                    $elDistrito = $unMovimiento['romanos'];
                }
                switch ($unMovimiento['idTipoMovimietno']) {
                    case 5:
                        $movimiento = 'Colegiado en Distrito I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').' (Baja del Distrito '.$elDistrito.'). ';
                        break;

                    case 6:
                        $movimiento = 'Egreso Definitivo del Distrito I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').', colegiado en Dist.'.$elDistrito.'. ';
                        break;

                    case 8:
                        $movimiento = 'Colegiado en Dist.'.$elDistrito.' Inscripto en Dist.I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').'. ';
                        break;

                    case 9:
                        $movimiento = 'Cancelación de matrícula por Art.40 inc C Decreto Ley 5413/58. Desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y');
                        if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                            $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta'],'d-m-y').'.-';
                        }
                        break;

                    case 10:
                        $movimiento = 'Colegiado en Dist.I, Inscripto en Dist.'.$elDistrito.' desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y');
                        if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                            $movimiento .= ' hasta el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta'],'d-m-y').'.-';
                        }
                        break;

                    default:
                        $movimiento = $unMovimiento['detalleMovimiento'].' desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde']);
                        if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                            $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta']).'.-';
                        }
                        break;
                }
                $pdf->MultiCell(0, $alturaLinea,$movimiento, 0, 'L', false, 1, '20', '', true);
            }
        } else {
            $pdf->MultiCell(0, $alturaLinea, $indice.'. Movimientos matriculares: NO REGISTRA', 0, 'L', false, 1, '', '', true);
        }

        //Situación con tesorería:
        //    !si es fallecido, jubilado, inscripto -> no se imprime la leyenda de Situacion Con Tesoreria
        if ($conEstadoTesoreria) {
            $indice += 1;
            $pdf->Ln(2);
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, $alturaLinea, $indice.'. Situación con tesorería: ', 0, 'L', false, 0, '', '', true);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, $alturaLinea, $estadoConTesoreria, 0, 'L', false, 1, '60', '', true);
        }
    } //fin conDetalle=TRUE

    if ($conNota) {
        //imprimir NOTA
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Ln(2);
        if ($idTipoCertificado == 1) {
            $resNotaCambio = $notaCambioDistritoLogic->obtenerNotaCambioDistritoPorId($idNotaCambioDistrito);
            if ($resNotaCambio['estado']) {
                $notaCambioDistrito = $resNotaCambio['datos']['nota'];
                $html = 'NOTA: '.$notaCambioDistrito.'<br>'; 
            } else {
                $html = '';
            }

            $html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                    'Sin otro particular saludo a usted muy atentamente.-';
        } else {
            if ($idTipoCertificado == 18) {
                // es para el exterior, se emite nota 
                $html = '<p style="line-height: 10em;">Asimismo, se aclara que hasta la fecha de 
                        cancelación de su matrícula y durante el período que ';
                if ($sexo <> 'F') {
                    $html .= 'el profesional médico ';
                } else {
                    $html .= 'la profesional médica ';
                }
                $html .= '<b>'.$colegiado['nombre'].' '.$colegiado['apellido'].'</b>, se encontraba con su 
                        matrícula debidamente habilitada, no se registró sanción disciplinaria, 
                        comunicación y/o inhabilitación judicial que haya impedido el libre 
                        ejercicio de su profesión.</p>';
            } else {
                $html = '<p style="line-height: 10em;"><b>NOTA: El presente certificado tiene validez dentro del ámbito del DISTRITO I y demás jurisdicciones donde el profesional se encuentre debidamente INSCRIPTO.-</b>
                    <br><i>Artículo 7º del Reglamento de Matriculación: "... el médico que ejerza en otro u otros distritos deberá presentarse a los respectivos Colegios para la debida constancia administrativa y el registro correspondiente...".- </i></p>';
            }
        }
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    }
        
    $pdf->Ln(5);
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->MultiCell(0, 1, 'Realizó: '.$_SESSION['user_entidad']['nombreUsuario'], 0, 'L', false, 1, '', '', true);                    
        
    //imprimo sello oval
    $pdf->Ln(15);
    $img = '../../public/images/SELLO.png';
    if ($conFirma == 'S') {
        //imprimo sello y firma
        //1: presidente
        $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(1); 
        if ($resFirmante['estado']) {
            $firmante = $resFirmante['datos'];
            $presidente = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
            $jpgfile1 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
                
            $htmlFirma1 = '<td style="text-align:center;" >
                            <img src="'.$jpgfile1.'" border="0" height="120" width="" />
                            <label style="font-size: 10px;">'.$presidente.'</label><br>
                            <label style="font-size: 8px;">Presidente<br>Colegio de Médicos - Distrito I</label>
                        </td>';
        } else {
            $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
        }
        //2: secretariogeneral
        $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); 
        if ($resFirmante['estado']) {
            $firmante = $resFirmante['datos'];
            $secretario = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
            $jpgfile2 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
                
            $htmlFirma2 = '<td style="text-align:center;" >
                            <img src="'.$jpgfile2.'" border="0" height="120" width="" />
                            <label style="font-size: 10px;">'.$secretario.'</label><br>
                            <label style="font-size: 8px;">Secretario General<br>Colegio de Médicos - Distrito I</label>
                        </td>';
        } else {
            $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
        }
    } else {
        //$pdf->Ln(75);
        $htmlFirma2 = '';
        $htmlFirma1 = '';
    }
    $html = '<table>
            <tr>'
                .$htmlFirma2.
                '<td style="text-align:center;" >
                    <img src="'.$img.'" border="0" height="140" width="" />
                </td>'
                .$htmlFirma1.
            '</tr>
            </table';
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
}