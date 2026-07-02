<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();

$continua = TRUE;
$mensaje = 'OK';
if (isset($_GET['idResolucion'])) {
    $idResolucion = $_GET['idResolucion'];

    $resDetalle = $resolucionesLogic->obtenerDetalleResolucionPorId($idResolucion);
    if ($resDetalle['estado']) {
        $detalle = $resDetalle['datos'];
        $cantidad = count($detalle);
        foreach ($detalle as $resolucionDetalle) {
            $idResolucionDetalle = $resolucionDetalle['idResolucionDetalle'];
            $idColegiado = $resolucionDetalle['idColegiado'];
            $idEspecialidad = $resolucionDetalle['idEspecialidad'];
            $tipoEspecialista = $resolucionDetalle['tipoEspecialista'];
            $idEstadoResolucionDetalle = $resolucionDetalle['idEstadoResolucionDetalle'];
            $fechaAprobacion = $resolucionDetalle['fechaAprobacion'];
            $fechaRecertificacion = $resolucionDetalle['fechaRecertificacion'];
            $fechaNacimiento = $resolucionDetalle['fechaNacimiento'];
            $idTipoEspecialista = $resolucionDetalle['idTipoEspecialista'];
            $distrito = $resolucionDetalle['distrito'];
            $idColegiadoEspecialista = $resolucionDetalle['idColegiadoEspecialista'];
            $idColegiadoEspecialistaTipo = $resolucionDetalle['idColegiadoEspecialistaTipo'];
            $incisoArticulo8 = $resolucionDetalle['incisoArticulo8'];

            $agregoEspecialista = FALSE;
            //si esta aprobada, cargo al especialista o la recertificacion
            if ($idEstadoResolucionDetalle <= 1) {
                switch ($tipoEspecialista) {
                    case 'E':
                    case 'X':
                    case 'A':
                    case 'N':
                    case 'O':
                    case 'U':
                        //verifica que no exista
                        //if (!$colegiadoEspecialistaLogic->existeEspecialista($idColegiado, $idEspecialidad, "Especialista")) {
                        if (!isset($idColegiadoEspecialista) || $idColegiadoEspecialista == "" || $idColegiadoEspecialista == 0) {
                            $fechaVencimiento = sumarRestarSobreFecha($fechaAprobacion, 5, 'year', '+');
                            if (!isset($distrito) || $distrito == "" || $distrito == " " || $distrito == "0") {
                                $distrito = '1';
                            }
                            $resultado = $colegiadoEspecialistaLogic->agregarEspecialista($idEspecialidad, $fechaAprobacion, $distrito, $fechaVencimiento, $idColegiado, $idTipoEspecialista, $idResolucionDetalle, $incisoArticulo8);
                            $agregoEspecialista = $resultado['estado'];
                        }
                        break;

                    case 'J':
                    case 'C':
                        if (!isset($idColegiadoEspecialistaTipo) || $idColegiadoEspecialistaTipo == "" || $idColegiadoEspecialistaTipo == 0) {
                            $resultado = $colegiadoEspecialistaLogic->agregarEspecialistaTipo($idColegiadoEspecialista, $tipoEspecialista, $fechaAprobacion, $distrito, $idResolucionDetalle, $idTipoEspecialista);
                            if (!$resultado['estado']) {
                                var_dump($resultado['mensaje']);
                                exit;
                            }
                            $agregoEspecialista = $resultado['estado'];
                        }
                        break;
                    case 'R':
                        if (isset($idColegiadoEspecialista) && $idColegiadoEspecialista <> "" && $idColegiadoEspecialista <> 0) {
                            $fechaVencimiento = sumarRestarSobreFecha($fechaRecertificacion, 5, 'year', '+');
                            $resultado = $colegiadoEspecialistaLogic->agregarRecertificacion($idColegiadoEspecialista, $fechaRecertificacion, $fechaVencimiento, $idResolucionDetalle);
                            $agregoEspecialista = $resultado['estado'];
                        }
                        break;
                    default:
                        break;
                }

                //si salio bien entonces marco como aplicado el detalle de la resolucion
                if ($agregoEspecialista) {
                    $resActualiza = $resolucionesLogic->cambiarEstadoResolucionDetalle($idResolucionDetalle, 1);
                    $cantidad -= 1;
                } 
            }
        }
        
        //if ($cantidad == 0) {
            $resResolucion = $resolucionesLogic->cambiarEstadoResolucion($idResolucion, 'E', 'C');
        //}
    } else {
        $continua = FALSE;
        $tipoMensaje = $resDetalle['clase'];
        $mensaje = $resDetalle['mensaje'];
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos en el formulario, verifique.";
}
/*
echo $mensaje;
var_dump($resultado);
exit;
 * 
 */
?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../especialidades_resoluciones_matriculas.php?idResolucion=<?php echo $idResolucion; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
        <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
    </form>
</body>

