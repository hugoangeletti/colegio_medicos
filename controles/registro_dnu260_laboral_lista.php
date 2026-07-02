<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();
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
    if(confirm('¿Estas seguro de cambiar el estado al Registro de Extranjeros?'))
      return true;
    else
      return false;
  }   
</script>

<?php
$titulo = "";
$continuar = TRUE;
if (isset($_GET['id']) && $_GET['id'] > 0) {
  $idRegistro = $_GET['id'];

  $resRegistro = $registroDNU260Logic->obtenerRegistroPorId($idRegistro);
  if ($resRegistro['estado']) {
    $registro = $resRegistro['datos'];
    $apellido = $registro['apellido'];
    $nombre = $registro['nombre'];
    $numero = $registro['numero'];
    $titulo .= " Registro Número: ".$numero.' - '.trim($apellido).', '.trim($nombre); 
  } else {
    $continuar = FALSE;
  }
} else {
  $continuar = FALSE;
}

if ($continuar) {
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
<div class="panel panel-danger">
<div class="panel-heading">
  <div class="row">
    <div class="col-md-9">
        <h4><b>Datos laborales del <?php echo $titulo ?></b></h4>
    </div>
    <div class="col-md-3 text-right">
        <form  method="POST" action="registro_dnu260_lista.php">
            <button type="submit" class="btn btn-danger" name='volver' id='name'>Volver al listado </button>
        </form>
    </div>
  </div>
</div>
<div class="panel-body">
    <div class="row">
        <div class="col-xs-9">&nbsp;</div>
        <div class="col-xs-3">
            <form method="POST" action="registro_dnu260_laboral_form.php?idRegistro=<?php echo $idRegistro; ?>">
                <div align="right">
                    <button type="submit" class="btn btn-success btn-lg">Nuevo Lugar de trabajo</button>
                    <input type="hidden" id="accion" name="accion" value="1">
                </div>
            </form>
        </div>
    </div>
    
    <div class="row">&nbsp;</div>
    <?php
    $resRegistro = $registroDNU260Logic->obtenerDatosLaborales($idRegistro);
    if ($resRegistro['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Entidad</th>
                    <th>Domicilio</th>
                    <th>Localidad</th>
                    <th>Fecha Carga</th>
                    <th>Estado</th>
                    <th>Modificar</th>
                    <th>Borrar</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resRegistro['datos'] as $dato) 
                  {
                      $idRegistroLaboral = $dato['idRegistroLaboral'];
                      $entidad = $dato['entidad'];
                      $domicilioProfesional = $dato['domicilioProfesional'];
                      $localidadProfesional = $dato['localidadProfesional'];
                      $fechaCarga = $dato['fechaCarga'];
                      $estado = $dato['estado'];
                      if ($dato['estado'] == "BAJA") {
                          $estado = "BAJA";
                          $colorTR = "red";
                      } else {
                        $estado = "Activo";
                        $colorTR = "green";
                      }
                  ?>
                      <tr style="color: <?php echo $colorTR; ?>;">
                        <td><?php echo $idRegistroLaboral;?></td>
                        <td><?php echo $entidad;?></td>
                        <td><?php echo $domicilioProfesional;?></td>
                        <td><?php echo $localidadProfesional;?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar(substr($fechaCarga, 0, 10));?></td>
                        <td><?php echo $estado; ?></td>
                        <td align="center">
                          <form method="POST" action="registro_dnu260_laboral_form.php?idRegistro=<?php echo $idRegistro; ?>&id=<?php echo $idRegistroLaboral ?>">
                              <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"> </button>
                              <input type="hidden" id="accion" name="accion" value="3">
                          </form>
                        </td>
                        <td align="center">
                          <form method="POST" action="datosRegistroDnu260/abm_laboral.php">
                              <button type="submit" class="btn btn-danger glyphicon glyphicon-pencil btn-sm" onclick="return confirmar()"></button>
                              <input type="hidden" name="idRegistro" id="idRegistro" value="<?php echo $idRegistro; ?>" />
                              <input type="hidden" name="id" id="id" value="<?php echo $idRegistroLaboral; ?>" />
                              <input type="hidden" id="accion" name="accion" value="2">
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
}
?>

<?php
require_once '../html/footer.php';