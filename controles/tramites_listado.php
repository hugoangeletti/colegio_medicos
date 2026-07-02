<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/tramiteLogic.php');
$tramiteLogic = new tramiteLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":10,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "order": [[ 0, "desc" ]],
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de consejero",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_consejeros.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
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
            <form  method="POST" action="tramites_form.php">
                <button type="submit" class="btn btn-primary" name='agregar' id='name'>Agregar listado </button>
                <input type="hidden" id="accion" name="accion" value="1">
            </form>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resTramites = $tramiteLogic->obtenerTramites('G');
    if ($resTramites['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Detalle</th>
                    <th>Fecha</th>
                    <th>Fecha desde</th>
                    <th>Fecha hasta</th>
                    <th>Detalle</th>
                    <th>Imprimir</th>
                    <!--<th>Enviar mail</th>-->
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resTramites['datos'] as $dato) 
                  {
                      $idTramite = $dato['idTramite'];
                      $tipoTramite = $dato['tipoTramite'];
                      $detalle = $dato['detalle'];
                      $fecha = $dato['fecha'];
                      $fechaDesde = $dato['fechaDesde'];
                      $fechaHasta = $dato['fechaHasta'];
                  ?>
                    <tr>
                        <td><?php echo $idTramite;?></td>
                        <td><?php echo $detalle;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fecha);?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaDesde);?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaHasta);?></td>
                        <td>
                            <form  method="POST" action="tramites_detalle.php">
                                <button type="submit" class="btn btn-primary" name='detalle' id='detalle'>Ver </button>
                                <input type="hidden" id="id" name="id" value="<?php echo $idTramite; ?>">
                            </form>
                        </td>
                        <td>
                            <?php 
                            if ($detalle == 'Altas') {
                                $link = "datosTramites/generar_pdf_altas.php";
                            } else {
                                $link = "tramites_detalle_imprimir.php";
                            }
                            ?>
                            <form  method="POST" action="<?php echo $link; ?>" target="_BLANK">
                                <button type="submit" class="btn btn-primary" name='detalle' id='detalle'>PDF </button>
                                <input type="hidden" id="id" name="id" value="<?php echo $idTramite; ?>">
                            </form>
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