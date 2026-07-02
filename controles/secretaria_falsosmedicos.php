<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/falsosMedicosLogic.php');
$falsosMedicosLogic = new falsosMedicosLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":50,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
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
                                "sTitle": "Listado de falsos medicos",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_falsosmedicos.pdf"
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
<div class="panel-heading"><h4><b>Denuncia de Falsos Médicos</b></h4></div>
<div class="panel-body">
    <div class="row">
        <div class="col-xs-6">
<!--            <form id="formImprimir" name="formImprimir" method="POST" onSubmit="" action="secretaria_falsosmedicos_imprimir.php">
                <button type="submit"  class="btn btn-info " >Imprimir lista</button>
            </form>-->
        </div>
        <div class="col-xs-3"></div>
        <div class="col-xs-3">
            <form method="POST" action="secretaria_falsosmedicos_form.php">
                <div align="right">
                <button type="submit" class="btn btn-success btn-lg">Nueva Denuncia</button>
                <input type="hidden" id="accion" name="accion" value="1">
                </div>
            </form>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resFalsosMedicos = $falsosMedicosLogic->obtenerFalsosMedicosPorEstado('A');
    if ($resFalsosMedicos['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Apellido y Nombres</th>
                    <th>Documento</th>
                    <th>Matricula</th>
                    <th>Remitido por</th>
                    <th>Observaciones</th>
                    <th>Fecha Denuncia</th>
                    <th>Editar</th>
                    <th>Anular</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resFalsosMedicos['datos'] as $dato) 
                  {
                      $idFalsoMedicos = $dato['id'];
                      $apellido = $dato['apellido'];
                      $nombre = $dato['nombre'];
                      $nroDocumento = $dato['nroDocumento'];
                      $matricula = $dato['matricula'];
                      $remitido = $dato['remitido'];
                      $observaciones = $dato['observaciones'];
                      $fechaDenuncia = $dato['fechaDenuncia'];
                      $fechaCarga = $dato['fechaCarga'];
                  ?>
                    <tr>
                        <td><?php echo $apellido.' '.$nombre;?></td>
                        <td><?php echo $nroDocumento;?></td>
                        <td><?php echo $matricula;?></td>
                        <td><?php echo $remitido;?></td>
                        <td><?php echo $observaciones;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaDenuncia);?></td>
                        <td>
                            <div align="center">
                                <form method="POST" action="secretaria_falsosmedicos_form.php?accion=3&id=<?php echo $idFalsoMedicos; ?>">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-pencil center-block btn-sm"></button>
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="secretaria_falsosmedicos_form.php?accion=2&id=<?php echo $idFalsoMedicos; ?>">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"></button>
                                </form>
                            </div>    
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
        <div class="<?php echo $resFalsosMedicos['clase']; ?>" role="alert">
            <span class="<?php echo $resFalsosMedicos['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resFalsosMedicos['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';