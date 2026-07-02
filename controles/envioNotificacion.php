<?php
require_once 'envioNotificacion_config.php';
/*
//produccion
$desarrollo = (__DIR__) . '/';
require_once $desarrollo . '../dataAccess/conection.php';
require_once $desarrollo . '../dataAccess/funcionesPhp.php';
require_once $desarrollo . '../dataAccess/envioMailDiarioLogic.php';
require_once $desarrollo . '../dataAccess/colegiadoDeudaAnualLogic.php';
require_once $desarrollo . '../dataAccess/colegiadoPlanPagoLogic.php';;
define("ENV", "prod");
//fin produccion
//desarrollo
$desarrollo = "";
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/envioMailDiarioLogic.php');
$envioMailDiarioLogic = new envioMailDiarioLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
define("ENV", "desa");
//fin desarrollo
*/
/*
define("FTP_ARCHIVOS", "ftp://webcolmed:web.2017@192.168.2.50:21");
//define("MAIL_MASIVO", "sistemas@colmed1.org.ar");
//define("MAIL_MASIVO_PASS", "@sistem@s_1965");
define("MAIL_MASIVO", "noreply@colmed1.org.ar");
define("MAIL_MASIVO_PASS", "ColMed@NRPL3214??");

//obtengo el periodo actual
$periodoActual = date('Y');
$mes = date('m');
if ($mes >= 1 && $mes < 5) {
    $periodoActual -= 1;
}
define("PERIODO_ACTUAL", $periodoActual);
//define("PERIODO_ACTUAL", 2024);
*/

