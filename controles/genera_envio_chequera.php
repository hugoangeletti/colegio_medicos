<?php
require_once (__DIR__) . '/../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once (__DIR__) . '/../dataAccess/funcionesConector.php';
require_once (__DIR__) . '/../dataAccess/funcionesPhp.php';
require_once (__DIR__) . '/../dataAccess/colegiadoDeudaAnualLogic.php';
//require_once (__DIR__) . '/../dataAccess/colegiadoEnvioChequeraLogic.php';
require_once (__DIR__) . '/../dataAccess/envioMailLogic.php';
require_once (__DIR__) . '/../dataAccess/notificacionNotaLogic.php';
set_time_limit(0);

$periodoActual = date('Y');
if (date('m')<6) {
    $periodoActual -= 1;
}

$resEnvioMail = $envioMailLogic->obtenerEnvioDisponible();
if ($resEnvioMail['estado']) {
    $envioMail = $resEnvioMail['datos'];
    $elMes = obtenerMes(date('m'));
    $laFecha = 'La Plata, '.date('d').' de '.$elMes.' de '.date('Y').'.-';
    $idEnvioMail = $envioMail['idEnvioMail'];
    $idNotificacion = $envioMail['idNotificacion'];
    $rango = $envioMail['rango'];

    $resNota = $notificacionNotaLogic->obtenerNotificacionNotaPorIdNotificacion($idNotificacion);
    if ($resNota['estado']) {
        $resChequera = $colegiadoDeudaAnualLogic->obtenerColegiadoEnvioChequera($periodoActual, $rango);
        if ($resChequera['estado']){
            foreach ($resChequera['datos'] as $fila) {
                $nota = $resNota['texto'];
                //guardo el envio en enviomailchequera y genero el pdf
                $idColegiadoDeudaAnual = $fila['idColegiadoDeudaAnual'];
                $matricula = $fila['matricula'];
                $apellido = $fila['apellido'];
                $nombre = $fila['nombre'];
                $mailDestino = $fila['mail'];
                $idDebitoTarjeta = $fila['idDebitoTarjeta'];
                $idDebitoAgremiacion = $fila['idDebitoAgremiacion'];
                $idDebitoCbu = $fila['idDebitoCbu'];

                $mailDestino = 'sistemas@colmed1.org.ar'; //para las pruebas, sacar en produccion

                if ($fila['sexo'] == 'M') {
                    $elColegiado = "Estimado Dr. ".$apellido.", ".$nombre;
                } else {
                    $elColegiado = "Estimada Dra. ".$apellido.", ".$nombre;
                }
                $nota = str_replace('{0}', $elColegiado, $nota);
                $nota = str_replace('{1}', $matricula, $nota);

                $conDebito = FALSE;
                $textoDebito = "Ud. puede abonar con d&eacute;bito autom&aacute;tico con Tarjeta de 
                    Cr&eacute;dito VISA &oacute; CBU. 
                    Para adherirse comun&iacute;quese llamando al 0221 4256311 / 4454316. De lunes a viernes
                    de 8 a 16 hs.<br>
                    <b>Otros medios de pago:</b> Red Link &HorizontalLine; Home Banking &HorizontalLine; 
                    Pago Mis Cuentas &HorizontalLine; ProvinciaNet &HorizontalLine; RapiPago &HorizontalLine; 
                    Pago Facil. ";
                if ($idDebitoAgremiacion > 0) {
                    //tiene debito por agremiacion, se le envia solo la nota, indicando que tiene debito automatico
                    $conDebito = TRUE;
                    $textoDebito = "Ud. se encuentra adherido al d&eacute;bito por <b>".$fila['lugarPago']."</b>";
                }
                if ($idDebitoTarjeta > 0) {
                    //tiene debito por tarjeta de credito, se le envia solo la nota, indicando que tiene debito automatico
                    $conDebito = TRUE;
                    if ($fila['tipoTarjeta'] == 'C') {
                        $tipoTarjeta = "Cr&eacute;dito";
                    } else {
                        $tipoTarjeta = "D&eacute;bito";
                    }
                    $textoDebito = "Ud. se encuentra adherido al d&eacute;bito por <b>Tarjeta de ".$tipoTarjeta."</b>";
                    if (isset($fila['banco']) && $fila['banco'] <> "") {
                        $textoDebito .= " del <b>".$fila['banco']."</b>";
                    } else {
                        $textoDebito .= ".-";
                    }
                }
                if ($idDebitoCbu > 0) {
                    //tiene debito por cbu, se le envia solo la nota, indicando que tiene debito automatico
                    $conDebito = TRUE;
                    $textoDebito = "Ud. se encuentra adherido al d&eacute;bito por CBU del <b>".$fila['bancoCbu']."</b>";
                }
                
                //busco las cuotas del perido para imprimir
                $resCuotas = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualCuotas($idColegiadoDeudaAnual);
                if ($resCuotas['estado']){
                    //imprimo las cuotas
                    $i = 3;
                    foreach ($resCuotas['datos'] as $cuota) {
                        //reemplazo los valores en al nota
                        $nota = str_replace('{2}', '$ '.number_format($cuota['importe'], 2, ',', '.'), $nota);
                        $nota = str_replace('{'.$i.'}', cambiarFechaFormatoParaMostrar($cuota['vencimiento']), $nota);
                        $i += 1;
                    }
                    
                    //imprimo el pago total
                    $resTotal = $colegiadoDeudaAnualLogic->obtenerPagoTotalPorIdDeudaAnual($idColegiadoDeudaAnual);
                    if ($resTotal['estado']){
                        $cuota = $resTotal['datos'];
                        $nota = str_replace('{13}', '$ '.number_format($cuota['importe'], 2, ',', '.'), $nota);
                        $nota = str_replace('{14}', cambiarFechaFormatoParaMostrar($cuota['fechaVencimiento']), $nota);
                        
                        $nota = str_replace('{15}', $textoDebito, $nota);
                        
                        //envio el mail
                        $destinatario = $fila['apellido'].', '.$fila['nombre'];
                        require_once '../PHPMailer/class.phpmailer.php';
                        require_once '../PHPMailer/class.smtp.php';

                        $mail = new PHPMailer();
                        $mail->IsSMTP();
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = "ssl";
                        $mail->Host = "mail.colmed1.org.ar";
                        $mail->Port = 465;
                        $mail->Username = "sistemas@colmed1.org.ar";
                        $mail->Password = "@sistemas1";

                        $mail->From = "tesoreria@colmed1.org.ar";
                        $mail->FromName = "Colegio de Medicos. Distrito I";
                        $mail->Subject = "Notificacion de Tesoreria";
                        $mail->AltBody = "";
                        $mail->MsgHTML($nota);
                        if (!$conDebito) {
                            //envio con pdf
                            $nombreArchivo = 'Colegiacion'.$periodoActual.'_Matricula_'.$matricula.'.pdf';
                            $mail->AddAttachment("../archivos/cuotas/".$periodoActual."/".$nombreArchivo);
                        } 
                        $mail->AddAddress($mailDestino, $destinatario);
                        $mail->IsHTML(true);
                        //echo $mailDestino .' - '. $matricula .' - '. $destinatario;
                        if($mail->Send()) {
                            $mailEnviado = TRUE;
                            echo 'Enviado: '.$mailDestino;
                        }else{
                            $mailEnviado = FALSE;
                            echo 'Mail no enviado: '.$mailDestino;
                        }

                        //echo $nota.'<br><br>';
                        $resGuardar = $colegiadoEnvioChequeraLogic->guardarEnvioChequera($idEnvioMail, $idColegiadoDeudaAnual);
                        if (!$resGuardar['estado']) {
                            echo $resGuardar['mensaje'];
                        } else {
                            //echo 'Guardo: '.$idEnvioMail.' - '.$idColegiadoDeudaAnual;
                        }
                    } else {
                        echo $resTotal['mensaje'];
                    }
                } else {
                    echo $resCuotas['mensaje'];
                }
            }
        } else {
            echo $resChequera['mensaje'];
        }
    } else {
        echo $resNota['mensaje'];
    }
} else {
    echo $resEnvioMail['mensaje'];
}
