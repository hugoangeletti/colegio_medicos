<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiacionAnualLogic.php');
$colegiacionAnualLogic = new colegiacionAnualLogic();
require_once ('../../dataAccess/colegiadoLogic.php');

set_time_limit(0);
$periodo = $_POST['periodo'];
$resColegiacion = $colegiacionAnualLogic->obtenerColegiacionAnualPorPeriodo($periodo, NULL);
if ($resColegiacion['estado']) {
    if (sizeof($resColegiacion['datos']) == 2) {
        $resCuotasColegiacion = $colegiacionAnualLogic->obtenerColegiacionAnualCuotas($periodo);
        if ($resCuotasColegiacion['estado']) {
            $cuotasLiquidar = $resCuotasColegiacion['datos'];
            //obtengo las cantidades a procesar
            $cantidadProcesar = 0;
            $cantidadProcesados = 0;
            $cantidadProcesadosAntiguedad1 = 0;
            $ImporteTotalAntiguedad1 = 0;
            $cantidadProcesadosAntiguedad2 = 0;    
            $ImporteTotalAntiguedad2 = 0;
            //obtener los colegiados a procesar
            $fechaCalculoAntiguedad = $periodo.'-05-31';
            $colegiadoLogic = new colegiadoLogic();
            $resultado = $colegiadoLogic->obtenerColegiadoParaLiquidacion($periodo, $fechaCalculoAntiguedad);
            if ($resultado['estado']) {
                foreach ($resultado['datos'] as $datos) {
                    $idColegiado = $datos['idColegiado'];
                    $fechaTitulo = $datos['fechaTitulo'];
                    $estado = $datos['estado'];
                    $antiguedad = $datos['antiguedad'];

                    //$descuentaPagos = $_POST['descuentaPagos'];
                    $descuentaPagos = 'N';
                    if ($descuentaPagos == "S") {
                        $cuotasVerificar = $_POST['cuotasVerificar'];
                    } else {
                        $cuotasVerificar = "";
                    }

                    switch ($antiguedad) {
                        case 1:
                            $datosColegiacion = $resColegiacion['datos'][0];
                            break;
                        case 2:
                            $datosColegiacion = $resColegiacion['datos'][1];
                            break;
                        default:
                            $datosColegiacion = array();
                            break;
                    }

                    $resGenerar = $colegiacionAnualLogic->generarColegiacionAnual($idColegiado, $antiguedad, $estado, $datosColegiacion, $descuentaPagos, $cuotasVerificar, NULL, $cuotasLiquidar);
                    if ($resGenerar['estado']) {
                        $cantidadProcesados++;
                        switch ($antiguedad) {
                            case 1:
                                $cantidadProcesadosAntiguedad1++;
                                $ImporteTotalAntiguedad1 += $datosColegiacion['importe'];
                                break;
                            case 2:
                                $cantidadProcesadosAntiguedad2++;    
                                $ImporteTotalAntiguedad2 += $datosColegiacion['importe'];
                                break;
                            default:
                                # code...
                                break;
                        }
                    }

                }
            }
        }
    }
} 

?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if (!$resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../liquidacion_colegiacion_anual_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../liquidacion_colegiacion_anual_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="OK">
            <input type="hidden"  name="cantidadProcesar" id="cantidadProcesar" value="<?php echo $cantidadProcesar;?>">
            <input type="hidden"  name="cantidadProcesados" id="cantidadProcesados" value="<?php echo $cantidadProcesados;?>">
            <input type="hidden"  name="cantidadProcesarAntiguedad1" id="cantidadProcesarAntiguedad1" value="<?php echo $cantidadProcesarAntiguedad1;?>">
            <input type="hidden"  name="cantidadProcesarAntiguedad2" id="cantidadProcesarAntiguedad2" value="<?php echo $cantidadProcesarAntiguedad2;?>">
            <input type="hidden"  name="cantidadProcesados" id="cantidadProcesados" value="<?php echo $cantidadProcesados;?>">            
            <input type="hidden"  name="cantidadProcesadosAntiguedad1" id="cantidadProcesadosAntiguedad1" value="<?php echo $cantidadProcesadosAntiguedad1;?>">
            <input type="hidden"  name="cantidadProcesadosAntiguedad2" id="cantidadProcesadosAntiguedad2" value="<?php echo $cantidadProcesadosAntiguedad2;?>">
            <input type="hidden"  name="ImporteTotalAntiguedad1" id="ImporteTotalAntiguedad1" value="<?php echo $ImporteTotalAntiguedad1;?>">
            <input type="hidden"  name="ImporteTotalAntiguedad2" id="ImporteTotalAntiguedad2" value="<?php echo $ImporteTotalAntiguedad2;?>">
            <input type="hidden"  name="antiguedad" id="antiguedad" value="<?php echo $antiguedad;?>">
            <input type="hidden"  name="importe" id="importe" value="<?php echo $importe;?>">
            <input type="hidden"  name="vencimientoCuotaUno" id="vencimientoCuotaUno" value="<?php echo $vencimientoCuotaUno;?>">
            <input type="hidden"  name="pagoTotal" id="pagoTotal" value="<?php echo $pagoTotal;?>">
            <input type="hidden"  name="vencimientoPagoTotal" id="vencimientoPagoTotal" value="<?php echo $vencimientoPagoTotal;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

