<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/retiroDocumentacionLogic.php');
$retiroDocumentacionLogic = new retiroDocumentacionLogic();

$accion = $_POST['accion'];
if (isset($_POST['idTipoDocumentacionRetiro'])) {
    $idTipoDocumentacionRetiro = $_POST['idTipoDocumentacionRetiro'];
} else {
    $idTipoDocumentacionRetiro = NULL;
}

$continua = TRUE;
if (isset($_POST['nombre']) && $_POST['nombre'] <> '') {
    $nombre = $_POST['nombre'];
} else {
    $continua = FALSE;
    $mensaje = "Faltan nombre.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $retiroDocumentacionLogic->agregarTipoDocumentacion($nombre);
            break;
        case '3':
            $resultado = $retiroDocumentacionLogic->editarTipoDocumentacion($idTipoDocumentacionRetiro, $nombre);
            break;
        default:
            break;
    }

} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../tipo_documentacion_retiro.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../tipo_documentacion_retiro_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idTipoDocumentacionRetiro" id="idTipoDocumentacionRetiro" value="<?php echo $idTipoDocumentacionRetiro;?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

