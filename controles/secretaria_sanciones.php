<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoSancionLogic.php');
$colegiadoSancionLogic = new colegiadoSancionLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 1, "asc" ]],
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
                                "sTitle": "Listado de medicos con sanciones",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_sanciones.pdf"
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
<div class="panel-heading"><h4><b>Sanciones</b></h4></div>
<div class="panel-body">
    <?php
    if (isset($_POST['estadoSancion']) && $_POST['estadoSancion'] != ""){
        $estadoSancion = $_POST['estadoSancion'];
    } else {
        $estadoSancion = 'A';
    }
    ?>
    <div class="row">
        <div class="col-xs-6">
            <form method="POST" action="secretaria_sanciones.php">
                <div class="col-xs-6">
                    <select class="form-control" id="estadoSancion" name="estadoSancion" required onChange="this.form.submit()">
                        <option value="A" <?php if($estadoSancion == "A") { echo 'selected'; } ?>>Activas</option>
                        <option value="B" <?php if($estadoSancion == "B") { echo 'selected'; } ?>>Anuladas</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="col-xs-3"></div>
        <div class="col-xs-3 text-right">
            <form method="POST" action="secretaria_sanciones_form.php?accion=1">
                <button type="submit" class="btn btn-success btn-lg">Nueva Sanción</button>
            </form>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resSanciones = $colegiadoSancionLogic->obtenerSanciones($estadoSancion);
    if ($resSanciones['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Apellido y Nombres</th>
                    <th>Matricula</th>
                    <th>Detalle</th>
                    <th>Fecha Desde</th>
                    <th>Fecha Hasta</th>
                    <th>Editar</th>
                    <th>Anular</th>
                    <th>Costas</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resSanciones['datos'] as $dato) 
                  {
                      $idColegiadoSancion = $dato['idColegiadoSancion'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      if (isset($apellidoNombre) && $apellidoNombre <> '') {
                        $matricula = $dato['matricula'];
                        $detalle = $dato['detalle'];
                        $fechaDesde = $dato['fechaDesde'];
                        $fechaHasta = $dato['fechaHasta'];
                        $provincia = $dato['provincia'];
                        $cantidadGalenos = $dato['cantidadGalenos'];
                        $fechaPago = $dato['fechaPago'];
                        $idCostas = $dato['idCostas'];
                  ?>
                    <tr>
                        <td><?php echo $idColegiadoSancion;?></td>
                        <td><?php echo $apellidoNombre;?></td>
                        <td><?php echo $matricula;?></td>
                        <td><?php echo $detalle;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaDesde);?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaHasta);?></td>
                        <td>
                            <div align="center">
                                <form method="POST" action="secretaria_sanciones_form.php?accion=3&idColegiadoSancion=<?php echo $idColegiadoSancion; ?>">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-pencil center-block btn-sm"></button>
                                    <input type="hidden" name="estadoSancion" id="estadoSancion" value="<?php echo $estadoSancion; ?>" />
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="secretaria_sanciones_form.php?accion=2&idColegiadoSancion=<?php echo $idColegiadoSancion; ?>">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"></button>
                                    <input type="hidden" name="estadoSancion" id="estadoSancion" value="<?php echo $estadoSancion; ?>" />
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <?php 
                                if (isset($cantidadGalenos) && $cantidadGalenos > 0) {
                                    if (isset($fechaPago) && $fechaPago <> ''){
                                        ?>
                                        <b style="color: #00F; ">Abonada</b>
                                        <?php
                                    } else {
                                        ?>
                                        <form method="POST" action="secretaria_sanciones_costas.php?accion=3&idColegiadoSancion=<?php echo $idColegiadoSancion; ?>&idCostas=<?php echo $idCostas; ?>">
                                            <button type="submit" class="btn btn-primary center-block btn-sm">Ver </button>
                                        </form>
                                        <?php
                                    }
                                } else {
                                ?>
                                    <form method="POST" action="secretaria_sanciones_costas.php?idColegiadoSancion=<?php echo $idColegiadoSancion; ?>">
                                        <button type="submit" class="btn btn-success center-block btn-sm">Agregar</button>
                                    </form>
                                <?php 
                                }
                                ?>
                            </div>    
                        </td>
                   </tr>
                  <?php
                      }
                  }
              ?>
              
	   </tbody>
	  </table>
    <?php
    } else {
      ?>
        <div class="<?php echo $resSanciones['clase']; ?>" role="alert">
            <span class="<?php echo $resSanciones['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resSanciones['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';