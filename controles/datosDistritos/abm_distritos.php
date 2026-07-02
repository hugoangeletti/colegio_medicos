<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/distritoLogic.php');
$distritoLogic = new distritoLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_POST['idDistrito']) && $_POST['idDistrito'] > 1) {
    $idDistrito = $_POST['idDistrito'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje .= "Falta idDistrito - ";
}
if (isset($_POST['presidente']) && $_POST['presidente'] <> "") {
    $presidente = $_POST['presidente'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Falta presidente - ";
}
if (isset($_POST['domicilio']) && $_POST['domicilio'] <> "") {
    $domicilio = $_POST['domicilio'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Falta domicilio - ";
}
if (isset($_POST['mail']) && $_POST['mail'] <> "") {
    $mail = $_POST['mail'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Falta mail -";
}
if (isset($_POST['pagina']) && $_POST['pagina'] <> "") {
    $pagina = $_POST['pagina'];
} else {
    $pagina = NULL;
}

if ($continua){
    $resultado = $distritoLogic->editarDistrito($idDistrito, $presidente, $domicilio, $mail, $pagina);
    if($resultado['estado']) {
        $tipoMensaje = 'alert alert-success';
    } else {
        $tipoMensaje = 'alert alert-danger';
    }
    $mensaje = $resultado['mensaje'];
}

?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../distritos_listado.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../distritos_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idDistrito" id="idDistrito" value="<?php echo $idDistrito;?>">
            <input type="hidden"  name="presidente" id="presidente" value="<?php echo $presidente;?>">
            <input type="hidden"  name="domicilio" id="domicilio" value="<?php echo $domicilio;?>">
            <input type="hidden"  name="mail" id="mail" value="<?php echo $mail;?>">
            <input type="hidden"  name="pagina" id="pagina" value="<?php echo $pagina;?>">
        </form>
    <?php
    }
    ?>
</body>

