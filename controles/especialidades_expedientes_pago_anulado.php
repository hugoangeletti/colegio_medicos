<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaColegiacion').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 0, "asc" ]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'                    
                });
    }
);

function confirmarCierre()
{
    if(confirm('¿Estas seguro de ANULAR LOS PAGOS PENDIENTES?'))
        return true;
    else
        return false;
}
</script>
<?php
if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php    
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-10">
                <h4><b>Expedientes Especialistas Anulados</b></h4>
            </div>
            <div class="col-md-2">
                <a href="especialidades_expediente_pendiente_pago.php" class="btn btn-info" >Volver a pendientes de pago</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $resDeuda = $mesaEntradaEspecialistaLogic->obtenerExpedientesAnulados();
        if ($resDeuda['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaColegiacion" class="display">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Id</th>
                                <th style="text-align: center;">Fecha ingreso</th>
                                <th style="text-align: center;">Matricula</th>
                                <th style="text-align: left;">Apellido y Nombre</th>
                                <th style="text-align: right;">Importe</th>
                                <th style="text-align: left;">Especialidad</th>
                                <th style="text-align: left;">Tipo</th>
                                <th style="text-align: left;">Realizó</th>
                                <th style="text-align: left;">Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resDeuda['datos'] as $dato){
                                $idMesaEntrada = $dato['idMesaEntrada'];
                                $fechaIngreso = $dato['fechaIngreso'];
                                $idColegiado = $dato['idColegiado'];
                                $matricula = $dato['matricula'];
                                $apellidoNombre = $dato['apellidoNombre'];
                                $importe = $dato['importe'];
                                $nombreEspecialidad = $dato['nombreEspecialidad'];
                                $nombreTipoEspecialista = $dato['nombreTipoEspecialista'];
                                if (isset($dato['incisoArticulo8']) && $dato['incisoArticulo8'] <> "") {
                                    $nombreTipoEspecialista .= ' inc. '.$dato['incisoArticulo8'];
                                }
                                $nombreUsuario = $dato['nombreUsuario'];
                                $observacion = $dato['observacion'];
                                ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $idMesaEntrada;?></td>
                                    <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaIngreso);?></td>
                                    <td style="text-align: center;"><?php echo $matricula;?></td>
                                    <td style="text-align: left;"><?php echo $apellidoNombre;?></td>
                                    <td style="text-align: right;"><?php echo $importe;?></td>
                                    <td style="text-align: left;"><?php echo $nombreEspecialidad;?></td>
                                    <td style="text-align: left;"><?php echo $nombreTipoEspecialista;?></td>
                                    <td style="text-align: left;"><?php echo $nombreUsuario;?></td>
                                    <td style="text-align: left;"><?php echo $observacion;?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resDeuda['clase']; ?>" role="alert">
                <span class="<?php echo $resDeuda['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resDeuda['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
</div>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
