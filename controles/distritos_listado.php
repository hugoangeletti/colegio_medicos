<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/distritoLogic.php');
$distritoLogic = new distritoLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":10,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "order": [[ 0, "asc" ]],
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
<div class="panel-heading"><h4>Listado de Distritos</h4></div>
<div class="panel-body">
    <div class="row">
        <!--<div class="col-md-12 text-right">
            <form  method="POST" action="tramites_form.php">
                <button type="submit" class="btn btn-primary" name='agregar' id='name'>Agregar distrito </button>
                <input type="hidden" id="accion" name="accion" value="1">
            </form>
        </div>-->
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resDistritos = $distritoLogic->obtenerDistritos();
    if ($resDistritos['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th style="display: none;">Id</th>
                    <th>Distrito</th>
                    <th>Presidente</th>
                    <th>Domicilio</th>
                    <th>Email</th>
                    <th>Pagina</th>
                    <th>Editar</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resDistritos['datos'] as $dato) 
                  {
                      $idDistrito = $dato['id'];
                      $distrito = $dato['distrito'];
                      $presidente = $dato['presidente'];
                      $domicilio = $dato['domicilio'];
                      $mail = $dato['mail'];
                      $pagina = $dato['pagina'];
                  ?>
                    <tr>
                        <td style="display: none;"><?php echo $idDistrito;?></td>
                        <td><?php echo $distrito;?></td>
                        <td><?php echo $presidente;?></td>
                        <td><?php echo $domicilio;?></td>
                        <td><?php echo $mail;?></td>
                        <td><?php echo $pagina;?></td>
                        <td>
                            <form  method="POST" action="distritos_form.php">
                                <button type="submit" class="btn btn-primary" name='abm_distrito' id='abm_distrito'>Editar </button>
                                <input type="hidden" id="idDistrito" name="idDistrito" value="<?php echo $idDistrito; ?>">
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