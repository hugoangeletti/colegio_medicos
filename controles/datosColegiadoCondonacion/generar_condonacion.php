<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/condonacionLogic.php');
$condonacionLogic = new condonacionLogic();
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../../dataAccess/colegiadoPlanPagoLogic.php');

$continua = TRUE;

if (isset($_POST['idColegiado']) && isset($_POST['idResponsable']) && isset($_POST['idTipoCondonacion']) 
        && (!empty($_POST['lasCuotas']) || !empty($_POST['lasCuotasPP']))) {
        //&& ($_POST['todas'] == 'S' || ($_POST['todas'] == 'N' 
        //        && (!empty($_POST['lasCuotas']) || !empty($_POST['lasCuotasPP']))))) {
    $idColegiado = $_POST['idColegiado'];
    $idResponsable = $_POST['idResponsable'];
    $idTipoCondonacion = $_POST['idTipoCondonacion'];
    $observaciones = $_POST['observaciones'];
    //$todas = $_POST['todas'];
    $todas = 'N';
    if (!empty($_POST['lasCuotas'])) {
        $lasCuotas = $_POST['lasCuotas'];
    } else {
        $lasCuotas = array();
    }
    if (!empty($_POST['lasCuotasPP'])) {
        $lasCuotasPP = $_POST['lasCuotasPP'];
    } else {
        $lasCuotasPP = array();
    }
} else {
    $mensaje = 'Faltan datos';
    $continua = FALSE;
}

if ($continua){
    $resultado = $condonacionLogic->agregarColegiadoCondonacion($idColegiado, $idResponsable, $idTipoCondonacion, $observaciones, $todas, $lasCuotas, $lasCuotasPP);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS. ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        $idCondonacion = $resultado['idCondonacion'];
    ?>
        <!--<form name="myForm"  method="POST" action="imprimir_condonacion.php?idCondonacion=<?php //echo $idCondonacion; ?>">-->
        <form name="myForm"  method="POST" action="../tesoreria_condonacion.php">
            <input type="hidden" name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden" name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden" name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_condonacion_nueva.php.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
        </form>
    <?php
    }
    ?>
</body>

