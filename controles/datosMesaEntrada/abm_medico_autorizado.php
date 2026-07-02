<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['agregar'])) {
    $accion = "AGREGAR";
    if (isset($_POST['idMesaEntradaConsultorio']) && $_POST['idMesaEntradaConsultorio'] <> "") {
        //da de alta la nueva solicitud de especialista
        $idMesaEntradaConsultorio = $_POST['idMesaEntradaConsultorio'];
    } else {
        $continua = FALSE;
        $mensaje = "Falta idMesaEntradaConsultorio - ";
    } 
    if (isset($_POST['idColegiadoAutorizado']) && $_POST['idColegiadoAutorizado'] <> "") {
        $idColegiadoAutorizado = $_POST['idColegiadoAutorizado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idColegiadoAutorizado - ";
    }
} else {
    if (isset($_GET['borrar'])) {
        $accion = "BORRAR";
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $ids = explode('_', $_GET['id']);
            $idMesaEntradaConsultorioAutorizado = $ids[0];
            $idMesaEntradaConsultorio = $ids[1];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idMesaEntradaConsultorioAutorizado - ";
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion - ";
    }
}
if ($continua) {
    $mesaEntradaLogic = new mesaEntradaLogic();
    switch ($accion) {
        case "AGREGAR":
            //continuar con el alta       
            $resultado = $mesaEntradaLogic->agregarMesaEntradaConsultorioAutorizado($idMesaEntradaConsultorio, $idColegiadoAutorizado);
            break;

        case "BORRAR":
            //continuar con el alta       
            $resultado = $mesaEntradaLogic->borrarMesaEntradaConsultorioAutorizado($idMesaEntradaConsultorioAutorizado);
            break;

        default:
            $continua = FALSE;
            break;
    }
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger';
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../mesa_entrada_habilitacion_consultorio.php?id=<?php echo $idMesaEntradaConsultorio; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
        <input type="hidden"  name="accion" id="accion" value="AGREGADA">
    </form>
</body>

