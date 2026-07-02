<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eleccionesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesListasLogic.php');
$eleccionesLocalidadesListasLogic = new eleccionesLocalidadesListasLogic();
require_once ('../dataAccess/eleccionesLocalidadesIntegrantesLogic.php');
$eleccionesLocalidadesIntegrantesLogic = new eleccionesLocalidadesIntegrantesLogic();
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
if (isset($_POST['idEleccionesLocalidad'])){
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
    $idElecciones = $_POST['idElecciones'];
} else {
    $idEleccionesLocalidad = NULL;
    $eleccionesLogic = new elecciones();
    $idElecciones = $eleccionesLogic->eleccionesActiva();
} 

if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}

if (isset($idElecciones)) {
    $tituloElecciones = "";
    $eleccionesLogic = new elecciones();
    $resElecciones = $eleccionesLogic->obtenerEleccionesPorId($idElecciones);
    if ($resElecciones['estado']) {
        $elecciones = $resElecciones['datos'];
        $tituloElecciones = $elecciones['detalle'];
    }
?>
<div class="panel panel-default">
<div class="panel-heading"><h4><b><?php echo $tituloElecciones; ?> - Listas por Localidad</b></h4></div>
<div class="panel-body">
    <div class="row">
        <div class="col-xs-6">
            <form method="POST" action="elecciones_listas_lista.php">
                <div class="col-xs-6">
                    <b>Localidad: </b>
                    <select class="form-control" id="idEleccionesLocalidad" name="idEleccionesLocalidad" required onChange="this.form.submit()">
                        <option value="" selected>Seleccione Localidad</option>
                        <?php
                        $eleccionesLocalidadesLogic = new eleccionesLocalidades();
                        $resLocalidades = $eleccionesLocalidadesLogic->obtenerLocalidadesPorIdElecciones($idElecciones);   
                        if ($resLocalidades['estado']){
                            foreach ($resLocalidades['datos'] as $dato) {
                                $detalleLocalidad = $dato['localidadDetalle'];
                                if (strtoupper($dato['localidadDetalle']) <> strtoupper($dato['detalle'])) {
                                    $detalleLocalidad .= ' - '.$dato['detalle'];
                                }
                            ?>
                                <option value="<?php echo $dato['idEleccionesLocalidad']; ?>" <?php if(isset($idEleccionesLocalidad) && $dato['idEleccionesLocalidad'] == $idEleccionesLocalidad) { echo 'selected'; } ?>><?php echo $detalleLocalidad; ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
            </form>    
        </div>
        <div class="col-xs-3"></div>
        <div class="col-xs-3">
            <?php
            if (isset($idEleccionesLocalidad) && $idEleccionesLocalidad <>'') {
            ?>
                <form method="POST" action="elecciones_listas_form.php">
                    <div align="right">
                        <button type="submit" class="btn btn-success btn-lg">Nueva Lista</button>
                        <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                        <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                        <input type="hidden" id="accion" name="accion" value="1">
                    </div>
                </form>
            <?php
            } else {
                echo '';
            }
            ?>
        </div>
    </div>        
    <div class="row">&nbsp;</div>
    <?php
    $resEleccionesListas = $eleccionesLocalidadesListasLogic->obtenerListasPorIdEleccionesLocalidad($idEleccionesLocalidad);   
    if ($resEleccionesListas['estado']){
    ?>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre de la lista</th>
                        <th>Tipo de lista</th>
                        <th style="width: 100px">Integrantes</th>
                        <th style="width: 30px">Editar</th>
                    </tr>
                </thead>
            <tbody>
                <?php
                foreach ($resEleccionesListas['datos'] as $dato) {
                    $idEleccionesLocalidadLista = $dato['idEleccionesLocalidadLista'];
                    $nombre = $dato['nombre'];
                    switch ($dato['tipoLista']) {
                        case 'B':
                            $tipoLista = 'Voto en Blanco';
                            break;

                        case 'A':
                            $tipoLista = 'Voto Anulado';
                            break;

                        case 'C':
                            $tipoLista = 'Consejeros';
                            break;

                        case 'T':
                            $tipoLista = 'Tribunal Disciplinario';
                            break;

                        default:
                            $tipoLista = '';
                            break;
                      } 
                      $cantIntegrantes = $dato['cantIntegrantes'];
                  ?>
                    <tr>
                	<td><?php echo $idEleccionesLocalidadLista;?></td>
			<td><?php echo $nombre;?></td>
			<td><?php echo $tipoLista;?></td>
                        <td>
                            <form method="POST" action="elecciones_integrantes_lista.php">
                                <?php
                                if ($dato['tipoLista'] == 'C' || $dato['tipoLista'] == 'T') {
                                    $resTitulares = $eleccionesLocalidadesIntegrantesLogic->obtenerCantidadIntegrantesPorCargo($idEleccionesLocalidadLista, 'T');
                                    $resSuplentes = $eleccionesLocalidadesIntegrantesLogic->obtenerCantidadIntegrantesPorCargo($idEleccionesLocalidadLista, 'S');

                                    $cantTitulares = NULL;
                                    if ($resTitulares['estado']) {
                                        $cantTitulares = $resTitulares['cantidad'];
                                    }

                                    $cantSuplentes = NULL;
                                    if ($resSuplentes['estado']) {
                                        $cantSuplentes = $resSuplentes['cantidad']; 
                                    }

                                    
                                ?>
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-book">
                                        <?php echo 'Titulares: '.$cantTitulares.' Suplentes: '.$cantSuplentes; ?>
                                    </button>
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                                    <input type="hidden" id="idEleccionesLocalidadLista" name="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>">
                                <?php
                                } else {
                                    echo "";
                                }
                                ?>
                            </form>
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="elecciones_listas_form.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block"></button>
                                    <input type="hidden" id="accion" name="accion" value="3">
                                    <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
                                    <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                                    <input type="hidden" id="idEleccionesLocalidadLista" name="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>">
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
        <div class="<?php echo $resEleccionesListas['clase']; ?>" role="alert">
            <span class="<?php echo $resEleccionesListas['icono']; ?>" ></span>
            <span><strong><?php echo $resEleccionesListas['mensaje']; ?></strong></span>
        </div>
    <?php
    }    
    ?>
    </div>
    </div>
<?php
} else {
    echo "No hay elecciones activas";
}
?>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';