<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    if (isset($_POST['idColegiadoNota']) && $_POST['idColegiadoNota'] <> '') {
        $idColegiadoNota = $_POST['idColegiadoNota'];
        if (isset($_POST['nota'])) {
            $nota = $_POST['nota'];
            $accion = 3;
        } else {
            $accion = 2;
        }
    } else {
        $idColegiadoNota = NULL;
        if (isset($_POST['nota'])) {
            $nota = $_POST['nota'];
            $accion = 1;
        } else {
            $accion = -1;
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = 'Sin NOTA';
        }
    }

    switch ($accion) 
    {
        case '1':
            $colegiadoLogic = new colegiadoLogic();
            $resultado = $colegiadoLogic->agregarColegiadoNota($idColegiado, $nota);
            break;
        case '3':
            $resultado = $colegiadoLogic->editarColegiadoNota($idColegiadoNota, $nota);
            break;
        case '2':
            $resultado = eliminarColegiadoNota($idColegiadoNota);
            break;
        default:
            break;
    }
} else {
    $resultado['estado'] = FALSE;
    $resultado['mensaje'] = 'Datos mal ingresados';
}
if($resultado['estado']) {
    $tipoMensaje = 'alert alert-success';
} else {
    $tipoMensaje = 'alert alert-danger';
}
$mensaje = $resultado['mensaje'];
?>


<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
        <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
    </form>
</body>

