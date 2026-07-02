<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
require_once ('../../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();

$continua = TRUE;
$idResolucion = NULL;
$mensaje = 'OK';
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];

    if (isset($_GET['id']) && isset($_GET['idResolucion'])) {
        $idResolucion = $_GET['idResolucion'];
        $idMesaEntradaEspecialidad = $_GET['id'];
        $fechaResolucion = $_GET['fecha'];
        
        $resMesaEntradaEspecialista = $mesaEntradaEspecialistaLogic->obtenerMesaEntradaEspecialistaPorId($idMesaEntradaEspecialidad);
        if ($resMesaEntradaEspecialista['estado']) {
            $mesaEntradaEspecialista = $resMesaEntradaEspecialista['datos'];
            $idEspecialidad = $mesaEntradaEspecialista['idEspecialidad'];
            $tipoEspecialista = $mesaEntradaEspecialista['tipoTramiteEspecialista'];
            $idColegiado = $mesaEntradaEspecialista['idColegiado'];
            $inciso = $mesaEntradaEspecialista['inciso'];
            $fechaAprobada = $fechaResolucion;
            $fechaRecertificacion = NULL;
            $idTipoEspecialista = $mesaEntradaEspecialista['idTipoEspecialista'];
            if ($tipoEspecialista == 'R') {
                //$fechaRecertificacion = sumarRestarSobreFecha($fechaAprobada, 5, 'year', '+');
                /*
                if (isset($mesaEntradaEspecialista['ultimaRecertificacion']) && $mesaEntradaEspecialista['ultimaRecertificacion'] <> "") {
                    $fechaRecertificacion = sumarRestarSobreFecha($mesaEntradaEspecialista['ultimaRecertificacion'], 5, 'year', '+');
                } else {
                    $fechaPermitida = sumarRestarSobreFecha($mesaEntradaEspecialista['fechaEspecialista'], 2, 'year', '+');
                    if ($fechaPermitida <= date('Y-m-d')) {
                        $fechaRecertificacion = sumarRestarSobreFecha($mesaEntradaEspecialista['fechaEspecialista'], 5, 'year', '+');
                    } else {
                        $fechaRecertificacion = sumarRestarSobreFecha($fechaResolucion, 5, 'year', '+');
                    }
                }
                 * 
                 */
                
                //obtenemos el vencimiento de la especialidad
                $resEspecialista = $colegiadoEspecialistaLogic->obtenerColegiadoEspecialistaPorColegiadoEspecialidad($idColegiado, $idEspecialidad);
                if ($resEspecialista['estado']) {
                    $dato = $resEspecialista['datos'];
                    $fechaVencimiento = $dato['fechaVencimiento'];
                    if (isset($fechaVencimiento) && $fechaVencimiento <> "" && $fechaVencimiento <> "0000-00-00") {
                        //$fechaRecertificacion = sumarRestarSobreFecha($dato['fechaRecertificacion'], 5, 'year', '+');
                        $fechaPermitida = sumarRestarSobreFecha($fechaVencimiento, 2, 'year', '+');
                        if ($fechaPermitida <= date('Y-m-d')) {
                            //$fechaRecertificacion = sumarRestarSobreFecha($fechaVencimiento, 5, 'year', '+');
                            //$fechaRecertificacion = sumarRestarSobreFecha($fechaResolucion, 5, 'year', '+');
                            $fechaRecertificacion = $fechaResolucion;
                        } else {
                            $fechaRecertificacion = $fechaVencimiento;
                        }
                    } else {
                        $fechaPermitida = sumarRestarSobreFecha($dato['fechaEspecialista'], 2, 'year', '+');
                        if ($fechaPermitida > date('Y-m-d')) {
                            $fechaRecertificacion = sumarRestarSobreFecha($dato['fechaEspecialista'], 5, 'year', '+');
                        } else {
                            $fechaRecertificacion = $fechaResolucion; //sumarRestarSobreFecha($fechaResolucion, 5, 'year', '+');
                        }
                    }
                } else {
                    $continua = FALSE;
                    $tipoMensaje = $resEspecialista['clase'];
                    $mensaje = $resEspecialista['mensaje'];
                }
            }
            $idEspecialistaBaja = NULL;
        } else {
            $continua = FALSE;
            $tipoMensaje = $resMesaEntradaEspecialista['clase'];
            $mensaje = $resMesaEntradaEspecialista['mensaje'];
        }
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Faltan datos en el formulario, verifique.";
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Mal ingreso.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $resolucionesLogic->agregarResolucionDetalle($idResolucion, $idMesaEntradaEspecialidad, $idEspecialidad, $tipoEspecialista, $fechaAprobada, $fechaRecertificacion, $idEspecialistaBaja, $idColegiado, $inciso, $idTipoEspecialista);
            break;
        /*
        case '2':
            $resultado = borrarResolucion($idResolucion);
            break;
         * 
         */
        case '3':
            $resultado = $resolucionesLogic->modificarResolucion($idResolucion, $numero, $fecha, $detalle);
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
    $resultado['estado'] = $continua;
}

/*
echo $mensaje;
var_dump($resultado);
exit;
 * 
 */

?>


<body onLoad="document.forms['myForm'].submit()">
        <form name="myForm"  method="POST" action="../especialidades_resoluciones_matricula_form.php?accion=<?php echo $accion; ?>&idResolucion=<?php echo $idResolucion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    /*
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../especialidades_resoluciones_matricula_form.php?accion=<?php echo $accion; ?>&idResolucion=<?php echo $idResolucion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../especialidades_resoluciones_matricula_form.php?idResolucion=<?php echo $idResolucion; ?>&estado=<?php echo $estadoResoluciones; ?>&accion=<?php echo $accion; ?>&anio=<?php echo $anioResoluciones ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <?php 
            if ($idResolucion) {
                ?>
                <input type="hidden"  name="idResolucion" id="idResolucion" value="<?php echo $idResolucion;?>">
            <?php
            }
            ?>
            <input type="hidden"  name="detalle" id="detalle" value="<?php echo $detalle;?>">
            <input type="hidden"  name="fecha" id="fecha" value="<?php echo $fecha;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="tipoResolucion" id="tipoResolucion" value="<?php echo $tipoResolucion;?>">
            <input type="hidden"  name="idTipoResolucion" id="idTipoResolucion" value="<?php echo $idTipoResolucion;?>">
            <input type="hidden"  name="numero" id="numero" value="<?php echo $numero;?>">
        </form>
    <?php
    }
     * 
     */
    ?>
</body>

