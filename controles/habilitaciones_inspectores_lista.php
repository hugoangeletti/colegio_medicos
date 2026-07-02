<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":100,
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
                                "sTitle": "Listado de inspectores",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_inspectores.pdf"
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

if (isset($_GET['consulta']) && $_GET['consulta'] == 'ok') {
    //ingresa por secretaria
    $soloConsulta = FALSE;
} else {
    $soloConsulta = TRUE;
}
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Listado de Inspectores de Consultorios</b></h4></div>
<div class="panel-body">
    <?php
    if (isset($_POST['estadoInspectores']) && $_POST['estadoInspectores'] != ""){
        $estadoInspectores = $_POST['estadoInspectores'];
    } else {
        $estadoInspectores = 'A';
    }
    ?>
    <div class="row">
        <div class="col-xs-6">
            <form method="POST" action="habilitaciones_inspectores_lista.php">
                <div class="col-xs-6">
                    <select class="form-control" id="estadoInspectores" name="estadoInspectores" required onChange="this.form.submit()">
                        <option value="A" <?php if($estadoInspectores == "A") { echo 'selected'; } ?>>Activas</option>
                        <option value="B" <?php if($estadoInspectores == "B") { echo 'selected'; } ?>>Hist&oacute;ricas</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="col-xs-3"></div>
        <div class="col-xs-3">
            <form method="POST" action="habilitaciones_inspectores_form.php">
                <div align="right">
                    <button type="submit" class="btn btn-success btn-lg">Nuevo Inspector</button>
                    <input type="hidden" id="accion" name="accion" value="1">
                    <input type="hidden" id="estadoInspectores" name="estadoInspectores" value="A">
                </div>
            </form>
        </div>
    </div>
    
    <div class="row">&nbsp;</div>
    <?php
    $resInspectores = $habilitacionConsultorioLogic->obtenerInspectores($estadoInspectores);
    if ($resInspectores['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Apellido y Nombres</th>
                    <th>Matrícula</th>
                    <th style="text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resInspectores['datos'] as $dato) 
                  {
                      $idInspector = $dato['idInspector'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $matricula = $dato['matricula'];
                      if ($estadoInspectores == 'A') {
                          $botonAccion = 'Eliminar';
                          $iconoAccion = 'btn-danger glyphicon glyphicon-erase';
                      } else {
                          $botonAccion = 'Activar';
                          $iconoAccion = 'btn-primary glyphicon glyphicon-check';
                      }
                  ?>
                    <tr>
                        <td><?php echo $apellidoNombre;?></td>
                        <td><?php echo $matricula;?></td>
                        <td>
                            <div align="center">
                                <form method="POST" action="habilitaciones_inspectores_form.php">
                                    <button type="submit" class="btn <?php echo $iconoAccion; ?> center-block btn-sm"> <?php echo $botonAccion; ?> </button>
                                    <input type="hidden" id="accion" name="accion" value="2">
                                    <input type="hidden" id="idInspector" name="idInspector" value="<?php echo $idInspector; ?>">
                                    <input type="hidden" id="estadoInspectores" name="estadoInspectores" value="<?php echo $estadoInspectores; ?>">
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
        <div class="<?php echo $resInspectores['clase']; ?>" role="alert">
            <span class="<?php echo $resInspectores['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resInspectores['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';