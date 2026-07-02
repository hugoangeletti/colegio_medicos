<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
} else {
    $accion = 1;
}
if (isset($_POST['estadoResoluciones']) && $_POST['estadoResoluciones'] != ""){
    $estadoResoluciones = $_POST['estadoResoluciones'];
} else {
    $estadoResoluciones = 'A';
}
if (isset($_POST['anioResoluciones']) && $_POST['anioResoluciones'] != ""){
    $anioResoluciones = $_POST['anioResoluciones'];
} else {
    $anioResoluciones = date('Y');
}

$continua = TRUE;
if ($accion <> 1) {
    if (isset($_POST['idAnexo']) && $_POST['idAnexo']) {
        $idAnexo = $_POST['idAnexo'];
        $resAnexo = $resolucionesLogic->obtenerResolucionAnexoPorId($idAnexo);
        if ($resAnexo['estado']) {
            $anexo = $resAnexo['datos'];
        } else {
            $resResolucion['clase'] = $resAnexo['clase'];
            $resResolucion['icono'] = $resAnexo['icono'];
            $resResolucion['mensaje'] = $resAnexo['mensaje'];
            $continua = FALSE;
        }
    } else {
        $resResolucion['clase'] = "alert alert-warning";
        $resResolucion['icono'] = "glyphicon glyphicon-exclamation-sign";
        $resResolucion['mensaje'] = "Datos mal ingresados";
        $continua = FALSE;
    }
} else {
    $idAnexo = NULL;
}

if (isset($_POST['idResolucion']) && $_POST['idResolucion']) {
    $idResolucion = $_POST['idResolucion'];
    $resResolucion = $resolucionesLogic->obtenerResolucionPorId($idResolucion);
    if ($resResolucion['estado']) {
        $resolucion = $resResolucion['datos'];
    } else {
        $continua = FALSE;
    }
} else {
    $resResolucion['clase'] = "alert alert-warning";
    $resResolucion['icono'] = "glyphicon glyphicon-exclamation-sign";
    $resResolucion['mensaje'] = "Datos mal ingresados";
    $continua = FALSE;
}

switch ($accion) {
    case 1:
        $titulo = 'Nuevo ANEXO';
        $panel = 'panel-info';
        $textoBoton = 'Confirmar';
        $claseBoton = 'btn-info';
        $readOnly = '';
        break;

    case 2:
        $titulo = 'Eliminar ANEXO';
        $panel = 'panel-danger';
        $textoBoton = 'Eliminar';
        $claseBoton = 'btn-danger';
        $readOnly = 'readonly=""';
        break;

    case 3:
        $titulo = 'Editar ANEXO';
        $panel = 'panel-info';
        $claseBoton = 'btn-info';
        $textoBoton = 'Confimar';
        $readOnly = '';
        break;

    default:
        $titulo = 'ANEXO - error de acceso';
        $panel = 'panel-default';
        $claseBoton = 'btn-default';
        $textoBoton = 'default';
        $readOnly = 'readonly=""';
        break;
}

?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>ANEXO de la Resolución:  <?php echo $resolucion['numero'].' con fecha '.cambiarFechaFormatoParaMostrar($resolucion['fecha']); ?></h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="especialidades_resoluciones_anexos.php?idResolucion=<?php echo $idResolucion; ?>&estado=<?php echo $estadoResoluciones; ?>&accion=1&anio=<?php echo $anioResoluciones ?>">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver al listado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['mensaje'])) {
        ?>
           <div class="ocultarMensaje"> 
               <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
           </div>
         <?php
            $observacion = $_POST['observacion'];
            $borrado = $_POST['borrado'];
        } else {
            if ($accion != 1) {
                $observacion = $anexo['observacion'];
                $borrado = $anexo['borrado'];
            } else {
                $observacion = NULL;
                $borrado = NULL;
            }
        }
        if ($continua) {
        ?>
            <div class="row">
                <div class="col-md-12 text-center"><h4><b><?php echo $titulo; ?></b></h4></div>
            </div>
            <form id="datosColegiacion" autocomplete="off" name="datosColegiacion" method="POST" action="datosResoluciones/abm_anexo.php">
                <div class="row">
                    <div class="col-md-12">
                        <label>Observaciones </label>
                        <textarea class="form-control" autofocus="" required="" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" name="observacion" id="observacion" rows="5" <?php echo $readOnly; ?> ><?php echo $observacion; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        <label>Estado *</label>
                        <select class="form-control" id="borrado" name="borrado" required="">
                            <option value="0" <?php if($borrado == 0) { ?> selected <?php } ?>>Activo</option>
                            <option value="1" <?php if($borrado == 1) { ?> selected <?php } ?>>Borrado</option>
                        </select>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" ><?php echo $textoBoton; ?> </button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        <input type="hidden" name="idResolucion" id="idResolucion" value="<?php echo $idResolucion; ?>" />
                        <input type="hidden" name="idAnexo" id="idAnexo" value="<?php echo $idAnexo; ?>" />
                        <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="<?php echo $estadoResoluciones; ?>">
                        <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
                    </div>
                </div>    
            </form>
        <?php
        } else {
        ?>
            <div class="col-md-12">
                <div class="<?php echo $resResolucion['clase']; ?>" role="alert">
                    <span class="<?php echo $resResolucion['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resResolucion['mensaje']; ?></strong></span>
                </div>        
            </div>
        <?php 
        }
        ?>
    </div>    
</div>
<?php
require_once '../html/footer.php';
