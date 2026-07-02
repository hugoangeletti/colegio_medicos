<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoRematriculacionLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "desc" ]],
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
    if(confirm('¿Estas seguro de ANULAR EL REGISTRO?'))
        return true;
    else
        return false;
}

</script>

<?php
$continua = TRUE;
$mensaje = "";
$colegiadoRematriculacionLogic = new colegiadoRematriculacionLogic();
$resRematriculacion = $colegiadoRematriculacionLogic->obtenerRematriculacionVigente();
if ($resRematriculacion['estado']) {
    if (sizeof($resRematriculacion['datos']) > 0) {
        $rematriculacion = $resRematriculacion['datos'];
        $idRematriculacionAnual = $rematriculacion['idRematriculacionAnual'];
        $anioRematriculacion = $rematriculacion['anio'];
    } else {
        $idRematriculacionAnual = NULL;
        $anioRematriculacion = NULL;
        $mensaje .= $resRematriculacion['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= $resRematriculacion['mensaje'];
}
if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
}   

if (isset($_POST['idRematriculacionAnual']) && $_POST['idRematriculacionAnual'] <> "") {
    $idRematriculacionAnualSeleccioanda = $_POST['idRematriculacionAnual'];
} else {
    $idRematriculacionAnualSeleccioanda = $idRematriculacionAnual;
}
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Matrículas por Rematriculación</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <form method="POST" action="rematriculacion_listado.php">
                    <label for="idRematriculacionAnual">Seleccione Año de Rematriculación</label>
                    <select class="form-control" id="idRematriculacionAnual" name="idRematriculacionAnual" required onChange="this.form.submit()" required>
                        <option value="" selected>Seleccione</option>
                        <?php
                        $resRematriculaciones = $colegiadoRematriculacionLogic->obtenerRematriculaciones();
                        if ($resRematriculaciones['estado']) {
                            foreach ($resRematriculaciones['datos'] as $dato) {
                            ?>
                                <option value="<?php echo $dato['idRematriculacionAnual']; ?>" <?php if($dato['idRematriculacionAnual'] == $idRematriculacionAnualSeleccioanda) { echo 'selected'; } ?>><?php echo $dato['anio']; ?></option>
                            <?php
                            }
                        } 
                        ?>
                    </select>
                </form>    
            </div>
            <div class="col-md-6 text-center">
                <?php 
                if ($idRematriculacionAnualSeleccioanda == $idRematriculacionAnual) {
                ?>
                    <h3>Rematriculación vigente</h3>
                <?php 
                }
                ?>
            </div>
            <div class="col-md-3 text-right">
                <?php 
                if ($idRematriculacionAnualSeleccioanda == $idRematriculacionAnual) {
                ?>
                    <br>
                    <a href="rematriculacion_form.php?id=<?php echo $idRematriculacionAnual;?>&agregar" class="btn btn-primary" >Agregar Matrícula</a>
                <?php 
                }
                ?>
            </div>
        </div>
        <?php
        $resColegiados = $colegiadoRematriculacionLogic->obtenerColegiadosPorIdRematriculacion($idRematriculacionAnualSeleccioanda);   
        if ($resColegiados['estado'] && sizeof($resColegiados['datos']) > 0){
        ?>
            <br>
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Fecha Carga</th>
                            <th>Matrícula</th>
                            <th>Apellido y Nombre</th>
                            <th>Estado matricular</th>
                            <th style="width: 30px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
              <tbody>
                  <?php
                  foreach ($resColegiados['datos'] as $dato) {
                      $idRematriculacionColegiado = $dato['idRematriculacionColegiado'];
                      $fechaCarga = $dato['fechaCarga'];
                      $idColegiado = $dato['idColegiado'];
                      $matricula = $dato['matricula'];
                      $estadoMatricular = $dato['estadoMatricular'];
                      $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                      $detalleMovimiento = $dato['detalleMovimiento'];
                      ?>
                    <tr>
                	   <td><?php echo $idRematriculacionColegiado;?></td>
                       <td><?php echo cambiarFechaFormatoParaMostrar($fechaCarga);?></td>
                       <td><?php echo $matricula;?></td>
                       <td><?php echo $apellidoNombre;?></td>
                       <td><?php echo $detalleMovimiento;?></td>
                       <td style="text-align: center;"></td>
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
            <div class="<?php echo $resColegiados['clase']; ?>" role="alert">
                <span class="<?php echo $resColegiados['icono']; ?>" ></span>
                <span><strong><?php echo $resColegiados['mensaje']; ?></strong></span>
            </div>
        <?php
        }    
        ?>
    </div>
</div>
<?php
require_once '../html/footer.php';