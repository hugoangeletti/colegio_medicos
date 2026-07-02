<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reconocimientoAntiguedadLogic.php');

$continua = TRUE;
$mensaje = "";
$accion = NULL;
$readOnly = NULL;
$requerido = NULL;

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idReconocimientoAntiguedad = $_GET['id'];
    if (isset($_GET['editar'])) {
        $accion = "EDITAR";
        $requerido = "required";
    } else {
        $accion = "CONSULTAR";
        $readOnly = "readonly";
    }
} else {
    if (isset($_GET['agregar'])) {
        $accion = "AGREGAR";
        $requerido = "required";
        $idReconocimientoAntiguedad = NULL;
        $anioActo = date('Y');
        $lugarActo = "Sede";
        $fechaActo = $anioActo.'-12-03';
        $antiguedad = NULL;
    }
}
if (isset($_POST['mensaje'])) {
    $anioActo = $_POST['anioActo'];
    $lugarActo = $_POST['lugarActo'];
    $fechaActo = $_POST['fechaActo'];
    $antiguedad = $_POST['antiguedad'];
}

if (isset($accion)) {
    if (isset($idReconocimientoAntiguedad) && $idReconocimientoAntiguedad <> "") {
        $actosLogic = new reconocimientoAntiguedadLogic();
        $resActos = $actosLogic->obtenerActoPorId($idReconocimientoAntiguedad);
        if ($resActos['estado']) {
            $acto = $resActos['datos'];
            $anioActo = $acto['anioActo'];
            $lugarActo = $acto['lugarActo'];
            $fechaActo = $acto['fechaActo'];
            $antiguedad = $acto['antiguedad'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resActos['mensaje'];
        }
    }
} else {
    $continua = FALSE;
    $mensaje .= "Acceso incorrecto";
}        
if ($continua) {
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h5><?php echo $accion; ?> ACTO.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="reconocimiento_antiguedad.php" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosActo" name="datosCurso" method="POST" autocomplete="OFF" action="datosActo\abm_acto.php">
                <div class="row">
                    <div class="col-md-2">
                        <label for="anioActo">Año acto: </label>
                        <input class="form-control" type="text" name="anioActo" id="anioActo" value="<?php echo $anioActo; ?>" readonly />
                    </div>
                    <?php 
                    if ($accion == 'EDITAR') {
                    ?>
                        <div class="col-md-2">
                            <label for="antiguedad">Antigüedad: *</label>
                            <input class="form-control" type="text" name="antiguedadLeyenda" id="antiguedadLeyenda" value="<?php echo $antiguedad.' Años'; ?>" readonly />
                            <input class="form-control" type="hidden" name="antiguedad" id="antiguedad" value="<?php echo $antiguedad.' Años'; ?>" readonly />
                        </div>
                    <?php 
                    }
                    ?>
                    <div class="col-md-2">
                        <label for="fechaActo">Fecha del acto: *</label>
                        <input class="form-control" type="date" name="fechaActo" id="fechaActo" value="<?php echo $fechaActo; ?>" required />
                    </div>
                    <div class="col-md-6">
                        <label for="lugarActo">Lugar del Acto: *</label>
                        <input class="form-control" type="text" name="lugarActo" id="lugarActo" value="<?php echo $lugarActo; ?>" required />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <br>
                        <button type="submit" name='confirma' id='confirma' class="btn btn-primary">Guardar</button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                        <?php 
                        if (isset($idReconocimientoAntiguedad) && $idReconocimientoAntiguedad <> "") {
                        ?>
                            <input type="hidden" name="idReconocimientoAntiguedad" id="idReconocimientoAntiguedad" value="<?php echo $idReconocimientoAntiguedad; ?>">
                        <?php 
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <a href="reconocimiento_antiguedad.php" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
