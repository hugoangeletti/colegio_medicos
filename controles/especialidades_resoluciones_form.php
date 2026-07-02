<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/tipoResolucionLogic.php');
$tipoResolucionLogic = new tipoResolucionLogic();
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
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
                        "mColumns" : [0, 1, 2, 3, 4, 5, 6],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                        "sTitle": "Listado de expedientes",
                        "sPdfOrientation": "portrait",
                        "sFileName": "listado_de_expedientes.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                    }

            ]
            }
        });
    });              
</script>
<?php
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];

    if (isset($_GET['idResolucion'])){
        $idResolucion = $_GET['idResolucion'];
    } else {
        $idResolucion = NULL;
    }
    $continua = TRUE;

    if ($accion == 3){
        $resResolucion = $resolucionesLogic->obtenerResolucionPorId($idResolucion);
        if ($resResolucion['estado']){
            $resolucion = $resResolucion['datos'];
            $fecha = $resolucion['fecha'];
            $detalle = $resolucion['detalle'];
            $estado = $resolucion['estado'];
            $numero = $resolucion['numero'];
            $idTipoResolucion = $resolucion['idTipoResolucion'];
            $estadoResoluciones = $resolucion['estado'];
            $anioResoluciones = date("Y",strtotime($resolucion['fecha']));
        } else {
            $continua = FALSE;
        }
        $titulo="Editar Resolución";
        $nombreBoton="Guardar cambios";
    } else {
        $titulo="Nueva Resolución";
        $nombreBoton="Guardar";
        $detalle = "Resolución de Consejo Directivo";
        $fecha = "";
        $estado = "A";
        $numero = "";
        $idTipoResolucion = null;
    }        
} else {
    $continua = FALSE;
}
if ($continua){
?>
    <?php
    if (isset($_POST['mensaje']))
    {
     ?>
        <div id="divMensaje"> 
            <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
     <?php    
        $idResolucion = $_POST['idResolucion'];
        $detalle = $_POST['detalle'];
        $estado = $_POST['estado'];
        $numero = $_POST['numero'];
        $idTipoResolucion = $_POST['idTipoResolucion'];
        $fecha = $_POST['fecha'];
    } 
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
            <div class="panel-body">
                <?php 
                if (($accion == 1 && (!isset($_POST['tipoResolucion']) || $_POST['tipoResolucion'] == ""))) {
                ?>
                    <form id="formElecciones" name="formResolucion" method="POST" onSubmit="" action="especialidades_resoluciones_form.php?accion=1">
                        <div class="row">
                            <div class="col-md-6">
                                <b>Tipo de Resolución *</b>  
                                <select class="form-control" id="tipoResolucion" name="tipoResolucion" required="" onChange="this.form.submit()">
                                    <option value="">Seleccione el Tipo de Resolución</option>
                                    <?php
                                    $resTipoResoluciones = $tipoResolucionLogic->obtenerTiposResoluciones();
                                    if ($resTipoResoluciones['estado']) {
                                        foreach ($resTipoResoluciones['datos'] as $row) {
                                        ?>
                                            <option value="<?php echo $row['id'] ?>" <?php if($idTipoResolucion == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                                        <?php
                                        }
                                    } else {
                                        echo $resTipoResoluciones['mensaje'];
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </form>   
                <?php 
                } else { 
                    if ($accion == 1 && isset($_POST['tipoResolucion'])) {
                        $idTipoResolucion = $_POST['tipoResolucion'];
                    }
                    $resTipoResolucione = $tipoResolucionLogic->obtenerTipoResolucionPorId($idTipoResolucion);
                    if ($resTipoResolucione['estado']) {
                        $tipoResolucionDatos = $resTipoResolucione['datos'];
                        $tipoResolucion = $tipoResolucionDatos['tipoEspecialista'];
                        $nombreTipoResolucion = $tipoResolucionDatos['nombre'];
                    } else {
                        $nombreTipoResolucion = "NO ENCONTRADO";
                    }
                ?>
                    <form id="formElecciones" name="formResolucion" method="POST" onSubmit="" action="datosResoluciones\abm_resolucion.php">
                        <div class="row">
                            <div class="col-md-3">
                                <b>Resolución de </b>  
                                <input class="form-control" type="text" name="tipoResolucion" id="tipoResolucion" value="<?php echo $nombreTipoResolucion; ?>" required="" disabled=""/>
                            </div>
                            <div class="col-md-3">
                                <b>Número de la resolución *</b>  
                                <input class="form-control" type="text" name="numero" id="numero" placeholder="Número de Resolución " value="<?php echo $numero; ?>" required=""/>
                            </div>
                            <div class="col-md-3">
                                <b>Fecha de la resolución *</b>  
                                <input class="form-control" type="date" name="fecha" value="<?php echo $fecha; ?>" required=""/>
                            </div>
                            <div class="col-md-3">
                                <b>Detalle *</b>  
                                <input class="form-control" type="text" name="detalle" id="detalle" value="<?php echo $detalle; ?>" required=""/>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                 <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                             </div>
                        </div>  
                        <?php
                        if ($idResolucion) {
                        ?>
                            <input type="hidden" name="idResolucion" id="idResolucion" value="<?php echo $idResolucion; ?>" />
                        <?php
                        }
                        ?>
                        <input type="hidden" id="tipoResolucion" name="tipoResolucion" value="<?php echo $tipoResolucion; ?>">
                        <input type="hidden" id="idTipoResolucion" name="idTipoResolucion" value="<?php echo $idTipoResolucion; ?>">
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    </form>   
                <?php
                }
                ?>
            </div>
        </div>
    </div>
<?php
            } else {
    
}
?>
    <!-- BOTON VOLVER -->    
    <div class="col-md-12" style="text-align:right;">
        <form  method="POST" action="especialidades_resoluciones.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
       </form>
    </div>  
    </div>
<?php
require_once '../html/footer.php';
