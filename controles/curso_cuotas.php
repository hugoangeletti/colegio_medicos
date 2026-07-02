<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');
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
    $idCurso = $_GET['id'];
    $cursos_pdo = new cursos_pdo();
    $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
    if ($resCurso['estado']) {
        $curso = $resCurso['datos'];
        $titulo = $curso['titulo'];
        $director = $curso['director'];
        $fechaInicio = $curso['fechaInicio'];
        $estadoCurso = $curso['estado'];
        $tema = $curso['tema'];
        $dias = $curso['dias'];
        $fechas = $curso['fechas'];
        $salon = $curso['salon'];
        $lugar = $curso['lugar'];
        $coordinador = $curso['coordinador'];
        $vigenciaHasta = $curso['vigenciaHasta'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resCurso['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idCurso - ';
}
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h5><b>Cuotas del Curso (#<?php echo $idCurso; ?>): <?php echo $titulo; ?> </b></h5></div>
    <div class="panel-body">
        <div class="row">
            <div class="row">
                <div class="col-xs-8">
                </div>
                <div class="col-xs-1 text-center">
                    <a href="curso_cuotas_form.php?idCurso=<?php echo $idCurso; ?>&agregar" class="btn btn-info">Agregar cuota</a>
                </div>
                <div class="col-xs-2 text-center">
                    <a href="datosCurso/actualiza_cuotas_asistentes.php?idCurso=<?php echo $idCurso; ?>" class="btn btn-info">Actualizar cuotas de los asistentes</a>
                </div>
                <div class="col-xs-1 text-center">
                    <a href="curso_listado.php" class="btn btn-primary" >Volver</a>
                </div>
            </div>
            <?php
            $cursos_pdo = new cursos_pdo();
            $resCuotas = $cursos_pdo->obtenerCuotasPorIdCurso($idCurso);
            if ($resCuotas['estado']){
            ?>
                <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Cuota</th>
                                <th>Detalle</th>
                                <th>Fecha Vencimiento</th>
                                <th>Importe</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resCuotas['datos'] as $dato) {
                          $idCuota = $dato['idCuota'];
                          $cuota = $dato['cuota'];
                          $detalleCuota = $dato['detalleCuota'];
                          $fechaVencimiento = $dato['fechaVencimiento'];
                          $importe = $dato['importe'];
                          $cantidadCuotas = $dato['cantidadCuotas'];
                          if ($cantidadCuotas > 0) {
                            $borrar = FALSE;
                          } else {
                            $borrar = TRUE;
                          }
                        ?>
                        <tr>
                    	   <td><?php echo $idCuota;?></td>
                           <td><?php echo $cuota;?></td>
                           <td><?php echo $detalleCuota;?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($fechaVencimiento);?></td>
                           <td><?php echo $importe;?></td>
                           <td>
                                <a href="curso_cuotas_form.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCuota; ?>&editar" class="btn btn-info">Editar cuota</a>
                                <?php 
                                if ($borrar) {
                                ?>
                                    <a href="datosCurso/abm_curso_cuota.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCuota; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular()">Borrar Cuota</a>
                                <?php 
                                } 
                                ?>
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
                <div class="<?php echo $resCuotas['clase']; ?>" role="alert">
                    <span class="<?php echo $resCuotas['icono']; ?>" ></span>
                    <span><strong><?php echo $resCuotas['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';