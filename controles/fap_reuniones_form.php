<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');

$continua = TRUE;
$mensaje = "";
$titulo = "";
$botonConfirma = "Confirma ";
$fapLogic = new fapLogic();
if (isset($_GET['agregar'])) {
    $accion = 'agregar';
    $titulo = 'ALTA DE REUNION DE CONSEJO';
    $idSapReunion = NULL;
} else {
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idSapReunion = $_GET['id'];
        $resReunion = $fapLogic->obtenerSapReunionPorId($idSapReunion);
        if ($resReunion['estado']) {
            $fapReunion = $resReunion['datos'];
            $fechaReunion = $fapReunion['fechaReunion'];
            $estadoReunion = $fapReunion['estadoReunion'];
            $observaciones = $fapReunion['observaciones'];
            $resolucion = $fapReunion['resolucion'];
            if (isset($_GET['editar'])) {
                $accion = 'editar';
                $titulo = 'EDITAR CARÁTULA DE ';
                $botonConfirma .= 'cambios';
            } else {
                $accion = 'consulta';
                $titulo = 'CONSULTA CARÁTULA DE ';
                $botonConfirma = 'Volver';
            }
        } else {
            $continua = FALSE;
            $mensaje .= $resReunion['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idSapReunion - ';
    }
}
if ($continua) {
    if (isset($_POST['mensaje'])) {
        //vino por error en la carga
        ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
        <?php
        if (isset($_POST['fechaReunion'])) {
            $fechaReunion = $_POST['fechaReunion'];
        } else {
            $fechaReunion = NULL;
        }
        if (isset($_POST['observaciones'])) {
            $observaciones = $_POST['observaciones'];
        } else {
            $observaciones = NULL;
        }
        if (isset($_POST['resolucion'])) {
            $resolucion = $_POST['resolucion'];
        } else {
            $resolucion = NULL;
        }
        if (isset($_POST['estadoReunion'])) {
            $estadoReunion = $_POST['estadoReunion'];
        } else {
            $estadoReunion = NULL;
        }
    } else {
        if (!isset($idSapReunion)) {
            //si entra por alta inicializa todos los campos en null
            $fechaReunion = NULL;
            $observaciones = NULL;
            $resolucion = NULL;
            $estadoReunion = 'A';
        }
    }
    if ($continua) {
    ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo $titulo; ?></h4>
                    </div>
                    <div class="col-md-2">
                        <a href="fap_reuniones.php" class="btn btn-info">Volver al listado</a>
                    </div>
                    <div class="col-md-2">
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <form id="formReunion" name="formReunion" method="POST" onSubmit="" action="datosFap/abm_reuniones.php?<?php echo $accion; if ($accion == 'editar') { echo '&id='.$idSapReunion; } ?>">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="fechaReunion">Fecha de la reunión *: </label>
                            <input class="form-control" type="date" name="fechaReunion" id="fechaReunion" value="<?php echo $fechaReunion; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="control-label">Estado: *</label>
                            <br>
                            <label class="radio-inline"><input type="radio" name="estadoReunion" value="A" <?php if ($estadoReunion == 'A') { ?> checked="" <?php } ?>>Abierta</label>
                            <label class="radio-inline"><input type="radio" name="estadoReunion" value="C" <?php if ($estadoReunion == 'C') { ?> checked="" <?php } ?>>Cerrada</label>
                        </div>
                        <div class="col-md-8">
                            <label for="resolucion">Resolución: </label>
                            <input class="form-control" type="text" name="resolucion" id="resolucion" value="<?php echo $resolucion; ?>">
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="observaciones">Observaciones: </label>
                            <textarea class="form-control" name="observaciones" id="observaciones" rows="4" ><?php echo $observaciones; ?></textarea>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-success"><?php echo $botonConfirma; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 alert alert-danger" role="alert">
                <span><strong><?php echo $mensaje; ?></strong></span>
            </div>
        </div>
    <?php
    }
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12 alert alert-danger" role="alert">
            <span><strong><?php echo $mensaje; ?></strong></span>
        </div>
    </div>
<?php    
}
require_once '../html/footer.php';