//echo ENV; exit;
set_time_limit(0);
$resEnvios = obtenerEnvioDiario();
echo date('H:i:s').'<br>';
//var_dump($resEnvios);
//echo '<br>->'.$desarrollo;
//exit;
if ($resEnvios['estado']){
    require_once $desarrollo . '../PHPMailer/class.phpmailer.php';
    require_once $desarrollo . '../PHPMailer/class.smtp.php';
//    require_once '../PHPMailer/class.phpmailer.php';
//    require_once '../PHPMailer/class.smtp.php';
            
    $limiteEnviosPorHora = 4;
    $totalEnviosDiarios = 0;
    //recorro los tipo de notificaciones y envio los que aun no esten registrados en enviodiariocolegiado
    foreach ($resEnvios['datos'] as $envio){
        $cantidadEnviar = 0;
        $cantidadEnviados = 0;
        if ($totalEnviosDiarios <= $limiteEnviosPorHora) {
            $idEnvio = $envio['idEnvio'];
            $detalle = $envio['detalle'];
            $notaOriginal = $envio['texto'];
            $from = $envio['from'];
            $subject = $envio['subject'];
            if (ENV == 'prod') {
                $rango = $envio['rango'];
            } else {
                $rango = 1;
            }

            $laFecha = 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').'.-';

            switch ($idEnvio) {
                case 1: //notificacion de deuda, se envia pdf con el recibo para pagar el total de la deuda
                case 25: //Notificación a deudores más de 2 períodos
                    require_once $desarrollo . '../dataAccess/notificacionDeudaLogic.php';
                    $respuesta = $notificacionDeudaLogic->obtenerColegiadoNotificacionDeuda($rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = sizeof($respuesta['datos']);
                        $continua = TRUE;
                    } 
                    break;
                case 2: //notificacion debito por tarjeta rechazados
                    require_once $desarrollo . '../dataAccess/debitoAutomaticoLogic.php';
                    $respuesta = $debitoAutomaticoLogic->obtenerColegiadoDebitoRechazado($rango, 28);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    } 
                    break;

                case 3: //notificacion debito por cbu rechazados
                    require_once $desarrollo . '../dataAccess/debitoAutomaticoLogic.php';
                    $respuesta = $debitoAutomaticoLogic->obtenerColegiadoDebitoRechazado($rango, 30);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    } 
                    break;

                case 4: //notificacion titulos para retirar
                    require_once $desarrollo . '../dataAccess/envioMailTituloLogic.php';
                    $anio = date('Y') - 2;
                    
                    $resEnvioMailTitulo = obtenerEnvioMailTitulo();
                    if ($resEnvioMailTitulo['estado']) {
                        $idEnvioMailTitulo = $resEnvioMailTitulo['idEnvioMailTitulo'];
                        
                        $respuesta = obtenerTitulosParaEnviar($anio, $rango);
                        if ($respuesta['estado']) {
                            $var0 = $laFecha;
                            $cantidadEnviar = $respuesta['cantidad'];
                            $continua = TRUE;
                        }
                    } 
                    break;

                case 5: //notificacion titulos vencidos o a vencer
                    require_once $desarrollo . '../dataAccess/colegiadoEspecialistaLogic.php';
                    $anio = date('Y');
                    $anioDesde = $anio - 2;
                    
                    $respuesta = $colegiadoEspecialistaLogic->obtenerEspecialistasConVencimientoParaNotificar($anio, $anioDesde, $rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    }
                    break;

                case 6: //envio de chequera anual de colegiacion
                case 19: //envio de chequera anual de colegiacion trimestre 1
                case 22: //envio de chequera anual de colegiacion trimestre 2
                case 23: //envio de chequera anual de colegiacion trimestre 3
                case 24: //envio de chequera anual de colegiacion trimestre 4
                    require_once $desarrollo . '../dataAccess/colegiadoDeudaAnualLogic.php';
                    echo 'PERIODO_ACTUAL->'.PERIODO_ACTUAL.' - rango->'.$rango.' - idEnvio->'.$idEnvio.'<br>';
                    $respuesta = $colegiadoDeudaAnualLogic->obtenerColegiadoEnvioChequera(PERIODO_ACTUAL, $rango, $idEnvio);
                    //var_dump($respuesta);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    }
                    break;

                //case 9: //notificacion presidentes de mesa
                case 12:
                    require_once $desarrollo . '../dataAccess/presidenteMesaLogic.php';
                    $anio = date('Y');
                    
                    $respuesta = $presidenteMesaLogic->obtenerPresidentesMesaParaNotificar($anio, $rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    }
                    break;

                case 10: //envio de chequera diplomas
                    require_once $desarrollo . '../dataAccess/diplomaLogic.php';
                    
                    $respuesta = $diplomaLogic->obtenerDiplomasEnviar($rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    }
                    break;

                case 14: //notificacion por stopdebit del Banco Provincia por el bono cobrado 2021
                    require_once $desarrollo . '../dataAccess/debitoAutomaticoLogic.php';
                    $respuesta = $debitoAutomaticoLogic->obtenerColegiadoStopDebitPorBono($rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    } 
                    break;

                case 15: //envio de chequera anual de colegiacion
                    require_once $desarrollo . '../dataAccess/colegiadoDeudaAnualLogic.php';
                    $respuesta = $colegiadoDeudaAnualLogic->obtenerColegiadoEnvioChequera2021(PERIODO_ACTUAL, $rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = $respuesta['cantidad'];
                        $continua = TRUE;
                    }
                    break;

                case 16: //envio notificacion cambios en LINK
                    require_once $desarrollo . '../dataAccess/colegiadoDeudaAnualLogic.php';
                    $respuesta = $envioMailDiarioLogic->obtenerColegiadoCambiosLink($rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = sizeof($respuesta['datos']);
                        $continua = TRUE;
                    }
                    break;

                case 18: //envio recibos de caja diaria
                    //require_once $desarrollo . '../dataAccess/cajaDiariaLogic.php';
                    $respuesta = $envioMailDiarioLogic->obtenerRecibosEnviarPorMail($rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = sizeof($respuesta['datos']);
                        $continua = TRUE;
                    }
                    break;

                case 20: //envio nota por pago duplicado
                    //envia a los que se le reimputo el pago doble del periodo anterior al periodo actual
                    $respuesta = $envioMailDiarioLogic->obtenerPagosDuplicados($periodoActual, $rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = sizeof($respuesta['datos']);
                        $continua = TRUE;
                    }
                    break;

                case 21: //envio certificado de cobertura seguro praxis medica
                    //envia a los que se le reimputo el pago doble del periodo anterior al periodo actual
                    $respuesta = $envioMailDiarioLogic->obtenerCertificadoSegur($rango);
                    if ($respuesta['estado']) {
                        $var0 = $laFecha;
                        $cantidadEnviar = sizeof($respuesta['datos']);
                        $continua = TRUE;
                    }
                    break;

                default:
                    break;
            }
            if (ENV == 'desa') {
                var_dump($respuesta);
                if (isset($respuesta['datos'])) {
                    echo '<br>'.sizeof($respuesta['datos']);
                } else {
                    echo '<br>SIN DATOS<br>';
                }
            }
            if ($cantidadEnviar > 0) {
                //recorro la lista obtenida y envio los mails
                $matriculaAnterior = 0;
                foreach ($respuesta['datos'] as $datosMatricula) {
                    //var_dump($datosMatricula);
                    $nota = $notaOriginal;
                    $nota = str_replace('{0}', $var0, $nota);
                    if (isset($datosMatricula['idColegiado'])) {
                        $idColegiado = $datosMatricula['idColegiado'];
                    } else {
                        $idColegiado = NULL;
                    }
                    $idReferencia = $datosMatricula['idReferencia'];
                    $matricula = $datosMatricula['matricula'];
                    if (isset($datosMatricula['sexo'])) {
                        $sexo = $datosMatricula['sexo'];
                    } else {
                        $sexo = "";
                    }
                    $destinatario = utf8_encode(trim($datosMatricula['apellido']).' '.trim($datosMatricula['nombres']));
                    $destinatarioMail = $destinatario;
                    if ($sexo == 'M'){
                        $destinatario = 'Estimado Dr. <b>'.$destinatario.'</b>';
                        $lo_la = 'lo';
                    } else {
                        if ($sexo == 'F') {
                            $destinatario = 'Estimada Dra. <b>'.$destinatario.'</b>';
                            $lo_la = 'la';
                        } else {
                            $destinatario = 'Estimada/o <b>'.$destinatario.'</b>';
                            $lo_la = 'le';
                        }
                    }
                    $destinatario .= ' - M.P. <b>'.$matricula.'</b>';
                    
                    $nota = str_replace('{1}', $destinatario, $nota);

                    if (isset($datosMatricula['fechaCreacion'])) {
                        $fechaCreacion = $datosMatricula['fechaCreacion'];
                    }
                    if (isset($datosMatricula['fechaVencimiento'])) {
                        $fechaVencimiento = $datosMatricula['fechaVencimiento'];
                    }
                    if (isset($datosMatricula['fechaRecibo'])) {
                        $fechaRecibo = $datosMatricula['fechaRecibo'];
                    }

                    if (ENV == 'prod') {
                        $mailDestino = $datosMatricula['mail'];
                        //if ($idEnvio == 1) {
                        //    $mailDestino = 'sistemas@colmed1.org.ar';
                        //}
                    } else {
                        $mailDestino = 'sistemas@colmed1.org.ar';
                        //$mailDestino = 'hangeletti@jag.gba.gob.ar';
                    }
                    
                    if ($mailDestino && $mailDestino!='' && $mailDestino!='NR'){
                        $enviaMail = TRUE;
                        switch ($idEnvio) {
                            case 1: //se le calcula la deuda en el momento, en caso de ya no existir deuda se desestima el envio del mail
                                $nota = str_replace('{2}', $lo_la, $nota);

                                $totalDeudaActualizada = 0;
                                $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualAPagar($idColegiado);
                                if ($resDeuda['estado']) {
                                    $totalDeudaCuotas = 0;
                                    $periodoDeuda = '';
                                    $periodoAnterior = 0;
                                    foreach ($resDeuda['datos'] as $dato) {
                                        if ($dato['vencimiento'] <= date('Y-m-d')) {
                                            $totalDeudaCuotas += $dato['importeActualizado'];
                                            if ($periodoAnterior <> $dato['periodo']) {
                                                if ($periodoAnterior <> 0) {
                                                    $periodoDeuda .= $periodoAnterior.', ';
                                                }
                                                $periodoAnterior = $dato['periodo'];
                                            }
                                        }
                                    }
                                    if ($totalDeudaCuotas > 0) {
                                        $totalDeudaActualizada += $totalDeudaCuotas;
                                        $periodoDeuda .= $periodoAnterior;
                                    }
                                }

                                $resDeudaPP = $colegiadoPlanPagoLogic->obtenerColegiadoPlanPago($idColegiado);
                                if ($resDeudaPP['estado']) {
                                    $totalDeudaPP = 0;
                                    foreach ($resDeudaPP['datos'] as $dato) {
                                        if ($dato['vencimiento'] <= date('Y-m-d')) {
                                            $totalDeudaPP += $dato['importeActualizado'];
                                        }
                                    }
                                    $totalDeudaActualizada += $totalDeudaPP;
                                }

                                if ($totalDeudaActualizada > 0) {
                                    $enviaMail = TRUE;
                                    $nota = str_replace('{3}', cambiarFechaFormatoParaMostrar($fechaCreacion), $nota);
                                    $nota = str_replace('{4}', '$'.$totalDeudaActualizada, $nota);
                                    $nota = str_replace('{5}', cambiarFechaFormatoParaMostrar($fechaVencimiento), $nota);
                                    if ($totalDeudaCuotas > 0 && $totalDeudaPP > 0) {
                                        $tieneDeuda = '(Cuotas de colegiaci&oacute;n per&iacute;odo/s: '.$periodoDeuda.') y plan de pagos).';
                                    } else {
                                        if ($totalDeudaPP > 0) {
                                            $tieneDeuda = '(Cuotas de plan de pagos).';
                                        } else {
                                            $tieneDeuda = '(Cuotas de colegiaci&oacute;n per&iacute;odo/s: '.$periodoDeuda.').';
                                        }
                                    }
                                    $nota = str_replace('{6}', $tieneDeuda, $nota);
                                } else {
                                    $enviaMail = FALSE;
                                }
                                //echo $nota;
                                break;

                            case 2: //notificacion de debito rechazado
                                if ($matricula <> $matriculaAnterior) {
                                    $matriculaAnterior = $matricula;

                                    $tipoDebito = $datosMatricula['tipoTarjeta'];
                                    switch ($tipoDebito) {
                                        case 'D':
                                            $tipoTarjeta = 'debito';
                                            $detalleProblema = substr($datosMatricula['detalle'], 3);
                                            if (substr($datosMatricula['detalle'], 0, 3) == '034') {
                                                $enviaMail = FALSE;
                                                $error = 'No se envia mail, '.$detalleProblema;
                                            }
                                            break;

                                        case 'C':
                                            $tipoTarjeta = 'credito';
                                            $detalleProblema = substr($datosMatricula['detalle'], 2);
                                            break;

                                        default:
                                            $enviaMail = FALSE;
                                            $error = 'Error en el tipo de debito, no se envia mail.';
                                            break;
                                    }
                                    $nota = str_replace('{2}', $tipoTarjeta, $nota);
                                    $nota = str_replace('{3}', $detalleProblema, $nota);
                                } else {
                                    $error = 'La Matrícula ya fue notificada en este envío';
                                    $estado = 'E';
                                    $enviaMail = FALSE;
                                }
                                break;

                            case 3: //notificacion de debito por cbu rechazado
                                if ($matricula <> $matriculaAnterior) {
                                    $matriculaAnterior = $matricula;
                                    $tipoCuenta = $datosMatricula['tipoCuenta'];
                                    switch ($tipoCuenta) {
                                        case '3':
                                            $tipoCuenta = 'Cuenta Corriente';
                                            $detalleProblema = $datosMatricula['detalle'];
                                            break;

                                        case '4':
                                            $tipoCuenta = 'Caja de Ahorro';
                                            $detalleProblema = $datosMatricula['detalle'];
                                            break;

                                        default:
                                            $enviaMail = FALSE;
                                            $error = 'Error en el tipo de cuenta, no se envia mail.';
                                            break;
                                    }
                                    $nota = str_replace('{2}', $tipoCuenta, $nota);
                                    $nota = str_replace('{3}', $detalleProblema, $nota);
                                } else {
                                    $error = 'La Matrícula ya fue notificada en este envío';
                                    $estado = 'E';
                                    $enviaMail = FALSE;
                                }
                                break;
                            
                            case 4: //notificacion titulos para retirar
                                $idTipoEspecialista = $datosMatricula['idTipoEspecialista'];
                                $detalleEspecialidad = 'su Título ';
                                switch ($idTipoEspecialista) {
                                    case CALIFICACION_AGREGADA:
                                        $detalleEspecialidad .= 'de Calificación Agregada en '.$datosMatricula['especialidad'];
                                        break;

                                    case RECERTIFICACION:
                                        $detalleEspecialidad .= 'Recertificado en '.$datosMatricula['especialidad'];
                                        break;
                                    
                                    case CONSULTOR:
                                        $detalleEspecialidad .= 'Consultor en '.$datosMatricula['especialidad'];
                                        break;
                                    
                                    case JERARQUIZADO:
                                        $detalleEspecialidad .= 'Jerarquizado en '.$datosMatricula['especialidad'];
                                        break;
                                    
                                    default:
                                        $detalleEspecialidad = 'Especialista en '.$datosMatricula['especialidad'];
                                        break;
                                }
                                $nota = str_replace('{2}', $detalleEspecialidad, $nota);
                                break;
                            
                            case 5: //notificacion titulos vencidos y a vencer
                                $especialidad = $datosMatricula['especialidad'];
                                $fechaVencimiento = $datosMatricula['fechaVencimiento'];
                                if ($fechaVencimiento < date('Y-m-d')) {
                                    $leyendaVencimiento = 'Venció el '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                } else {
                                    $leyendaVencimiento = 'Caduca el '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                }
                                $nota = str_replace('{2}', $especialidad, $nota);
                                $nota = str_replace('{3}', $leyendaVencimiento, $nota);
                                break;
                            
                            case 6: //envio de chequera cuotas de colegiacion
                                $importe = $datosMatricula['importe'];
                                $importeTotal = $datosMatricula['importeTotal'];
                                $fechaVencimientoPagoTotal = $datosMatricula['fechaVencimiento'];
                                $cuotas = $datosMatricula['cuotas'];

                                //busco las cuotas para imprimir el detall
                                $resCuotas = $colegiadoDeudaAnualLogic->obtenerDeudaAnualCuotas($idReferencia); 
                                if ($resCuotas['estado']) {
                                    $nota = str_replace('{2}', PERIODO_ACTUAL, $nota);
                                    $nota = str_replace('{3}', $cuotas, $nota);
                                    $nota = str_replace('{4}', "$".number_format($importe, 2, ',', ''), $nota);

                                    //armo tabla de cuotas
                                    $tablaCuotas = "<table>
                                        <thead>
                                            <th>Cuota</th>
                                            <th>Importe</th>    
                                            <th>Vencimiento</th>
                                        </thead>
                                        <tbody>";
                                    foreach ($resCuotas['datos'] as $fila) {
                                        $cuota = $fila['cuota'];
                                        $importeCuota = $fila['importe'];
                                        $fechaVencimiento = $fila['vencimiento'];

                                        $tablaCuotas .= "<tr>
                                                        <td>".$cuota."</td>
                                                        <td>$".number_format($importeCuota, 2, ',', '')."</td>
                                                        <td>".cambiarFechaFormatoParaMostrar($fechaVencimiento)."</td></tr>";
                                    }
                                    $tablaCuotas .= "</tbody></table>";
                                    $nota = str_replace('{5}', $tablaCuotas, $nota);

                                    //obtenemos el pago total
                                    if (isset($importeTotal) && $importeTotal > 0) {
                                        $textoPagoTotal = "ó PAGO TOTAL: <b>$".number_format($importeTotal, 2, ',', '')."</b> con único vencimiento ".cambiarFechaFormatoParaMostrar($fechaVencimientoPagoTotal).",- Código Pago Electrónico Red Link / PagoMisCuentas: <b>".rellenarCeros($matricula, 8)."</b>";
                                        $nota = str_replace('{6}', $textoPagoTotal, $nota);
                                    } else {
                                        $textoPagoTotal = "";
                                    }
                                    $nota = str_replace('{6}', $textoPagoTotal, $nota);

                                    $textoDebito = "Le enviamos la chequera con las cuotas para poder abonar por medio de los siguientes canales:<br>
                                                a. HomeBanking: red Link y Pago Mis Cuentas (Puede optar el Pago Total con un 20% de descuento)<br>
                                                b. ProvinciaNET, Pago Facil o Rapipago<br>
                                                c. D&eacute;bito autom&aacute;tico por tarjeta de cr&eacute;dito VISA<br>
                                                d. D&eacute;bito autom&aacute;tico por CBU<br>";
                                    $enviaPdf = TRUE;
                                    if (isset($datosMatricula['idDebitoCBU'])) {
                                        $textoDebito = "Le rocordamos que se encunetra adherido al débito automático por CBU";
                                        $enviaPdf = FALSE;
                                    }
                                    if (isset($datosMatricula['idDebitoTarjeta'])) {
                                        $textoDebito = "Le rocordamos que se encunetra adherido al débito automático por Tarjeta de Crédito/Débito";
                                        $enviaPdf = FALSE;
                                    }
                                    $nota = str_replace('{7}', $textoDebito, $nota);

                                    $nombreArchivo = 'ColegiacionMatricula_'.$matricula.'.pdf';
                                } else {
                                    continue;
                                }
                                break;
                            
                            case 8: //notificacion de grandes deudores
                                if ($matricula <> $matriculaAnterior) {
                                    $matriculaAnterior = $matricula;
                                    $nota = str_replace('{3}', $fecha, $nota);
                                    $nota = str_replace('{4}', $monto+$montoPP, $nota);
                                } else {
                                    $error = 'La Matrícula ya fue notificada en este envío';
                                    $estado = 'E';
                                    $enviaMail = FALSE;
                                }
                                break;

                            //case 9: //notificacion presidentes de mesa
                            case 12:
                                $fecha = $datosMatricula['fecha'];
                                $hora = $datosMatricula['hora'];
                                $mesa = $datosMatricula['mesa'];
                                $nota = str_replace('{2}', $fecha, $nota);
                                $nota = str_replace('{3}', $hora, $nota);
                                $nota = str_replace('{4}', $mesa, $nota);
                                break;

                            case 10: //notificacion presidentes de mesa
                                $nombreEvento = $datosMatricula['nombreEvento'];
                                $nota = str_replace('{2}', $nombreEvento, $nota);
                                $path = $datosMatricula['path'];
                                $nombreArchivo = $datosMatricula['nombrePdf'];
                                break;

                            case 14: //notificacion stopdebit por bono cobrado 2021
                                $codigoElectronico = rellenarCeros($matricula, 8);
                                $nota = str_replace('{2}', $codigoElectronico, $nota);
                                $nombreArchivo = 'ColegiacionMatricula_'.$matricula.'.pdf';
                                $enviaPdf = TRUE;
                                break;

                            case 15: //envio de chequera cuotas de colegiacion 2021
                            case 19: //envio de chequera cuotas de colegiacion trimestre 1
                            case 22: //envio de chequera cuotas de colegiacion trimestre 2
                            case 23: //envio de chequera cuotas de colegiacion trimestre 3
                            case 24: //envio de chequera cuotas de colegiacion trimestre 4
                                $nombreArchivo = 'ColegiacionMatricula_'.$matricula.'.pdf';
                                $enviaPdf = TRUE;
                                break;
                            
                            case 16: //envio datos del homebanking
                                $nota = str_replace('{2}', rellenarCeros($matricula, 8), $nota);
                                break;

                            case 18: //envio recibos de caja diaria
                                $tipoRecibo = $datosMatricula['tipo'];
                                $numeroRecibo = $datosMatricula['numeroRecibo'];
                                //$nota = str_replace('{2}', $tipoRecibo.'-'.$numeroRecibo, $nota);
                                $nota = str_replace('{2}', cambiarFechaFormatoParaMostrar($fechaRecibo), $nota);
                                $enviaPdf = TRUE;
                                break;

                            case 20: //envio pago doble
                                $enviaPdf = FALSE;
                                break;

                            case 21: //envio certificado de seguro
                                $nombreArchivo = $matricula.'.pdf';
                                $enviaPdf = TRUE;
                                break;

                            case 25: //Notificación a deudores más de 2 períodos
                                $enviaPdf = FALSE;
                                break;

                            default:
                                break;
                        }

                        if (ENV == 'desa') {
                            echo $nota;
                            var_dump($enviaMail);
                        }

                        if ($enviaMail) {
                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = "ssl";
                            $mail->Host = "mail.colmed1.org.ar";
                            $mail->Port = 465;                           
                            $mail->Username = MAIL_MASIVO;
                            $mail->Password = MAIL_MASIVO_PASS;
                            //$mail->From = 'noreply@colmed1.org.ar';
                            $mail->From = MAIL_MASIVO;
                            $mail->FromName = "Colegio de Medicos. Distrito I";
                            if ($idEnvio == 25) {
                                //si es deudor de mas de 2 periodos sale personalizado
                                $mail->Subject = $destinatarioMail.' - '.$subject;    
                            } else {
                                $mail->Subject = $subject;
                            }
                            $mail->AltBody = "";
                            $mail->CharSet = 'UTF-8';
                            $mail->MsgHTML($nota);
                            switch ($idEnvio) {
                                case 6:
                                case 14:
                                case 15:
                                case 19: //envio de chequera cuotas de colegiacion 2024 trimestre 1
                                case 22: //envio de chequera cuotas de colegiacion 2024 trimestre 2
                                case 23: //envio de chequera cuotas de colegiacion 2024 trimestre 3
                                case 24: //envio de chequera cuotas de colegiacion 2024 trimestre 4
                                    if ($enviaPdf) {
                                        //$mail->AddAttachment($desarrollo."../archivos/chequera/".PERIODO_ACTUAL."/".$nombreArchivo);
                                        $archivoAdjuntar = "../archivos/chequera/".PERIODO_ACTUAL."/".$nombreArchivo;
                                        $mail->AddAttachment($desarrollo.$archivoAdjuntar);
                                        //$mail->AddAttachment("../archivos/chequera/".PERIODO_ACTUAL."/".$nombreArchivo);
                                        //echo $mailDestino.'<br>';
                                        //echo "../archivos/chequera/".PERIODO_ACTUAL."/".$nombreArchivo.'<br>';
                                    }
                                    break;
                                
                                case 10:
                                    $mail->AddAttachment($desarrollo."../".$path."/".$nombreArchivo);
                                    break;

                                case 18:
                                    $subCarpeta = substr($fechaRecibo, 0, 4).'/'.substr($fechaRecibo, 5, 2);
                                    $nombreArchivo = $tipoRecibo.'_'.$numeroRecibo.'.pdf';
                                    $archivoAdjuntar = "../archivos/recibos/".$subCarpeta."/".$nombreArchivo;
                                    $mail->Subject = 'Comprobante '.$tipoRecibo.' '.$numeroRecibo;
                                    $mail->AddAttachment($desarrollo.$archivoAdjuntar);
                                    break;

                                case 21:
                                    $subCarpeta = PERIODO_ACTUAL;
                                    $nombreArchivo = $matricula.'.pdf';
                                    $archivoAdjuntar = "../archivos/seguro/".$subCarpeta."/".$nombreArchivo;
                                    $mail->AddAttachment($desarrollo.$archivoAdjuntar);
                                    break;

                                default:
                                    // code...
                                    break;
                            }
                            $mail->AddAddress($mailDestino, $destinatarioMail);
                            $mail->IsHTML(true);
                            //echo $mailDestino .' - '. $matricula .' - '. $destinatario;
                            //echo $nota;
                            if($mail->Send()) {
                                $error = 'OK';
                                $estado = 'O';
                                $cantidadEnviados += 1; 
                                $totalEnviosDiarios += 1;
                            }else{
                                $error = $mail->ErrorInfo;
                                $estado = 'E';
                                var_dump($error);
                                echo '<br>';
                            }
                        } else {
                            $estado = 'E';
                            if ($idEnvio == 1 && $totalDeudaActualizada == 0) {
                                $error = 'OK - SIN DEUDA';
                                $estado = 'O';
                            }
                        }
                        $res = $envioMailDiarioLogic->guardarEnvioColegiado($idEnvio, $idColegiado, $idReferencia, $error, $estado, $mailDestino);
                        
                        switch ($idEnvio) {
                            case 4: //notificacion titulos para retirar
                                $envioMailTituloLogic->guardarTituloEnviadoColegiado($idEnvioMailTitulo, $idColegiado);
                                break;
                            
                            default:
                                break;
                        }
                    }
                }
            }
        }
        echo 'Envio: '.$idEnvio.' - '.$detalle.' - ('.$rango.') - enviados: '.$cantidadEnviados.'<br>';
    }
    
    if ($totalEnviosDiarios > 0) {
        echo 'Finalizó el envío de notificaciones. Total mails enviados: '.$totalEnviosDiarios.'<br>';
    } else {
        echo 'No se enviaron notificaciones.<br>';
    }
    ?>
<?php
}
echo date('H:i:s').'<br>';