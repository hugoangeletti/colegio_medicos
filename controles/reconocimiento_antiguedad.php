<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reconocimientoAntiguedadLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
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
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Listado de Actos</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            $actosLogic = new reconocimientoAntiguedadLogic();
            $resActos = $actosLogic->obtenerActos();            
            ?>
            <div class="row">
                <div class="col-xs-6">
                </div>
                <div class="col-xs-3"></div>
                <div class="col-xs-1 text-center">
                    <a href="reconocimiento_antiguedad_form.php?agregar" class="btn btn-info" >Agregar Acto</a>
                </div>
                <div class="col-xs-2 text-center">
                </div>
            </div>
            <?php
            if ($resActos['estado']){
            ?>
                <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Año</th>
                                <th>Antigüedad</th>
                                <th>Fecha Acto</th>
                                <th>Lugar Acto</th>
                                <th style="width: 600px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resActos['datos'] as $dato) {
                          $idReconocimientoAntiguedad = $dato['idReconocimientoAntiguedad'];
                          $anioActo = $dato['anioActo'];
                          $lugarActo = $dato['lugarActo'];
                          $fechaActo = $dato['fechaActo'];
                          $antiguedad = $dato['antiguedad'];
                        ?>
                        <tr>
                	       <td><?php echo $idReconocimientoAntiguedad;?></td>
                           <td><?php echo $anioActo;?></td>
                           <td><?php echo $antiguedad;?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($fechaActo);?></td>
                           <td><?php echo $lugarActo;?></td>
                           <td style="width: 600px;">
                                <a href="reconocimiento_antiguedad_form.php?id=<?php echo $idReconocimientoAntiguedad; ?>&editar" class="btn btn-info">Editar acto</a>
                                <a href="reconocimiento_antiguedad_detalle.php?id=<?php echo $idReconocimientoAntiguedad; ?>" class="btn btn-info">Ver colegiados</a>
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
                <div class="<?php echo $resActos['clase']; ?>" role="alert">
                    <span class="<?php echo $resActos['icono']; ?>" ></span>
                    <span><strong><?php echo $resActos['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';