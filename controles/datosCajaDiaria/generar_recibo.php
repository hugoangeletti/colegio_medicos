<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cursos_pdo.php');
require_once ('../../dataAccess/cajaDiariaLogic.php');
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../../dataAccess/colegiadoPlanPagoLogic.php');
require_once ('../../dataAccess/constanciaFirmaLogic.php');

$continua = TRUE;
$mensaje = "";
$resultado = NULL;
/*
if (isset($_POST['listaIdMesaEntrada']) && $_POST['listaIdMesaEntrada'] <> "") {
    $listaIdMesaEntrada = $_POST['listaIdMesaEntrada'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta listaIdMesaEntrada. ";
}
*/

$tipoRecibo = NULL;
if (isset($_POST['tipoRecibo']) && $_POST['tipoRecibo']) {
    $tipoRecibo = $_POST['tipoRecibo'];
    $generarReciboPP = NULL;
    if ($tipoRecibo == "CUOTAS") {
        if (isset($_POST['generarReciboPP']) && $_POST['generarReciboPP']) {
            $generarReciboPP = $_POST['generarReciboPP'];
        }
    }
    $idColegiado = NULL;
    $idAsistente = NULL;
    if ($tipoRecibo == 'CURSOS') {
        if (isset($_POST['idAsistente']) && $_POST['idAsistente'] <> "") {
            $idAsistente = $_POST['idAsistente'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta asistente. ";
        }
    } else {
        if ($tipoRecibo == 'CUOTAS' || $tipoRecibo == 'TIPO_PAGO' || $tipoRecibo == 'ESPECIALISTAS' || $tipoRecibo == 'DEVOLUCION') {
            if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
                $idColegiado = $_POST['idColegiado'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta colegiado. ";
            }
            if ($tipoRecibo == 'DEVOLUCION') {
                if (isset($_POST['importe']) && $_POST['importe'] <> "") {
                    $importe = $_POST['importe'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta importe. ";
                }    
                $tipoPago = $_POST['idTipoPago'];
            }
        } else {
            if ($tipoRecibo == 'OTROS_INGRESOS') {
                if (isset($_POST['nombre']) && $_POST['nombre'] <> "") {
                    $nombre = $_POST['nombre'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta nombre. ";
                }            
                if (isset($_POST['cuit']) && $_POST['cuit'] <> "") {
                    $cuit = $_POST['cuit'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta cuit. ";
                }            
                if (isset($_POST['domicilio']) && $_POST['domicilio'] <> "") {
                    $domicilio = $_POST['domicilio'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta domicilio. ";
                }            
                if (isset($_POST['concepto']) && $_POST['concepto'] <> "") {
                    $concepto = $_POST['concepto'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta concepto. ";
                }            
                if (isset($_POST['importe']) && $_POST['importe'] <> "") {
                    $importe = $_POST['importe'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta importe. ";
                }            
                $tipoPago = $_POST['tipoPago'];
            } else {
                if ($tipoRecibo == 'FIRMA') {
                    $nombre = 'Certificacion de Firma';
                    $cuit = '30540780028';
                    $domicilio = 'Av.51 - 763';
                    $concepto = 'Certificaciones de Firma';
                    $tipoPago = 62;                
                    if (isset($_POST['importe']) && $_POST['importe'] <> "") {
                        $importe = $_POST['importe'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= "Falta importe. ";
                    }            
                }
            }
        }
    }

    //se verifica la forma de pago
    if ($tipoRecibo == 'FIRMA' || $tipoRecibo == 'DEVOLUCION') {
        $idFormaPago = 1;
    } else {
        if (isset($_POST['formaPago'])) {
            $idFormaPago = $_POST['formaPago'];
            if ($idFormaPago <> 1) {
                //si no es efectivo debo controlar que venga el banco y el comprobante
                if (isset($_POST['idBanco']) && $_POST['idBanco'] <> "") {
                    $idBanco = $_POST['idBanco'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta idBanco. ";                
                }
                if (isset($_POST['comprobante']) && $_POST['comprobante'] <> "") {
                    $comprobante = $_POST['comprobante'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta comprobante. ";                
                }
                if (isset($_POST['importeRecargo']) && $_POST['importeRecargo'] <> "") {
                    $intereses = $_POST['importeRecargo'];
                    if (isset($_POST['totalConRecargo']) && $_POST['totalConRecargo'] <> "") {
                        $importe = $_POST['totalConRecargo'];
                    //} else {
                    //    $continua = FALSE;
                    //    $mensaje .= "Falta totalConRecargo. ";                
                    }
                } else {
                    $intereses = "0.00";
                }
            } else {
                $idBanco = NULL;
                $comprobante = NULL;
                $intereses = "0.00";
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Falta formaPago. ";        
        }
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta tipoRecibo. ";
}

if ($tipoRecibo <> 'OTROS_INGRESOS' && $tipoRecibo <> 'FIRMA') {
    if (isset($_POST['generarRecibo']) && $_POST['generarRecibo'] <> "") {
        $generarRecibo = $_POST['generarRecibo'];
    } else {
        $generarRecibo = NULL;
    }
    if (isset($_POST['conRecargo']) && $_POST['conRecargo'] <> "") {
        $conRecargo = $_POST['conRecargo'];
    } else {
        $conRecargo = 'SI';
    }
}

if ($continua) {
    if ($tipoRecibo == 'OTROS_INGRESOS' || $tipoRecibo == 'FIRMA') {
        $cajaDiariaLogic = new cajaDiariaLogic();
        $resultado = $cajaDiariaLogic->generarReciboCajaDiariaOtrosIngresos($tipoRecibo, $nombre, $cuit, $domicilio, $concepto, $importe, $tipoPago, $idFormaPago, $idBanco, $comprobante, $intereses);
    } else {
        if ($tipoRecibo == 'DEVOLUCION') {
            $importe = $importe * (-1);
            $cajaDiariaLogic = new cajaDiariaLogic();
            $resultado = $cajaDiariaLogic->generarDevolucionCajaDiaria($idColegiado, $tipoPago, $idFormaPago, $importe);
        } else {
            $cajaDiariaLogic = new cajaDiariaLogic();
            $resultado = $cajaDiariaLogic->generarReciboCajaDiaria($idColegiado, $tipoRecibo, $generarRecibo, $generarReciboPP, $conRecargo, $idAsistente, $idFormaPago, $idBanco, $comprobante, $intereses);
        }
    }
    if ($resultado['estado']) {
        //guarda pdf
        $idCajaDiariaMovimiento = $resultado['idCajaDiariaMovimiento'];

        $pathOrigen = '../';
        include 'generar_pdf.php';
    }
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['estado'] = FALSE;
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../cajadiaria.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>


