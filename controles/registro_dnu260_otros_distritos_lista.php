<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260OtrosDistritosLogic.php');
$registroDNU260OtrosDistritosLogic = new registroDNU260OtrosDistritosLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":25,
                    "order": [[ 0, "desc" ]],
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
                                "sTitle": "Listado de registro DNU 260/2020",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_inspectores.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
            });
            
  function confirmar()
  {
    if(confirm('¿Estas seguro de REACTIVAR al Registro de Extranjeros?'))
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
        <div class="<?php echo $_POST['clase']; ?>" role="alert">
            <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
            <span><?php echo $_POST['mensaje'];?></span>
        </div>
    </div>
 <?php    
}   
?> 
<div class="panel panel-primary">
<div class="panel-heading"><h4><b>Registro Extranjeros Otros Distritos con Baja</b></h4></div>
<div class="panel-body">
    <div class="row">
      <div class="col-md-9">&nbsp;</div>
      <div class="col-md-3">
          <form method="POST" action="registro_dnu260_otros_distritos_form.php">
              <div align="right">
                  <button type="submit" class="btn btn-primary btn-lg">Nueva Baja de Otro Distrito</button>
                  <input type="hidden" id="accion" name="accion" value="1">
              </div>
          </form>
      </div>
    </div>
    
    <div class="row">&nbsp;</div>
    <?php
    $resRegistro = $registroDNU260OtrosDistritosLogic->obtenerRegistrosOtrosDistritosTodos();
    if ($resRegistro['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Apellido y Nombres</th>
                    <th>Distrito</th>
                    <th>Fecha Alta</th>
                    <th>Fecha Baja</th>
                    <th>Modificar</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resRegistro['datos'] as $dato) 
                  {
                      $idRegistro = $dato['idRegistro'];
                      $numero = $dato['numero'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $fechaAlta = $dato['fechaAlta'];
                      $nacionalidad = $dato['nacionalidad'];
                      $tipoDocumento = $dato['tipoDocumento'];
                      $numeroDocumento = $dato['numeroDocumento'];
                      $numeroPasaporte = $dato['numeroPasaporte'];    
                      $fechaBaja = $dato['fechaBaja'];
                      $distrito = $dato['distrito'];
                      
                  ?>
                      <tr style="color: <?php echo $colorTR; ?>;">
                        <td><?php echo $numero;?></td>
                        <td><?php echo $apellidoNombre;?></td>
                        <td><?php echo $distrito;?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaAlta);?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaBaja);?></td>
                        <td align="center">
                          <form method="POST" action="registro_dnu260_otros_distritos_form.php?id=<?php echo $idRegistro ?>">
                              <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"> </button>
                              <input type="hidden" id="accion" name="accion" value="3">
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
        <div class="<?php echo $resRegistro['clase']; ?>" role="alert">
            <span class="<?php echo $resRegistro['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resRegistro['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';