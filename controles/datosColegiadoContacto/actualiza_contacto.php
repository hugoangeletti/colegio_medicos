<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../../dataAccess/colegiadoLogic.php');

$idColegiado = $_POST['idColegiado'];
$continua = TRUE;

if (isset($_POST['telefonoFijo']) && isset($_POST['telefonoMovil']) && isset($_POST['mail'])) {
    $telefonoFijo = $_POST['telefonoFijo'];
    $telefonoMovil = $_POST['telefonoMovil'];
    $mail = $_POST['mail'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos en el expediente, verifique.";
}

if ($continua){
    $accion = 'modificar';
    $resultado = $colegiadoContactoLogic->agregarColegiadoContacto($idColegiado, $telefonoFijo, $telefonoMovil, $mail, $accion);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../actualizar_contacto.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
            <input type="hidden"  name="telefonoFijo" id="telefonoFijo" value="<?php echo $telefonoFijo;?>">
            <input type="hidden"  name="telefonoMovil" id="telefonoMovil" value="<?php echo $telefonoMovil;?>">
            <input type="hidden"  name="mail" id="mail" value="<?php echo $mail;?>">
        </form>
    <?php
    }
    ?>
</body>

