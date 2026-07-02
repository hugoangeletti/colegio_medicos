<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eleccionesLogic.php');
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":10,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3, 4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de Elecciones",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_elecciones.pdf"
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
<div class="panel-heading"><h4><b>Elecciones</b></h4></div>
<div class="panel-body">
    <div class="row">
        <?php
    if (isset($_POST['estadoElecciones']) && $_POST['estadoElecciones'] != ""){
        $estadoElecciones = $_POST['estadoElecciones'];
    } else {
        $estadoElecciones = 'A';
    }
    ?>
    <div class="row">
        <div class="col-xs-6">
            <form method="POST" action="elecciones_lista.php">
                <div class="col-xs-6">
                    <select class="form-control" id="estadoElecciones" name="estadoElecciones" required onChange="this.form.submit()">
                        <option value="A" <?php if($estadoElecciones == "A") { echo 'selected'; } ?>>Activas</option>
                        <option value="C" <?php if($estadoElecciones == "C") { echo 'selected'; } ?>>Hist&oacute;ricas</option>
                    </select>
                </div>
            </form>    
        </div>
        <div class="col-xs-3"></div>
        <div class="col-xs-3">
            <form method="POST" action="elecciones_form.php">
                <div align="right">
                    <button type="submit" class="btn btn-success btn-lg">Nueva Elecci&oacute;n</button>
                    <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
                    <input type="hidden" id="accion" name="accion" value="1">
                </div>
            </form>
        </div>
    </div>
    <?php
    $eleccionesLogic = new elecciones();
    $resElecciones = $eleccionesLogic->obtenerEleccionesPorEstado($estadoElecciones);   //elecciones activas
    //var_dump($facturas);
    if ($resElecciones['estado']){
    ?>
        <br>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Detalle</th>
                        <th>Estado</th>
                        <th>A&ntilde;o</th>
                        <th style="width: 30px">Editar</th>
                        <th style="width: 30px">Localidades</th>
                    </tr>
                </thead>
          <tbody>
              <?php
                  foreach ($resElecciones['datos'] as $dato) 
                  {
                      $idElecciones = $dato['idElecciones'];
                      $detalle = $dato['detalle'];
                      $estado = $dato['estado'];
                      $anio = $dato['anio'];
                  ?>
                    <tr>
                	<td><?php echo $idElecciones;?></td>
			<td><?php echo $detalle;?></td>
                        <td><?php
                                switch ($estado) {
                                    case "A":
                                        ?>
                                        <div style="color: green;">Activa</div>
                                        <?php
                                        break;

                                    case "C":
                                        ?>
                                        <div style="color: red;">Hist&oacute;rica</div>
                                        <?php
                                        break;

                                    default:
                                        break;
                                }
                        ?></td>
			<td><?php echo $anio;?></td>
                        <td>
                            <div align="center">
                                <form method="POST" action="elecciones_form.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="3">
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="elecciones_localidades_lista.php">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-book center-block btn-sm"></button>
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
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
    <div class="<?php echo $resElecciones['clase']; ?>" role="alert">
        <span class="<?php echo $resElecciones['icono']; ?>" ></span>
        <span><strong><?php echo $resElecciones['mensaje']; ?></strong></span>
    </div>
<?php
}    
?>
</div>
</div>
</div>
<?php
require_once '../html/footer.php';