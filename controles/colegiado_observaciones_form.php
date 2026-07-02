<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoObservacionLogic.php');
$colegiadoObservacionLogic = new colegiadoObservacionLogic();

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
} else {
    $accion = 1;
}

$continua = TRUE;
if ($accion <> 1) {
    if (isset($_GET['id']) || isset($_POST['idColegiadoObservacion'])) {
        if (isset($_GET['id'])) {
            $idColegiadoObservacion = $_GET['id'];
        } else {
            $idColegiadoObservacion = $_POST['idColegiadoObservacion'];
        }
    } else {
        $resObservaciones['clase'] = "alert alert-warning";
        $resObservaciones['icono'] = "glyphicon glyphicon-exclamation-sign";
        $resObservaciones['mensaje'] = "Datos mal ingresados";
        $continua = FALSE;
    }
} else {
    $idColegiadoObservacion = NULL;
}

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $idColegiado = NULL;
}

switch ($accion) {
    case 1:
        $titulo = 'Nueva Observación';
        $panel = 'panel-success';
        $textoBoton = 'Confirmar';
        $claseBoton = 'btn-success';
        $readOnly = '';
        break;

    case 2:
        $titulo = 'Eliminar Observación';
        $panel = 'panel-danger';
        $textoBoton = 'Eliminar';
        $claseBoton = 'btn-danger';
        $readOnly = 'readonly=""';
        break;

    case 3:
        $titulo = 'Editar Observación';
        $panel = 'panel-info';
        $claseBoton = 'btn-info';
        $textoBoton = 'Confimar';
        $readOnly = '';
        break;

    default:
        $titulo = 'Observación - error de acceso';
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
                <h4>Observación</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_observaciones.php?idColegiado=<?php echo $idColegiado; ?>">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver a Observaciones</button>
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
        $idColegiadoObservacion = $_POST['idColegiadoObservacion'];
        $idTipoObservacion = $_POST['idTipoObservacion'];
        $observaciones = $_POST['observaciones'];
        $estado = $_POST['estado'];
    } else {
        if ($accion != 1) {
            $resObservaciones = $colegiadoObservacionLogic->obtenerColegiadoObservacionPorId($idColegiadoObservacion);
            if ($resObservaciones['estado']) {
                $observacion = $resObservaciones['datos'];
                $idTipoObservacion = $observacion['idTipoObservacion'];
                $tipoObservacion = $observacion['tipoObservacion'];
                $observaciones = $observacion['observaciones'];
                $estado = $observacion['estado'];
            } else {
                $continua = FALSE;
            }
        } else {
            $idTipoObservacion = NULL;
            $observaciones = NULL;
            $estado = 'A';
        }
    }
        if ($continua) {
        ?>
            <div class="row">
                <div class="col-md-12 text-center"><h4><b><?php echo $titulo; ?></b></h4></div>
            </div>
            <form id="datosObservaciones" autocomplete="off" name="datosObservaciones" method="POST" action="datosObservaciones/abm_observaciones.php">
                <div class="row">
                    <div class="col-md-9">
                        <label>Tipo *</label>
                        <select class="form-control" id="idTipoObservacion" name="idTipoObservacion" required="">
                            <?php
                            $resTipo = $colegiadoObservacionLogic->obtenerTiposObservacion();
                            if ($resTipo['estado']) {
                                foreach ($resTipo['datos'] as $row) {
                                ?>
                                    <option value="<?php echo $row['id'] ?>" <?php if($idTipoObservacion == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                                <?php
                                }
                            } else {
                                echo $resTipo['mensaje'];
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Estado *</label>
                        <select class="form-control" id="estado" name="estado" required="">
                            <option value="A" <?php if($estado == 'A') { ?> selected <?php } ?>>Activo</option>
                            <option value="B" <?php if($estado == 'B') { ?> selected <?php } ?>>ANULADO</option>
                        </select>
                    </div>
                </div>
                <div class="row">&nbsp;</div>

                <div class="row">
                    <div class="col-md-12">
                        <label>Observaciones </label>
                        <textarea class="form-control" autofocus="" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" name="observaciones" id="observaciones" rows="5" <?php echo $readOnly; ?> ><?php echo $observaciones; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" ><?php echo $textoBoton; ?> </button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        <input type="hidden" name="idColegiadoObservacion" id="idColegiadoObservacion" value="<?php echo $idColegiadoObservacion; ?>" />
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                </div>    
            </form>
        <?php
            
        } else {
        ?>
            <div class="col-md-12">
                <div class="<?php echo $resObservaciones['clase']; ?>" role="alert">
                    <span class="<?php echo $resObservaciones['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resObservaciones['mensaje']; ?></strong></span>
                </div>        
            </div>
        <?php 
        }
        ?>
    </div>    
</div>
<?php
require_once '../html/footer.php';
