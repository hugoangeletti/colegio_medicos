<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/retiroDocumentacionLogic.php');
$retiroDocumentacionLogic = new retiroDocumentacionLogic();

$accion = $_POST['accion'];
$estadoRetiro = $_POST['estadoRetiro'];
if (isset($_POST['idRetiroDocumentacion'])) {
    $idRetiroDocumentacion = $_POST['idRetiroDocumentacion'];
} else {
    $idRetiroDocumentacion = NULL;
}

$continua = TRUE;
if ($accion == 1 || $accion == 3) {
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> '') {
        $idColegiado = $_POST['idColegiado'];
        $colegiado_buscar = $_POST['colegiado_buscar'];
    } else {
        $continua = FALSE;
        $mensaje = "Faltan idColegiado.";
    }
    if (isset($_POST['idTipoDocumentacionRetiro']) && $_POST['idTipoDocumentacionRetiro'] <> '') {
        $idTipoDocumentacionRetiro = $_POST['idTipoDocumentacionRetiro'];
    } else {
        $continua = FALSE;
        $mensaje = "Faltan idTipoDocumentacionRetiro.";
    }
    if (isset($_POST['observacion']) && $_POST['observacion'] <> '') {
        $observacion = $_POST['observacion'];
    } else {
        $observacion = NULL;
    }
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $retiroDocumentacionLogic->agregarRetiroDocumentacion($idColegiado, $idTipoDocumentacionRetiro, $observacion);
            break;
        case '3':
            $resultado = $retiroDocumentacionLogic->editarRetiroDocumentacion($idRetiroDocumentacion, $idColegiado, $idTipoDocumentacionRetiro, $observacion);
            break;
        case '2':
            switch ($estadoRetiro) {
                case 'A':
                    $estado = "B";
                    break;
                
                case 'B':
                    $estado = "A";
                    break;
                
                default:
                    $estado = "X";
                    break;
            }
            $resultado = $retiroDocumentacionLogic->borrarRetiroDocumentacion($idRetiroDocumentacion, $estado);
            break;
            
        case '4':
            switch ($estadoRetiro) {
                case 'E':
                    $estado = "A";
                    break;
                
                case 'A':
                    $estado = "E";
                    break;
                
                default:
                    $estado = "X";
                    break;
            }
            $resultado = $retiroDocumentacionLogic->marcarEntregaRetiroDocumentacion($idRetiroDocumentacion, $estado);
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
        <form name="myForm"  method="POST" action="../retiro_documentacion.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="estadoRetiro" id="estadoRetiro" value="<?php echo $estadoRetiro;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="retiro_documentacion_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="estadoRetiro" id="estadoRetiro" value="<?php echo $estadoRetiro;?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="observacion" id="observacion" value="<?php echo $observacion;?>">
            <input type="hidden"  name="idTipoDocumentacionRetiro" id="idTipoDocumentacionRetiro" value="<?php echo $idTipoDocumentacionRetiro;?>">
            <input type="hidden"  name="idRetiroDocumentacion" id="idRetiroDocumentacion" value="<?php echo $idRetiroDocumentacion;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

