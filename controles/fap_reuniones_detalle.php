<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "asc" ]],
            //"order": [[ 2, "desc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });   

function confirmaAnular()
{
    if(confirm('¿Estas seguro de Borrar registro?'))
        return true;
    else
        return false;
}

</script>

<?php
$continua = TRUE;
$mensaje = "";
$fapLogic = new fapLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSapConsejo = $_GET['id'];
    $resReunion = $fapLogic->obtenerSapReunionPorId($idSapConsejo);
    if ($resReunion['estado']) {
        $fapReunion = $resReunion['datos'];
        $fechaReunion = $fapReunion['fechaReunion'];
        $estadoReunion = $fapReunion['estadoReunion'];
        $observaciones = $fapReunion['observaciones'];
        $resolucion = $fapReunion['resolucion'];
    } else {
        $continua = FALSE;
        $mensaje .= $resReunion['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Error de ingreso, falta idSapConsejo - ';
}

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
}   
$fapLogic = new fapLogic();
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-8">
                <h4><b>Detalle Reunión de Consejo del día <?php echo cambiarFechaFormatoParaMostrar($fechaReunion); ?></b></h4>
            </div>
            <div class="col-md-2">
                <?php 
                if ($estadoReunion <> 'C') {
                ?>
                    <a href="fap_reuniones_detalle_form.php?id=<?php echo $idSapConsejo; ?>&agregar" class="btn btn-info">Agregar Causa</a>
                <?php 
                }
                ?>
            </div>
            <div class="col-md-2">
                <a href="fap_reuniones.php?anio=<?php echo substr($fechaReunion, 0, 4); ?>&estado=<?php echo $estadoReunion; ?>" class="btn btn-info">Volver al listado</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $resDetalle = $fapLogic->obtenerReunionDetallePorIdReunion($idSapConsejo);
        if ($resDetalle['estado']){
        ?>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th style="display: none;">IdSapConsejoDetalle</th>
                        <th>N° FAP</th>
                        <th>Matrícula</th>
                        <th>Apellido y nombre</th>
                        <th>Tipo de trámite</th>
                        <th>Tipo de causa</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resDetalle['datos'] as $dato) {
                        $orden = $dato['orden'];
                        $idSapConsejoDetalle = $dato['idSapConsejoDetalle'];
                        $idSapCaratula = $dato['idSapCaratula'];
                        $matricula = $dato['matricula'];
                        $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                        $estado = $dato['estado'];
                        $nombreEstado = $dato['nombreEstado'];
                        $observaciones = $dato['observaciones'];
                        $nombreSapTipoTramite = $dato['nombreSapTipoTramite'];
                        $nombreTipoCausa = $dato['nombreTipoCausa'];
                        ?>
                        <tr>
                            <th><?php echo $orden; ?></th>
                            <td style="display: none;"><?php echo $idSapConsejoDetalle; ?></td>
                    	    <td><?php echo $idSapCaratula;?></td>
                            <td><?php echo $matricula;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                            <td><?php echo $nombreSapTipoTramite;?></td>
                            <td><?php echo $nombreTipoCausa;?></td>
                            <td><?php echo $nombreEstado;?></td>
                            <td><?php echo $observaciones;?></td>
                            <td style="text-align: center;">
                                <?php 
                                if ($estadoReunion == fapLogic::ESTADO_REUNION_ABIERTA) { 
                                ?>
                                    <a href="datosFap/abm_reuniones_detalle_individual.php?id=<?php echo $idSapConsejoDetalle; ?>&borrar" class="btn btn-primary" onclick="return confirmaAnular()">Borrar</a>
                                <?php 
                                } 
                                ?>
                                <a href="fap_reuniones_detalle_individual_form.php?id=<?php echo $idSapConsejoDetalle; ?>&editar" class="btn btn-primary">Editar</a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>              
    	       </tbody>
	       </table>
        <?php
        } else {
            ?>  
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resDetalle['clase']; ?>" role="alert">
                <span class="<?php echo $resDetalle['icono']; ?>" ></span>
                <span><strong><?php echo $resDetalle['mensaje']; ?></strong></span>
            </div>
        <?php
        }    
        ?>
    </div>
</div>
<?php
require_once '../html/footer.php';
?>
<script language="JavaScript">

    function confirmarCierre()
    {
        if(confirm('¿Estas seguro de CERRAR esta reunión?'))
            return true;
        else
            return false;
    }
    
</script>
