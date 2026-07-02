<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reconocimientoAntiguedadLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":50,
            "order": [[ 2, "asc" ]],
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
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idReconocimientoAntiguedad = $_GET['id'];
    $actosLogic = new reconocimientoAntiguedadLogic();
    $resActos = $actosLogic->obtenerActoPorId($idReconocimientoAntiguedad);            
    if ($resActos['estado']){
        $acto = $resActos['datos'];
        $fechaActo = $acto['fechaActo'];
        $lugarActo = $acto['lugarActo'];
        $antiguedad = $acto['antiguedad'];
    } else {
        $continua = FALSE;
        $mensaje .= $resActos['mensaje'];
    }            
} else {
    $continua = FALSE;
    $mensaje .= "Falta idReconocimientoAntiguedad - ";
}
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><b>Listado de Colegiados para el Acto de entrega de diplomas por los <?php echo $antiguedad; ?> años de recibido. Fecha del acto: <?php echo cambiarFechaFormatoParaMostrar($fechaActo); ?></b></h4>
            </div>
            <div class="col-md-3 text-right">
                <a href="reconocimiento_antiguedad.php" class="btn btn-primary" >Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['estadoMatricular']) && $_POST['estadoMatricular'] != "") {
            $estado = $_POST['estadoMatricular'];
        } else {
            $estado = 'ACTIVOS';
        }
        ?>
        <div class="row">
            <div class="col-md-3">
                <label>Filtro por estado matricular: *</label>
                <form method="POST" action="reconocimiento_antiguedad_detalle.php?id=<?php echo $idReconocimientoAntiguedad; ?>">
                    <select class="form-control" id="estadoMatricular" name="estadoMatricular" required onChange="this.form.submit()">
                        <option value="ACTIVOS" <?php if($estado == "ACTIVOS") { echo 'selected'; } ?>>Solo Activos</option>
                        <option value="ACTIVOS_CANCELACION_TRANSITORIA" <?php if($estado == "ACTIVOS_CANCELACION_TRANSITORIA") { echo 'selected'; } ?>>Activos y Con cancelación Transitoria</option>
                        <option value="JUBILADOS" <?php if($estado == "JUBILADOS") { echo 'selected'; } ?>>Jubilados y Inicio de Trámite jubilatorio</option>
                        <option value="TODOS" <?php if($estado == "TODOS") { echo 'selected'; } ?>>Todos</option>
                    </select>
                </form>    
            </div>
            <div class="col-md-9 text-right">
                <a href="reconocimiento_antiguedad_imprimir.php?id=<?php echo $idReconocimientoAntiguedad; ?>&filtro=<?php echo $estado; ?>" class="btn btn-info" target="_BLANK">Imprimir colegiados</a>
                <a href="reconocimiento_antiguedad_archivo.php?id=<?php echo $idReconocimientoAntiguedad; ?>&filtro=<?php echo $estado; ?>" class="btn btn-info" target="_BLANK">Generar archivo colegiados</a>
                <a href="reconocimiento_antiguedad_diplomas.php?idActo=<?php echo $idReconocimientoAntiguedad; ?>&filtro=<?php echo $estado; ?>" class="btn btn-info">Generar todos los diplomas</a>
            </div>
        </div>
        <div class="row">
            <?php
            if ($continua) {
                $resActoDetalle = $actosLogic->obtenerColegiadosPorActo($idReconocimientoAntiguedad, $estado);
                if ($resActoDetalle['estado']){
                ?>
                    <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Matrícula</th>
                                <th>Apellido y Nombre</th>
                                <th>Estado citación</th>
                                <th>Estado matricular</th>
                                <th>Estado con tesorería</th>
                                <th style="width: 300px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          foreach ($resActoDetalle['datos'] as $dato) {
                              $idReconocimientoAntiguedadDetalle = $dato['idReconocimientoAntiguedadDetalle'];
                              $idColegiado = $dato['idColegiado'];
                              $matricula = $dato['matricula'];
                              $apellidoNombre = $dato['apellidoNombre'];
                              $estadoInvitacion = $dato['estadoInvitacion'];
                              $estadoMatricular = $dato['estadoMatricular'];
                              $codigoDeudor = $dato['codigoDeudor'];
                              $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudor);
                              if ($resEstadoTesoreria['estado']){
                                  $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                              } else {
                                  $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                              }
                            ?>
                            <tr>
                    	       <td><?php echo $idColegiado;?></td>
                               <td><?php echo $matricula;?></td>
                               <td><?php echo $apellidoNombre;?></td>
                               <td><?php echo $estadoInvitacion;?></td>
                               <td><?php echo $estadoMatricular;?></td>
                               <td><?php echo $estadoTesoreria;?></td>
                               <td style="width: 300px;">
                                    <a href="datosActo\abm_acto.php?id=<?php echo $idReconocimientoAntiguedadDetalle; ?>&idActo=<?php echo $idReconocimientoAntiguedad; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular()">Borrar colegiado</a>
                                    <a href="reconocimiento_antiguedad_diplomas.php?id=<?php echo $idReconocimientoAntiguedadDetalle; ?>&filtro=<?php echo $estado; ?>" class="btn btn-info">Generar diploma</a>
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
                    <div class="<?php echo $resActoDetalle['clase']; ?>" role="alert">
                        <span class="<?php echo $resActoDetalle['icono']; ?>" ></span>
                        <span><strong><?php echo $resActoDetalle['mensaje']; ?></strong></span>
                    </div>
                <?php
                }
            } else {
            ?>
                <div class="row">&nbsp;</div>
                <div class="alert alert-danger" role="alert">
                    <span><strong><?php echo $mensaje; ?></strong></span>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';