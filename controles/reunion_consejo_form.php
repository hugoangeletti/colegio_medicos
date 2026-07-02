<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reunionConsejoLogic.php');

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
    $idReunionConsejo = $_GET['id'];
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
        $idReunionConsejo = NULL;
        $numeroActa = NULL;
        $tipoReunion = 'O';
        $fecha = date('Y-m-d');
        $observacion = NULL;
        if (isset($_POST['mensaje'])) {
            $numeroActa = $_POST['numeroActa'];
            $tipoReunion = $_POST['tipoReunion'];
            $fecha = $_POST['fecha'];
            $observacion = $_POST['observacion'];
        }
    }
}

if (isset($accion)) {
    if (isset($idReunionConsejo) && $idReunionConsejo <> "") {
        $reunionConsejoLogic = new reunionConsejoLogic();
        $resReunion = $reunionConsejoLogic->obtenerReunionConsejoPorId($idReunionConsejo);
        if ($resReunion['estado']) {
            $reunion = $resReunion['datos'];
            $numeroActa = $reunion['numeroActa'];
            $tipoReunion = $reunion['tipoReunion'];
            $fecha = $reunion['fecha'];
            $observacion = $reunion['observacion'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resReunion['mensaje'];
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
                    <h5><?php echo $accion; ?> REUNIÓN DE CONSEJO.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="reunion_consejo_lista.php" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosReunionConsejo" name="datosReunionConsejo" method="POST" action="datosReunionConsejo\abm_reunion_consejo.php">
                <div class="row">
                    <div class="col-md-2">
                        <label for="fecha">Fecha de la reunión: *</label>
                        <input class="form-control" type="date" name="fecha" id="fecha" value="<?php echo $fecha; ?>" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="tipoReunion">Tipo de reunión: *</label>
                        <select class="form-control" id="tipoReunion" name="tipoReunion"  <?php echo $readOnly.$requerido; ?>>
                            <option value="O" <?php if($tipoReunion == "O") { echo 'selected'; } ?>>ORDINARIA</option>
                            <option value="E" <?php if($tipoReunion == "E") { echo 'selected'; } ?>>EXTRAORDINARIA</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="numeroActa">Número de Acta: *</label>
                        <input class="form-control" type="text" name="numeroActa" id="numeroActa" value="<?php echo $numeroActa; ?>" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-6">
                        <label for="observacion">Observación: </label>
                        <input class="form-control" type="text" name="observacion" id="observacion" value="<?php echo $observacion; ?>" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <?php 
                if ($accion <> "CONSULTAR") {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <br>
                            <button type="submit" name='confirma' id='confirma' class="btn btn-primary">Guardar</button>
                            <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                            <?php 
                            if ($accion <> 'AGREGAR') {
                            ?>
                                <input type="hidden" name="idReunionConsejo" id="idReunionConsejo" value="<?php echo $idReunionConsejo; ?>">
                            <?php 
                            } 
                            ?>
                        </div>
                    </div>
                <?php 
                } 
                ?>
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
            <a href="reunion_consejo_lista.php" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
