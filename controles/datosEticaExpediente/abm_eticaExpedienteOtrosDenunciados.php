<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idEticaExpedienteOtroDenunciado = $_GET['id'];
    $resExpediente = $eticaExpedienteLogic->obtenerOtroDenunciadoPorId($idEticaExpedienteOtroDenunciado);
    if ($resExpediente['estado']) {
        $idEticaExpediente = $resExpediente['datos']['idEticaExpediente'];
        $accion = 2;
    } else {
        $continua = FALSE;
        $mensaje .= "Falta campo idEticaExpediente";
    }
} else {
    $accion = 1;
    if (isset($_POST['idEticaExpediente']) && $_POST['idEticaExpediente']) {
        $idEticaExpediente = $_POST['idEticaExpediente'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta campo idEticaExpediente";
    }
    if (isset($_POST['idColegiado']) && $_POST['idColegiado']) {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta campo idColegiado";
    }
}

if ($continua){
    switch ($accion) {
        case '1':
            $resultado = $eticaExpedienteLogic->agregarOtrosDenunciados($idEticaExpediente, $idColegiado);
            break;

        case '2':
            $resultado = $eticaExpedienteLogic->borrarOtrosDenunciados($idEticaExpedienteOtroDenunciado);
            break;
        
        default:
            // code...
            break;
    }
}

//var_dump($resultado);
//exit;
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        $action = "../eticaExpedienteOtrosDenunciados.php?id=".$idEticaExpediente;
    ?>
        <form name="myForm"  method="POST" action="<?php echo $action; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../eticaExpedienteOtrosDenunciados_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente;?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
        </form>
    <?php
    }
    ?>
</body>

