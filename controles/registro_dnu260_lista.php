<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260Logic.php');
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

if (isset($_SESSION['distritoFiltro'])) {
  if (isset($_POST['distritoFiltro']) && $_POST['distritoFiltro'] != ""){
    $_SESSION['distritoFiltro'] = $_POST['distritoFiltro'];
  } 
} else {
  $_SESSION['distritoFiltro'] = '1';
}

$distritoFiltro = $_SESSION['distritoFiltro'];
?> 
<div class="panel panel-danger">
<div class="panel-heading"><h4><b>Registro DNU 260/2020</b></h4></div>
<div class="panel-body">
    <div class="row">
      <div class="col-md-3">
        <form method="POST" action="registro_dnu260_lista.php">
          <select class="form-control" id="distritoFiltro" name="distritoFiltro" required onChange="this.form.submit()">
              <option value="1" <?php if($distritoFiltro == "1") { echo 'selected'; } ?>>Registrados en Distrito I</option>
              <option value="0" <?php if($distritoFiltro == "0") { echo 'selected'; } ?>>Registrados Inscriptos</option>
          </select>
        </form>
      </div>
      <div class="col-md-3">&nbsp;</div>
      <div class="col-md-3 text-rigth">
          <form method="POST" action="registro_dnu260_form.php">
              <div align="right">
                  <button type="submit" class="btn btn-success btn-lg">Nuevo Registro en Distrito I</button>
                  <input type="hidden" id="accion" name="accion" value="1">
                  <input type="hidden" id="distrito" name="distrito" value="1">
              </div>
          </form>
      </div>
      <div class="col-md-3">
          <form method="POST" action="registro_dnu260_form.php">
              <div align="right">
                  <button type="submit" class="btn btn-primary btn-lg">Nuevo Registro de Otro Distrito</button>
                  <input type="hidden" id="accion" name="accion" value="1">
                  <input type="hidden" id="distrito" name="distrito" value="0">
              </div>
          </form>
      </div>
    </div>
    
    <div class="row">&nbsp;</div>
    <?php
    $resRegistro = $colegiadoConsultorioLogic->obtenerTodos($distritoFiltro);
    if ($resRegistro['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Apellido y Nombres</th>
                    <th>Fecha Alta</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                    <th>Datos Laborales</th>
                    <th>Modificar</th>
                    <th>Renovar</th>
                    <th>Baja</th>
                    <th>Certificado</th>
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
                      $fechaIngreso = $dato['fechaIngreso'];
                      $fechaVencimiento = $dato['fechaVencimiento'];
                      $distrito = $dato['distrito'];
                      
                      $estado = "Activo";
                      $colorTR = "green";
                      if ($dato['estado'] == "B") {
                          $estado = "BAJA";
                          $colorTR = "red";
                      } else {
                          if ($fechaVencimiento < date('Y-m-d')) {
                            $estado = 'VENCIDO';
                            $colorTR = "blue";
                          }
                      }

                  ?>
                      <tr style="color: <?php echo $colorTR; ?>;">
                        <td><?php echo $numero;?></td>
                        <td><?php echo $apellidoNombre;?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar(substr($fechaAlta, 0, 10));?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaVencimiento);?></td>
                        <td><?php echo $estado;?></td>
                        <td align="center">
                          <form method="POST" action="registro_dnu260_laboral_lista.php?id=<?php echo $idRegistro ?>">
                              <button type="submit" class="btn btn-default glyphicon glyphicon-eye-open center-block btn-sm"> </button>
                          </form>
                        </td>
                        <td align="center">
                          <form method="POST" action="registro_dnu260_form.php?id=<?php echo $idRegistro ?>">
                              <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"> </button>
                              <input type="hidden" id="accion" name="accion" value="3">
                              <input type="hidden" id="distrito" name="distrito" value="<?php echo $distrito; ?>">
                          </form>
                        </td>
                        <td align="center">
                          <form method="POST" action="registro_dnu260_renovar.php?idRegistro=<?php echo $idRegistro ?>">
                              <button type="submit" class="btn btn-info glyphicon glyphicon-pencil center-block btn-sm"></button>
                          </form>
                        </td>
                        <td align="center">
                          <?php
                          if ($estado == "Activo" || $estado == "VENCIDO") {
                          ?>
                            <form method="POST" action="registro_dnu260_baja.php?idRegistro=<?php echo $idRegistro ?>">
                                <button type="submit" class="btn btn-danger glyphicon glyphicon-pencil  btn-sm"> </button>
                                <input type="hidden" id="accion" name="accion" value="2">
                            </form>
                          <?php
                          } else {
                          ?>
                            <form method="POST" action="datosRegistroDnu260/baja_registro.php?id=<?php echo $idRegistro ?>">
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirmar()">Reactivar </button>
                                <input type="hidden" id="accion" name="accion" value="1">
                            </form>
                          <?php
                          }
                          ?>
                        </td>
                        <td>
                            <div class="center-block">
                                <form method="POST" action="registro_dnu260_certificado.php?id=<?php echo $idRegistro ?>" >
                                    <button type="submit" class="btn btn-warning glyphicon glyphicon-list-alt center-block btn-sm"></button>
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