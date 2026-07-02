<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/patologiasLogic.php');
$patologiasLogic = new patologiasLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":10,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "order": [[ 1, "asc" ]],
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
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Listado de Patologías</b></h4></div>
<div class="panel-body">
    <div class="row">&nbsp;</div>
    <?php
    $resPatologias = $patologiasLogic->obtenerPatologias();
    if ($resPatologias['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Patología</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resPatologias['datos'] as $dato) 
                  {
                      $codigo = $dato['codigo'];
                      $nombre = $dato['nombre'];
                  ?>
                    <tr>
                        <td><?php echo $codigo;?></td>
                        <td><?php echo $nombre;?></td>
                   </tr>
                  <?php
                  }
              ?>
              
	   </tbody>
	  </table>
    <?php
    } else {
      ?>
        <div class="<?php echo $resPatologias['clase']; ?>" role="alert">
            <span class="<?php echo $resPatologias['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resPatologias['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';