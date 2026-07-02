<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoResidenteLogic.php');
$colegiadoResidenteLogic = new colegiadoResidenteLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":25,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmaAnular()
{
    if(confirm('¿Estas seguro de ANULAR este RECIBO?'))
        return true;
    else
        return false;
}
</script>
<?php
$continua = TRUE;
$mensaje = "";
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = $colegiado['apellido'].' '.trim($colegiado['nombre']);
    } else {
        $continua = FALSE;
        $mensaje .= $resColegiado['mensaje'];
    }
} else {
    $continua = FALSE;
    $resColegiadoResidente['clase'] = "alert alert-warning";
    $resColegiadoResidente['icono'] = "glyphicon glyphicon-exclamation-sign";
    $resColegiadoResidente['mensaje'] = "Datos mal ingresados";
}

if (isset($idColegiadoResidente)) {
    $panel = 'panel-info';
    $claseBoton = 'btn-info';
    $textoBoton = 'Confimar';
    $readOnly = 'readonly';
    $requerido = '';
} else {
    $panel = 'panel-success';
    $textoBoton = 'Confirmar';
    $claseBoton = 'btn-success';
    $readOnly = '';
    $requerido = 'required';
}

?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4> Opción de residente</h4>
            </div>
            <div class="col-md-2 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_residente_opcion_form.php?idColegiado=<?php echo $idColegiado; ?>">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Agregar opción</button>
                </form>
            </div>
            <div class="col-md-1 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
    if (isset($_POST['mensaje'])) {
    ?>
       <div class="ocultarMensaje"> 
           <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php
    }
    ?>
        <div class="row">
            <div class="col-md-4">
                <label for="apellidoNombre">Apellido y nombre</label>
                <input class="form-control" type="text" name="apellidoNombre" value="<?php echo $apellidoNombre; ?>" readonly/>
            </div>
            <div class="col-md-1">
                <label for="matricula">Matrícula</label>
                <input class="form-control" type="number" name="matricula" value="<?php echo $matricula; ?>" readonly/>
            </div>
            <div class="col-md-2">
                <label for="fechaInicio">Fecha de solcitud:</label>
                <input class="form-control" type="date" name="fechaInicio" value="<?php echo $fechaInicio; ?>" readonly/>
            </div>
            <div class="col-md-2">
                <label for="fechaFin">Fecha de caducidad:</label>
                <input class="form-control" type="date" name="fechaFin" value="<?php echo $fechaFin; ?>" readonly/>
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Fecha de solicitud</th>
                    <th>Fecha de caducidad</th>
                    <th>Estado</th>
                    <th>Opción</th>
                    <th>Entidad</th>
                    <th>Adjunto</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $resSolicitudes = $colegiadoResidenteLogic->obtenerSolicitudesColegiadoResidentePorIdColegiado($idColegiado);
                if ($resSolicitudes['estado']) {
                    foreach ($resSolicitudes['datos'] as $solicitud) {
                        $fechaInicio = $solicitud['fechaInicio'];
                        $fechaFin = $solicitud['fechaFin'];
                        $idColegiadoResidente = $solicitud['idColegiadoResidente'];
                        $opcion = $solicitud['opcion'];
                        $anioResidencia = $solicitud['anio'];
                        $idEntidad = $solicitud['idEntidad'];
                        $nombreEntidad = $solicitud['nombreEntidad'];
                        $adjunto = $solicitud['adjunto'];
                        $borrado = $solicitud['borrado'];
                        if ($borrado == 0 && $fechaFin > date('Y-m-d')) {
                            $estado = 'VIGENTE';
                        } else {
                            $estado = '';
                            if ($borrado == 1) {
                                $estado = 'ANULADO';
                            }
                        }
                        ?>
                        <tr>
                            <td><?php echo $idColegiadoResidente; ?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaInicio); ?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaFin); ?></td>
                            <td><?php echo $estado; ?></td>
                            <td><?php echo $opcion; ?></td>
                            <td><?php echo $nombreEntidad; ?></td>
                            <td><?php echo $adjunto; ?></td>
                            <td style="width: 400px;">
                                <?php 
                                if ($estado == 'VIGENTE') {
                                ?>
                                    <a href="colegiado_residente_imprimir.php?id=<?php echo $idColegiadoResidente; ?>" class="btn btn-default" target="_BLANK">Imprimir Planilla</a>
                                    <a href="colegiado_residente_opcion_form.php?id=<?php echo $idColegiadoResidente; ?>&idColegiado=<?php echo $idColegiado; ?>" class="btn btn-default">Actualizar datos</a>
                                    <a href="datosResidente/abm_residente.php?id=<?php echo $idColegiadoResidente; ?>&idColegiado=<?php echo $idColegiado; ?>&anular" class="btn btn-default" onclick="return confirmar()">Anular</a>
                                <?php 
                                } 
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    $continua = FALSE;
                    $mensaje .= $resSolicitudes['mensaje'];
                }
                ?>
            </tbody>
        </table>
    </div>    
</div>
<?php
require_once '../html/footer.php';
