<?php
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/informeContableLogic.php');

set_time_limit(0);

if (isset($_GET['periodo']) && $_GET['periodo'] >= 2021) {
    $periodo = $_GET['periodo'];
} else {
    echo "PERIODO ERRONEO";
    exit;
}
$mes = '10';
if (isset($_GET['mes']) && $_GET['mes'] >= '01' && $_GET['mes'] <= '12') {
    $mes = $_GET['mes'];
} else {
    echo "MES ERRONEO";
    exit;
}

$carpeta = "../archivos/cobranza/".$periodo;

$mesProcesado = $periodo.'-'.$mes;
if ($mes < '05') {
    $mesProcesado = ($periodo+1).'-'.$mes;
}

echo 'Inicio -> '.date('Y-m-d H:i:s').'<br>';
$origen = 'Cajas';
echo $mesProcesado.'<br>';
$resInforme = generarInformeContable($periodo, $mesProcesado, $origen);
if ($resInforme['estado']) {
    $idInforme = $resInforme['idInforme'];
    $archivo = $carpeta."/".$mesProcesado." ".$origen."/Vitems.txt";
    if (file_exists($archivo)) {
        $arrLineas=array();
        $arrLineas = array(file( $archivo ));
        $cantidadLineas=sizeof($arrLineas[0]);
        $cantidadInsert = 0;
        for ($i=0; $i<$cantidadLineas; $i++) {
            $lineaBejerman= $arrLineas[0][$i];
            $tipoComprobante = substr($lineaBejerman, 0, 3);
            $numeroComprobante = substr($lineaBejerman, 3, 13);
            $fechaPago = substr($lineaBejerman, 24,8);
            $cliente = substr($lineaBejerman, 32, 6);
            $codigoC = substr($lineaBejerman, 38, 1);
            $concepto = substr($lineaBejerman, 39, 23);
            $detalle = utf8_encode(substr($lineaBejerman, 94, 50));
            $importe = substr($lineaBejerman, 144, 16);
            $importe = $importe * (-1);

            $resInformeDetalle = agregarInformeDetalle($idInforme, $tipoComprobante, $numeroComprobante, $fechaPago, $cliente, $codigoC, $concepto, $detalle, $importe, $lineaBejerman);
            if (!$resInformeDetalle['estado']) {
                echo 'NO SE AGREGO LA LINEA'.$lineaBejerman.'<br>';
                echo $tipoComprobante.' - '.$numeroComprobante.' - '.$fechaPago.' - '.$cliente.' - '.$codigoC.' - '.$concepto.' - '.$detalle.' - '.$importe.'<br>';
                echo '<br>';
            } else {
                $cantidadInsert ++;
            }            
        }
        echo $origen.'<br>';
        echo 'A procesar: '.$cantidadLineas.'<br>';
        echo 'Agregadas: '.$cantidadInsert.'<br>';
    } else {
        echo $archivo." -> NO EXISTE<BR>";
    }
} else {
    echo $resInforme['mensaje'].'<br>';
}

$origen = 'MediosDePago';
$resInforme = generarInformeContable($periodo, $mesProcesado, $origen);
if ($resInforme['estado']) {
    $idInforme = $resInforme['idInforme'];
    $archivo = $carpeta."/".$mesProcesado." ".$origen."/Vitems.txt";
    if (file_exists($archivo)) {
        $arrLineas=array();
        $arrLineas = array(file( $archivo ));
        $cantidadLineas=sizeof($arrLineas[0]);
        $cantidadInsert = 0;
        for ($i=0; $i<$cantidadLineas; $i++) {
            $lineaBejerman= $arrLineas[0][$i];
            $tipoComprobante = substr($lineaBejerman, 0, 3);
            $numeroComprobante = substr($lineaBejerman, 3, 13);
            $fechaPago = substr($lineaBejerman, 24,8);
            $cliente = substr($lineaBejerman, 32, 6);
            $codigoC = substr($lineaBejerman, 38, 1);
            $concepto = substr($lineaBejerman, 39, 23);
            $detalle = utf8_encode(substr($lineaBejerman, 94, 50));
            $importe = substr($lineaBejerman, 144, 16);
            $importe = $importe * (-1);

            $resInformeDetalle = agregarInformeDetalle($idInforme, $tipoComprobante, $numeroComprobante, $fechaPago, $cliente, $codigoC, $concepto, $detalle, $importe, $lineaBejerman);
            if (!$resInformeDetalle['estado']) {
                echo 'NO SE AGREGO LA LINEA'.$lineaBejerman.'<br>';
                echo $tipoComprobante.' - '.$numeroComprobante.' - '.$fechaPago.' - '.$cliente.' - '.$codigoC.' - '.$concepto.' - '.$detalle.' - '.$importe.'<br>';
                echo '<br>';
            } else {
                $cantidadInsert ++;
            }            
        }
        echo $origen.'<br>';
        echo 'A procesar: '.$cantidadLineas.'<br>';
        echo 'Agregadas: '.$cantidadInsert.'<br>';
    } else {
        echo $archivo." -> NO EXISTE<BR>";
    }
} else {
    echo $resInforme['mensaje'].'<br>';
}

