<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoResidenteLogic.php');
$colegiadoResidenteLogic = new colegiadoResidenteLogic();
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

if (isset($_POST['tipoFiltro']) && $_POST['tipoFiltro'] != ""){
  $tipoFiltro = $_POST['tipoFiltro'];
} else {
  $tipoFiltro = "VIGENTE";
}
?> 
<div class="panel panel-info">
<div class="panel-heading"><h4><b>Registro de Residentes</b></h4></div>
<div class="panel-body">
    <div class="row">
      <div class="col-md-3">
        <form method="POST" action="colegiado_registro_residente.php">
          <select class="form-control" id="tipoFiltro" name="tipoFiltro" required onChange="this.form.submit()">
              <option value="VIGENTE" <?php if($tipoFiltro == "VIGENTE") { echo 'selected'; } ?>>Vigentes</option>
              <option value="NO_VIGENTE" <?php if($tipoFiltro == "NO_VIGENTE") { echo 'selected'; } ?>>No vigentes</option>
              <option value="TODOS" <?php if($tipoFiltro == "TODOS") { echo 'selected'; } ?>>TODOS</option>
          </select>
        </form>
      </div>
      <div class="col-md-9">&nbsp;</div>
    </div>
    
    <div class="row">&nbsp;</div>
    <?php
    $resRegistro = $colegiadoResidenteLogic->obtenerColegiadosResidentes($tipoFiltro);
    if ($resRegistro['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Matrícula</th>
                    <th>Apellido y Nombres</th>
                    <th>Fecha Inicio</th>
                    <th>Opción</th>
                    <th>Entidad</th>
                    <th>Año de residencia</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resRegistro['datos'] as $dato) 
                  {
                      $idColegiadoResidente = $dato['idColegiadoResidente'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $fechaInicio = $dato['fechaInicio'];
                      $matricula = $dato['matricula'];
                      $nombreEntidad = $dato['nombreEntidad'];
                      $anio = $dato['anio'];
                      switch ($anio) {
                        case 1:
                          $anioResidencia = 'Residencia 1° nivel - 1er Año';
                          break;
                        
                        case 2:
                          $anioResidencia = 'Residencia 1° nivel - 2do Año';
                          break;
                        
                        case 3:
                          $anioResidencia = 'Residencia 1° nivel - 3er Año';
                          break;
                        
                        case 4:
                          $anioResidencia = 'Residencia 1° nivel - 4to Año';
                          break;
                        
                        case 5:
                          $anioResidencia = 'Residencia 1° nivel - Jefatura';
                          break;
                        
                        case 6:
                          $anioResidencia = 'Residencia 2° nivel - 1er Año';
                          break;
                        
                        case 7:
                          $anioResidencia = 'Residencia 2° nivel - 2do Año';
                          break;
                        
                        case 8:
                          $anioResidencia = 'Residencia 2° nivel - 3er Año';
                          break;
                        
                        case 9:
                          $anioResidencia = 'Residencia 2° nivel - Jefatura';
                          break;
                        
                        default:
                          $anioResidencia = 'No ingresado';
                          break;
                      }
                      $opcion = $dato['opcion'];    
                      
                      if ($dato['fechaFin'] < date('Y-m-d')) {
                          $estado = "NO VGENTE";
                          $colorTR = "red";
                      } else {
                          $estado = "VGENTE";
                        $colorTR = "green";
                      }

                  ?>
                      <tr style="color: <?php echo $colorTR; ?>;">
                        <td><?php echo $idColegiadoResidente;?></td>
                        <td><?php echo $matricula;?></td>
                        <td><?php echo $apellidoNombre;?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar(substr($fechaInicio, 0, 10));?></td>
                        <td><?php echo $opcion;?></td>
                        <td><?php echo $nombreEntidad;?></td>
                        <td><?php echo $anioResidencia;?></td>
                        <td><?php echo $estado;?></td>
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