<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/calendario_eventos_Logic.php');

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
    $idCursoEntidad = $_GET['id'];
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
        $idCursoEntidad = NULL;

        $titulo = NULL;
        $director = NULL;
        $fechaInicio = date('Y-m-d');
        $vigenciaHasta = NULL;
        $observacion = NULL;
        if (isset($_POST['mensaje'])) {
            $titulo = $_POST['titulo'];
            $director = $_POST['director'];
            $fechaInicio = $_POST['fechaInicio'];
            $vigenciaHasta = $_POST['vigenciaHasta'];
            $observacion = $_POST['observacion'];
        }
    }
}

if (isset($accion)) {
    if (isset($idCursoEntidad) && $idCursoEntidad <> "") {
        $calendarioLogic = new calendario_eventosLogic();
        $resEvento = $calendarioLogic->obtenerCursoEntidadPorId($idCursoEntidad);
        if ($resEvento['estado']) {
            $evento = $resEvento['datos'];
            $titulo = $evento['titulo'];
            $director = $evento['director'];
            $fechaInicio = $evento['fechaInicio'];
            $estadoCurso = $evento['estado'];
            $vigenciaHasta = $evento['vigenciaHasta'];
            $observacion = $evento['observacion'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resEvento['mensaje'];
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
                    <h5><?php echo $accion; ?> EVENTO / CURSO.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="calendario_eventos.php" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCurso" name="datosCurso" method="POST" action="datosCalendarioEventos/abm_curso_entidad.php">
                <div class="row">
                    <div class="col-md-6">
                        <label for="titulo">Título: *</label>
                        <input class="form-control" type="text" name="titulo" id="titulo" value="<?php echo $titulo; ?>" placeholder="Ingrese el título del curso" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-6">
                        <label for="director">Director: </label>
                        <input class="form-control" type="text" name="director" id="director" value="<?php echo $director; ?>" placeholder="Ingrese el director del curso" <?php echo $readOnly; ?> />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="fechaInicio">Fecha de Inicio: *</label>
                        <input class="form-control" type="date" name="fechaInicio" id="fechaInicio" value="<?php echo $fechaInicio; ?>" min="<?php echo $fechaInicio; ?>" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="vigenciaHasta">Vigencia Hasta: </label>
                        <input class="form-control" type="date" name="vigenciaHasta" id="vigenciaHasta" value="<?php echo $vigenciaHasta; ?>" min="<?php echo date('Y-m-d'); ?>" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="observacion">Observaciones: </label>
                        <textarea class="form-control" type="text" name="observacion" id="observacion" rows="5" <?php echo $readOnly; ?>><?php echo $observacion; ?></textarea>
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
                            <input type="hidden" name="idCursoEntidad" id="idCursoEntidad" value="<?php echo $idCursoEntidad; ?>">
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
            <a href="calendario_eventos.php" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
