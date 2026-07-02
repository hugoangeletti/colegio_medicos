<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cajaDiariaLogic.php');
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');

$continua = TRUE;
$mensaje = "";
$cerradas = FALSE;
if (isset($_GET['estado']) && $_GET['estado'] == "cerrada") {
    $cerradas = TRUE;
}
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $id = explode('_', $_GET['id']);
    $idCajaDiaria = $id[0];
    $idCajaDiariaMovimiento = $id[1];
} else {
    $continua = FALSE;
    $mensaje .= "Falta id. ";
}


if ($continua) {
    $cajaDiariaLogic = new cajaDiariaLogic();
    $resRecibo = $cajaDiariaLogic->obtenerCajaDiariaMovimientoPorId($idCajaDiariaMovimiento);
    if ($resRecibo['estado']) {
        $recibo = $resRecibo['datos']; 
        $tipoRecibo = $recibo['tipoRecibo'];
        $numeroRecibo = $recibo['numeroRecibo'];
        $fechaPago = $recibo['fechaPago'];

        $subCarpeta = substr($fechaPago, 0, 4).'/'.substr($fechaPago, 5, 2);
        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= '/'.PATH_PDF.'/archivos/recibos/'.$subCarpeta.'/';
        $nombreArchivo = $camino.$tipoRecibo.'_'.$numeroRecibo.'.pdf';

        //echo $nombreArchivo;
        if (file_exists($nombreArchivo)) {
            unlink($nombreArchivo);
            //echo 'borrado';
        } else {
            //echo 'No encontrado';
        }
        //exit;
    }
    $resultado['mensaje'] = $resRecibo['mensaje'];
    $resultado['estado'] = $resRecibo['estado'];
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['estado'] = FALSE;
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="<?php if ($cerradas) { echo '../cajadiaria_movimientos.php?id='.$idCajaDiaria; } else { echo '../cajadiaria.php'; }?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        <input type="hidden"  name="idCajaDiaria" id="idCajaDiaria" value="<?php echo $idCajaDiaria; ?>">
    </form>
</body>


