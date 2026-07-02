<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eleccionesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesLogic.php');
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
$continua = TRUE;
$mensaje = "";
if (isset($_POST['estadoElecciones'])) {
    $estadoElecciones = $_POST['estadoElecciones'];
} else {
    $estadoElecciones = 'A';
}
if (isset($_POST['idElecciones'])){
    $idElecciones = $_POST['idElecciones'];
} else {
    if (isset($_GET['id'])) {
        $idElecciones = $_GET['id'];
    } else {
        $continua = FALSE;
        $idElecciones = NULL;
        $mensaje .= "ACCESO INCORRECTO";
    }
}
$tituloElecciones = "";
if (isset($idElecciones)) {
    $eleccionesLogic = new elecciones();
    $resElecciones = $eleccionesLogic->obtenerEleccionesPorId($idElecciones);
    if ($resElecciones['estado']) {
        $elecciones = $resElecciones['datos'];
        $tituloElecciones = $elecciones['detalle'];
    }
}

if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php    
}   
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $tituloElecciones; ?> - Localidades</b></h4>
            </div>
            <div class="col-xs-3 text-right">
                <a href="elecciones_lista.php?" class="btn btn-info">Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-9">&nbsp;</div>
            <div class="col-xs-3">
                <form method="POST" action="elecciones_localidades_form.php">
                    <div align="right">
                        <button type="submit" class="btn btn-primary">Nueva Localidad</button>
                        <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                        <input type="hidden" id="accion" name="accion" value="1">
                    </div>
                </form>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <?php
        $eleccionesLocalidadesLogic = new eleccionesLocalidades();
        $resElecciones = $eleccionesLocalidadesLogic->obtenerLocalidadesPorIdElecciones($idElecciones);   
        //var_dump($facturas);
        if ($resElecciones['estado']){
        ?>
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Localidad</th>
                            <th>Delegados</th>
                            <th>Electores</th>
                            <th>Válidos</th>
                            <th>Anulados</th>
                            <th>En Blanco</th>
                            <th>Cociente</th>
                            <th>Editar</th>
                            <th style="width: 200px">Padrones</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php
                    foreach ($resElecciones['datos'] as $dato) {
                        $idEleccionesLocalidad = $dato['idEleccionesLocalidad'];
                        $codLocalidad = $dato['codigoLocalidad'];
                        $cantDelegados = $dato['cantDelegados'];
                        $cantElectores = $dato['cantElectores'];
                        $cantValidos = $dato['cantValidos'];
                        $cantAnulados = $dato['cantAnulados'];
                        $cantEnBlanco = $dato['cantEnBlanco'];
                        $cociente = $dato['cociente'];
                        $localidadDetalle = $dato['localidadDetalle'];
                        ?>
                        <tr>
                    	    <td><?php echo $idEleccionesLocalidad;?></td>
                			<td><?php echo $localidadDetalle.' ('.$codLocalidad.')';?></td>
                			<td><?php echo $cantDelegados;?></td>
                			<td><?php echo $cantElectores;?></td>
                			<td><?php echo $cantValidos;?></td>
                			<td><?php echo $cantAnulados;?></td>
                			<td><?php echo $cantEnBlanco;?></td>
                			<td><?php echo $cociente;?></td>
                            <td style="text-align: center;">
                                <form method="POST" action="elecciones_localidades_form.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="3">
                                    <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
                                </form>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Padrones<span class="caret"></span></button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <a href="elecciones_generar_padron.php?id=<?php echo $idEleccionesLocalidad; ?>" class="btn btn-info">Generar padron</a>
                                        </li>
                                        <li>
                                            <a href="elecciones_imprimir_padron.php?id=<?php echo $idEleccionesLocalidad; ?>&editar" class="btn btn-info">Imprimir padron</a>
                                        </li>
                                    </ul>
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
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';