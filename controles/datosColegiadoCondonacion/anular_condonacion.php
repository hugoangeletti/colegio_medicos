<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/condonacionLogic.php');
$condonacionLogic = new condonacionLogic();

if (isset($_POST['idCondonacion'])) {
    $idCondonacion = $_POST['idCondonacion'];
    $resultado = $condonacionLogic->anularCondonacion($idCondonacion);
} else {
    $resultado['estado'] = FALSE;
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS.";
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_condonacion.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="LA CONDONACIÓN FUE ANULADA CORRECTAMENTE">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="estadoCondonacion" id="estadoCondonacion" value="B">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_condonacion_anular.php?idCondonacion=<?php echo $idCondonacion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
        </form>
    <?php
    }
    ?>
</body>

