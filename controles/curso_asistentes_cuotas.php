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
            "order": [[ 1, "asc" ]],
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
$cursos_pdo = new cursos_pdo();

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCursosAsistente = $_GET['id'];
    $resAsistente = $cursos_pdo->obtenerAsistentePorId($idCursosAsistente);
    if ($resAsistente['estado']) {
        $asistente = $resAsistente['datos'];
        $apellidoNombre = $asistente['apellidoNombre'];
        $matricula = $asistente['matricula'];

        $idCurso = $asistente['idCurso'];
        $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
        if ($resCurso['estado']) {
            $curso = $resCurso['datos'];
            $idCurso = $curso['idCurso'];
            $titulo = $curso['titulo'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resCurso['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resAsistente['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "falta idCursosAsistente - ";
}

?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
        <div class="col-xs-9 text-center">
            <h5>
                Cuotas del Asistente del Curso (<b>#<?php echo $idCurso; ?></b>) - <b><?php echo $titulo; ?></b>
                <br>
                Apellido y nombre: <b><?php echo $apellidoNombre.'</b>'; if (isset($matricula) && $matricula <> "") { echo ' - Matrícula: <b>'.$matricula.'</b>'; }?>
                </h5>
        </div>
        <div class="col-xs-3 text-right">
            <a href="curso_asistentes.php?id=<?php echo $idCurso; ?>" class="btn btn-primary" >Volver</a>
        </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php
            $resCuotas = $cursos_pdo->obtenerCuotasPorAsistente($idCursosAsistente);
            if ($resCuotas['estado']){
            ?>
                <div class="row">
                    <div class="col-md-12">
                    <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Cuota</th>
                                <th>Detalle</th>
                                <th>Fecha Vencimiento</th>
                                <th>Importe</th>
                                <th>Fecha Pago</th>
                                <th>Recibo Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          foreach ($resCuotas['datos'] as $dato) {
                              $idCursosAsistenteCuota = $dato['idCursosAsistenteCuota'];
                              $importe = $dato['importe'];
                              $cuota = $dato['cuota'];
                              $detalleCuota = $dato['detalleCuota'];
                              $fechaVencimiento = $dato['fechaVencimiento'];
                              $fechaPago = $dato['fechaPago'];
                              $recibo = $dato['recibo'];
                              if (isset($fechaPago) && $fechaPago <> "0000-00-00") {
                                $pagada = TRUE;
                              } else {
                                $pagada = FALSE;
                              }
                            ?>
                            <tr>
                        	   <td><?php echo $idCursosAsistenteCuota;?></td>
                               <td><?php echo $cuota;?></td>
                               <td><?php echo $detalleCuota;?></td>
                               <td><?php echo cambiarFechaFormatoParaMostrar($fechaVencimiento);?></td>
                               <td><?php echo $importe;?></td>
                               <td><?php if ($pagada) { echo cambiarFechaFormatoParaMostrar($fechaPago); }?></td>
                               <td><?php echo $recibo;?></td>
                               <td>
                                    <a href="curso_asistentes_cuotas_form.php?idCursosAsistente=<?php echo $idCursosAsistente; ?>&id=<?php echo $idCursosAsistenteCuota; ?>&editar" class="btn btn-info" >Modificar</a> 
                                    <?php 
                                    if (!$pagada) {
                                    ?>
                                        <a href="datosCurso/abm_curso_asistente_cuota.php?idCursosAsistente=<?php echo $idCursosAsistente; ?>&id=<?php echo $idCursosAsistenteCuota; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular()">Borrar </a>
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
                    </div>
                </div>
            <?php
            } else {
            ?>  
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="<?php echo $resAsistentes['clase']; ?>" role="alert">
                            <span class="<?php echo $resAsistentes['icono']; ?>" ></span>
                            <span><strong><?php echo $resAsistentes['mensaje']; ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';