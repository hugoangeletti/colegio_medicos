<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/envios_caja_medicosLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });
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
<div class="panel panel-info">
    <div class="panel-heading"><h4>Listado de Movimientos matriculares y altas</h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12 text-right">
                <a href="envios_caja_medicos_form.php" class="btn btn-primary" name="agregar" id="agregar">Agregar listado</a>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <?php
        $envioLogic = new enviosCajaMedicosLogic();
        $resTramites = $envioLogic->obtenerEnvios();
        if ($resTramites['estado']){
        ?>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha</th>
                        <th>Fecha desde</th>
                        <th>Fecha hasta</th>
                        <th>Mail de envío</th>
                        <th>Path</th>
                        <th>Archivos</th>
                        <th>Colegiados</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                      foreach ($resTramites['datos'] as $dato) 
                      {
                          $idEnviosCajaMedicos = $dato['idEnviosCajaMedicos'];
                          $fechaEnvio = $dato['fechaEnvio'];
                          $fechaDesde = $dato['fechaDesde'];
                          $fechaHasta = $dato['fechaHasta'];
                          $mail = $dato['mail'];
                          $path = $dato['path'];
                          $nombreArchivo = $dato['nombreArchivo'];
                          $nombrePdf = $dato['nombrePdf'];
                      ?>
                        <tr>
                            <td><?php echo $idEnviosCajaMedicos;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar(substr($fechaEnvio, 0, 10));?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaDesde);?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaHasta);?></td>
                            <td><?php echo $mail;?></td>
                            <td><?php echo $path;?></td>
                            <td><?php echo $nombreArchivo.'<br>'.$nombrePdf;?></td>
                            <td>
                                <a href="envios_caja_medicos_detalle.php?id=<?php echo $idEnviosCajaMedicos; ?>" class="btn btn-primary" name="ver_detalle" id="ver_detalle">Ver detalle</a>
                            </td>
                            <td>
                                <a href="envios_caja_medicos_imprimir.php?id=<?php echo $idEnviosCajaMedicos; ?>" class="btn btn-primary" name="ver_pdf" id="ver_pdf">PDF</a>
                                <a href="datosTramites/descargar_archivos.php?id=<?php echo $idEnviosCajaMedicos ?>&descargar" class="btn btn-primary">Descargar Archivo</a>
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
            <div class="<?php echo $resTramites['clase']; ?>" role="alert">
                <span class="<?php echo $resTramites['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resTramites['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
    ?>
    </div>
</div>
<?php
require_once '../html/footer.php';