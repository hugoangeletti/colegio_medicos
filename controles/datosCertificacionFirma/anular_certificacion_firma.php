<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/constanciaFirmaiaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCertificacionFirma = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta id. ";
}


if ($continua) {
    $cajaDiariaLogic = new cajaDiariaLogic();
    $resultado = $cajaDiariaLogic->anularReciboCajaDiaria($idCajaDiariaMovimiento);
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
    <form name="myForm"  method="POST" action="../certificacion_firma.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        <input type="hidden"  name="idCajaDiaria" id="idCajaDiaria" value="<?php echo $idCajaDiaria; ?>">
    </form>
</body>


