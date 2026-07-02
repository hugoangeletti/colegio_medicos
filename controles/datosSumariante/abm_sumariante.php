<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/sumarianteLogic.php');
$sumarianteLogic = new sumarianteLogic();

if (isset($_POST['idSumariante'])) {
    $idSumariante = $_POST['idSumariante'];
    $accion = $_POST['accion'];
} else {
    $idSumariante = NULL;
    $accion = 1;
}

$continua = TRUE;
if (isset($_POST['idColegiado']) && isset($_POST['estado']) && isset($_POST['colegiado_buscar'])) {
    $colegiado_buscar = $_POST['colegiado_buscar'];
    $estado = $_POST['estado'];
    $idColegiado = $_POST['idColegiado'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos en el expediente, verifique.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $sumarianteLogic->agregarSumariante($idColegiado, $estado);
            break;
        case '2':
            $resultado = $sumarianteLogic->borrarSumariante($idSumariante);
            break;
        case '3':
            $resultado = $sumarianteLogic->editarSumariante($idSumariante, $idColegiado, $estado);
            break;
        default:
            break;
    }

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
        <form name="myForm"  method="POST" action="../sumariante_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../sumariante_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idSumariante" id="idSumariante" value="<?php echo $idSumariante;?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

