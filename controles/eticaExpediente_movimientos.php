<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eticaExpedienteMovimientoLogic.php');
$eticaExpedienteMovimientoLogic = new eticaExpedienteMovimientoLogic();
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "desc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            dom: 'T<"clear">lfrtip',
            tableTools: {
               "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
               "aButtons": [
                    {
                        "sExtends": "pdf",
                        "mColumns" : [0, 1, 2, 3, 4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                        "sTitle": "Listado de movimientos por expediente",
                        "sPdfOrientation": "portrait",
                        "sFileName": "listado_de_movimientos_por_expediente.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                    }
                    
            ]
            }
        });
    });
           
function confirmar()
{
    if(confirm('¿Estas seguro de elimiar este movimiento?'))
        return true;
    else
        return false;
}           
</script>

<?php
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Movimientos del Expediente</b></h4></div>
    <div class="panel-body">
    <?php
    $continua = TRUE;
    if (isset($_POST['estadoExpediente']) && isset($_POST['idEticaExpediente'])){
        $estadoExpediente = $_POST['estadoExpediente'];
        $idEticaExpediente = $_POST['idEticaExpediente'];
        
        $resExpediente = $eticaExpedienteLogic->obtenerEticaExpedientePorId($idEticaExpediente);
        if ($resExpediente['estado']){
            $eticaExpediente = $resExpediente['datos'];
        ?>
        <div class="row">
            <div class="col-xs-4"><h4><b>Colegiado:</b> <?php echo $eticaExpediente['apellido'].' '.$eticaExpediente['nombres'].' ('.$eticaExpediente['matricula'].')'; ?></h4></div>
            <div class="col-xs-5"><h4><b>Car&aacute;tula:</b> <?php echo $eticaExpediente['caratula']; ?></h4></div>
            <div class="col-xs-3">
                <a href="eticaExpediente_movimientos_form.php?idEticaExpediente=<?php echo $idEticaExpediente; ?>" class="btn btn-primary" role="button" >Nuevo movimiento</a>
            </div>
        </div>
        <?php
        $tipoUsuario = 'E'; //E: empleado del colegio
        $resMovimientos = $eticaExpedienteMovimientoLogic->obtenerMovimientosPorIdEticaExpediente($idEticaExpediente, $tipoUsuario);
        if ($resMovimientos['estado']){
        ?>
            <div class="row">&nbsp;</div>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th style="display: none;">Fecha movimiento</th>
                        <th>Id</th>
                        <th>Derivado a</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Observaci&oacute;n</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($resMovimientos['datos'] as $dato) 
                {
                    $idEticaExpedienteMovimiento = $dato['idEticaExpedienteMovimiento'];
                    $derivado = $dato['derivado'];
                    $fecha = $dato['fecha'];
                    $estado = $dato['estado'];
                    $observacion = $dato['observacion'];
                    $usuario = $dato['usuario'];
                ?>
                    <tr>
                        <td style="display: none;"><?php echo substr($fecha, 0, 10);?></td>
                        <td><?php echo $idEticaExpedienteMovimiento;?></td>
                        <td><?php echo $derivado;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar(substr($fecha, 0, 10));?></td>
                        <td><?php echo $estado;?></td>
                        <td><?php echo $observacion;?></td>
                        <td><?php echo $usuario;?></td>
                        <td>
                            <a href="eticaExpediente_movimientos_form.php?id=<?php echo $idEticaExpedienteMovimiento; ?>&accion=3" class="btn btn-primary" role="button" >Editar</a>
                            &nbsp;
                            <a href="datosEticaExpediente/abm_eticaExpedienteMovimiento.php?id=<?php echo $idEticaExpedienteMovimiento; ?>&accion=2" class="btn btn-danger" role="button" onclick="return confirmar()">Eliminar</a>
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
            <div class="<?php echo $resMovimientos['clase']; ?>" role="alert">
                <span class="<?php echo $resMovimientos['icono']; ?>" ></span>
                <span><strong><?php echo $resMovimientos['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
    } else {
    ?>
        <div class="<?php echo $resExpediente['clase']; ?>" role="alert">
            <span class="<?php echo $resExpediente['icono']; ?>" ></span>
            <span><strong><?php echo $resExpediente['mensaje']; ?></strong></span>
        </div>
    <?php
    }
} else {
?>
    <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            <span><strong>Faltan datos del expediente, vuelva a ingresar.</strong></span>
    </div>
<?php
}
?>
        
        <!-- BOTON VOLVER -->    
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12" style="text-align:right;">
                <form  method="POST" action="eticaExpediente_lista.php">
                    <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
                    <input type="hidden" name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente; ?>" />
               </form>
            </div>  
        </div>  
</div>
</div>
<?php
require_once '../html/footer.php';