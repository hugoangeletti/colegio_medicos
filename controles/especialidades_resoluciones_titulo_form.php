<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/tipoResolucionLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../dataAccess/colegiadoLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idResolucionDetalle = $_GET['id'];
} else {
    $idResolucionDetalle = NULL;
    $mensaje .= "Ingreso incorrecto - ";
}
if ($continua) {
    $resDetalle = $resolucionesLogic->obtenerResolucionDetallePorId($idResolucionDetalle);
    if ($resDetalle['estado']) {
        $resolucionDetalle = $resDetalle['datos'];
        $idResolucion = $resolucionDetalle['idResolucion'];
        $tipo = $resolucionDetalle['tipo'];
        $especialidad = $resolucionDetalle['especialidad'];
        $estado = $resolucionDetalle['estado'];
        $fechaAprobada = $resolucionDetalle['fechaAprobada'];
        $fechaRecertificacion = $resolucionDetalle['fechaRecertificacion'];
        $inciso = $resolucionDetalle['inciso'];
        $idColegiado = $resolucionDetalle['idColegiado'];
        $matricula = $resolucionDetalle['matricula'];
        $apellidoNombre = $resolucionDetalle['apellido'].' '.$resolucionDetalle['nombre'];
        $especialidadDetalle = $resolucionDetalle['especialidadDetalle'];
        $tipoEspecialista = $resolucionDetalle['tipoEspecialista'];
        $idTipoResolucion = $resolucionDetalle['idTipoResolucion'];
        $tipoResolucion = $resolucionDetalle['tipoResolucion'];
        $numeroResolucion = $resolucionDetalle['numeroResolucion'];
        $idColegiadoEspecialista = $_GET['idColegiadoEspecialista'];

        ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-9 text-left">
                        <h4>Imprimir Título de Especialista.</h4>
                    </div>
                    <div class="col-md-3 text-left">
                        <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="especialidades_resoluciones_matriculas.php?idResolucion=<?php echo $idResolucion; ?>">
                                <button type="submit"  class="btn btn-info" >Volver a la resolución</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-6"><h4>Matrícula: &nbsp;<?php echo $matricula; ?> - <?php echo $apellidoNombre; ?></h4></div>
                </div>
                <div class="row">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-4">
                        <label>Especialidad: &nbsp;</label><?php echo $especialidadDetalle; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-4">
                        <label>Tipo de presentación: &nbsp;</label><?php echo $tipoEspecialista; if ($inciso <> "") { echo ' inciso '.$inciso; } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-4">
                        <label>Resolución: &nbsp;</label><?php echo $numeroResolucion.' - '.$tipoResolucion; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-4">
                        <label>Fecha aprobación: &nbsp;</label><?php echo cambiarFechaFormatoParaMostrar($fechaAprobada); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-4">
                        <label>Fecha recertificación: &nbsp;</label><?php echo cambiarFechaFormatoParaMostrar($fechaRecertificacion); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 text-right">
                        <form id="formConfirma" name="formConfirma" method="POST" onSubmit="" action="especialidades_resoluciones_generar_codigo_qr.php?<?php echo 'id='.$idResolucionDetalle.'&matricula='.$matricula.'&idResolucion='.$idResolucion.'&idColegiadoEspecialista='.$idColegiadoEspecialista; ?>">
                            <button type="submit"  class="btn btn-info" >Generar Código QR</button>
                        </form>
                    </div>
                    <div class="col-md-4">
                    </div>
                </div>
            </div>
        </div>
    <?php 
    }
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="cajadiaria.php">
                <button type="submit"  class="btn btn-info" >Volver</button>
            </form>
        </div>
    </div>
<?php
}
require_once '../html/footer.php';
