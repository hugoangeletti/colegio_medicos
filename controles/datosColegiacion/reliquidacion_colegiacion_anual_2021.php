<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiacionAnualLogic.php');
$colegiacionAnualLogic = new colegiacionAnualLogic();
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();

set_time_limit(0);
$periodo = 2021;
$cuotaInicio = 4;
$cantidadProcesados = 0;
$cantidadNoProcesados = 0;
/*
- $colegiacionAnualLogic->obtenerCuotasAgregar($periodo) - listo
- $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualPorPeriodo($periodo)
- recorrer colegiados
- agregar cuotas a la liquidacion del colegiado
- actualizar el importe del periodo
- agregar el pagototal con el descuento del 10%, restando previamente el importe abonado de las 3 cuotas 

*/
$resColegiacion = $colegiacionAnualLogic->obtenerCuotasAgregar($periodo, $cuotaInicio);
if ($resColegiacion['estado']) {
    $colegiacionMenos5 = array();
    $colegiacionMas5 = array();
    foreach ($resColegiacion['datos'] as $colegiacion) {
        if ($colegiacion['antiguedad'] == 1) {
            array_push($colegiacionMenos5, $colegiacion);
        }
        if ($colegiacion['antiguedad'] == 2) {
            array_push($colegiacionMas5, $colegiacion);
        }
    }
    /*
    print_r($colegiacionMenos5);
    echo '<br>';
    print_r($colegiacionMas5);
    */
    $resColegiacionTotal = $colegiacionAnualLogic->obtenerPagoTotal($periodo);
    if ($resColegiacionTotal['estado']) {
        $colegiacionTotal = $resColegiacionTotal['datos'];

        $resColegiados = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualPorPeriodo($periodo);
        if ($resColegiados['estado']) {
            foreach ($resColegiados['datos'] as $colegiado) {
                $idColegiadoDeudaAnual = $colegiado['idColegiadoDeudaAnual'];
                $antiguedad = $colegiado['antiguedad'];
                echo 'Id -> '.$idColegiadoDeudaAnual.'<br>';
                $listaCuotas = array();
                $pagoTotal = array();
                // inserto las cuotas segun la antiguedad
                switch ($antiguedad) {
                    case '1':
                        $listaCuotas = $colegiacionMenos5;
                        $pagoTotal = $colegiacionTotal[0];
                        break;
                    
                    case '2':
                        $listaCuotas = $colegiacionMas5;
                        $pagoTotal = $colegiacionTotal[1];
                        break;
                    
                    default:
                        break;
                }
                if (sizeof($listaCuotas) > 0) {
                    // agregamos las cuotas por IdColegiadoDeudaAnual y Cuota
                    $resultado = $colegiacionAnualLogic->regenerarColegiacionAnual_2021($idColegiadoDeudaAnual, $listaCuotas, $pagoTotal);
                    if ($resultado['estado']) {
                        $cantidadProcesados++;
                    } else {
                        $cantidadNoProcesados++;
                        echo 'ERROR: no inserto cuotas. -> '.$idColegiadoDeudaAnual.'<br>';
                        var_dump($resultado);   
                    }
                } else {
                        $cantidadNoProcesados++;
                    echo 'ERROR: no encontro antiguedad. -> '.$idColegiadoDeudaAnual.'<br>';
                }
            }
        } else {
            $resultado = $resColegiados;
        }
    } else {
        $resultado = $resColegiacionTotal;
    }
} else {
    $resultado = $resColegiacion;
} 
echo 'Procesados -> '.$cantidadProcesados;
echo '<br>';
echo 'No Procesados -> '.$cantidadNoProcesados;
echo '<br>';
var_dump($resultado);
exit;    
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