$origen = 'Liquidacion';
$resInforme = generarInformeContable($periodo, $mesProcesado, $origen);
if ($resInforme['estado']) {
    $idInforme = $resInforme['idInforme'];
    $archivo = $carpeta."/".$mesProcesado." ".$origen."/Vitems.txt";
    if (file_exists($archivo)) {
        $arrLineas=array();
        $arrLineas = array(file( $archivo ));
        $cantidadLineas=sizeof($arrLineas[0]);
        $cantidadInsert = 0;
        for ($i=0; $i<$cantidadLineas; $i++) {
            $lineaBejerman= $arrLineas[0][$i];
            $tipoComprobante = substr($lineaBejerman, 0, 3);
            $numeroComprobante = substr($lineaBejerman, 3, 13);
            $fechaPago = substr($lineaBejerman, 24,8);
            $cliente = substr($lineaBejerman, 32, 6);
            $codigoC = substr($lineaBejerman, 38, 1);
            $concepto = substr($lineaBejerman, 39, 23);
            $detalle = utf8_encode(substr($lineaBejerman, 94, 50));
            $importe = substr($lineaBejerman, 144, 16);
            $importe = $importe * (-1);

            $resInformeDetalle = agregarInformeDetalle($idInforme, $tipoComprobante, $numeroComprobante, $fechaPago, $cliente, $codigoC, $concepto, $detalle, $importe, $lineaBejerman);
            if (!$resInformeDetalle['estado']) {
                echo 'NO SE AGREGO LA LINEA'.$lineaBejerman.'<br>';
                echo $tipoComprobante.' - '.$numeroComprobante.' - '.$fechaPago.' - '.$cliente.' - '.$codigoC.' - '.$concepto.' - '.$detalle.' - '.$importe.'<br>';
                echo '<br>';
            } else {
                $cantidadInsert ++;
            }            
        }
        echo $origen.'<br>';
        echo 'A procesar: '.$cantidadLineas.'<br>';
        echo 'Agregadas: '.$cantidadInsert.'<br>';
    } else {
        echo $archivo." -> NO EXISTE<BR>";
    }
} else {
    echo $resInforme['mensaje'].'<br>';
}

$origen = 'Bajas';
$resInforme = generarInformeContable($periodo, $mesProcesado, $origen);
if ($resInforme['estado']) {
    $idInforme = $resInforme['idInforme'];
    $archivo = $carpeta."/".$mesProcesado." ".$origen."/Vitems.txt";
    if (file_exists($archivo)) {
        $arrLineas=array();
        $arrLineas = array(file( $archivo ));
        $cantidadLineas=sizeof($arrLineas[0]);
        $cantidadInsert = 0;
        for ($i=0; $i<$cantidadLineas; $i++) {
            $lineaBejerman= $arrLineas[0][$i];
            $tipoComprobante = substr($lineaBejerman, 0, 3);
            $numeroComprobante = substr($lineaBejerman, 3, 13);
            $fechaPago = substr($lineaBejerman, 24,8);
            $cliente = substr($lineaBejerman, 32, 6);
            $codigoC = substr($lineaBejerman, 38, 1);
            $concepto = substr($lineaBejerman, 39, 23);
            $detalle = utf8_encode(substr($lineaBejerman, 94, 50));
            $importe = substr($lineaBejerman, 144, 16);
            $importe = $importe * (-1);

            $resInformeDetalle = agregarInformeDetalle($idInforme, $tipoComprobante, $numeroComprobante, $fechaPago, $cliente, $codigoC, $concepto, $detalle, $importe, $lineaBejerman);
            if (!$resInformeDetalle['estado']) {
                echo 'NO SE AGREGO LA LINEA'.$lineaBejerman.'<br>';
                echo $tipoComprobante.' - '.$numeroComprobante.' - '.$fechaPago.' - '.$cliente.' - '.$codigoC.' - '.$concepto.' - '.$detalle.' - '.$importe.'<br>';
                echo '<br>';
            } else {
                $cantidadInsert ++;
            }            
        }
        echo $origen.'<br>';
        echo 'A procesar: '.$cantidadLineas.'<br>';
        echo 'Agregadas: '.$cantidadInsert.'<br>';
    } else {
        echo $archivo." -> NO EXISTE<BR>";
    }
} else {
    echo $resInforme['mensaje'].'<br>';
}

echo 'Fin -> '.date('Y-m-d H:i:s').'<br>';