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
$tituloCursoEntidad = "";
if (isset($_GET['idCursoAula']) && $_GET['idCursoAula'] <> "") {
    $idCursoAula = $_GET['idCursoAula'];
    $calendarioLogic = new calendario_eventosLogic();
    $resCursoAula = $calendarioLogic->obtenerCursoAulaPorId($idCursoAula);
    if ($resCursoAula['estado']) {
        $cursoAula = $resCursoAula['datos'];
        $tituloCursoEntidad = $cursoAula['titulo'];
        $nombreAula = $cursoAula['nombreAula'];
        $nombreDia = $cursoAula['nombreDia'];
        $tituloCursoEntidad = '<b>'.$tituloCursoEntidad.'</b> en <b>'.$nombreAula.'</b> del día <b>'.$nombreDia.'</b>';
    } else {
        $continua = FALSE;
        $mensaje .= $resCursoAula['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idCursoEntidad - ';
}

if (isset($_GET['periodo']) && $_GET['periodo'] <> "") {
    $periodo = $_GET['periodo'];
} else {
    $periodo = date('Y');
}

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCursoAulaTurno = $_GET['id'];
    if (isset($_GET['editar'])) {
        $accion = "EDITAR";
        $requerido = "required";
    } else {
        $accion = "CONSULTAR";
        $readOnly = "readonly";
    }
}

if (isset($accion)) {
    if (isset($idCursoAulaTurno) && $idCursoAulaTurno <> "") {
        $resCursoAulaTurno = $calendarioLogic->obtenerCursoAulaTurnoPorId($idCursoAulaTurno);
        if ($resCursoAulaTurno['estado']) {
            $cursoAulaTurno = $resCursoAulaTurno['datos'];
            $fecha = $cursoAulaTurno['fecha'];
            $horaInicio = $cursoAula['horaInicio'];
            $horaFin = $cursoAula['horaFin'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resCursoAulaTurno['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idCursoAulaTurno -";
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
                    <h5><?php echo $accion; ?> TURNOS A <b><?php echo $tituloCursoEntidad; ?></b>.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="calendario_eventos_ver_turnos.php?id=<?php echo $idCursoAula; ?>" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCurso" name="datosCurso" method="POST" action="datosCalendarioEventos/abm_curso_aula_turno.php">
                <div class="row">
                    <div class="col-md-2">
                        <label for="fecha">Fecha: </label>
                        <input class="form-control" type="date" name="fecha" id="fecha" value="<?php echo $fecha; ?>" readonly/>
                    </div>
                    <div class="col-md-2">
                        <label for="horaInicio">Hora Inicio: *</label>
                        <input class="form-control" type="text" name="horaInicio" id="horaInicio" value="<?php echo number_format($horaInicio, 2); ?>" placeholder="HH.MM" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="horaFin">Hora Fin: *</label>
                        <input class="form-control" type="text" name="horaFin" id="horaFin" value="<?php echo number_format($horaFin, 2); ?>" placeholder="HH.MM" <?php echo $readOnly; ?>/>
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
                            <input type="hidden" name="idCursoAula" id="idCursoAula" value="<?php echo $idCursoAula; ?>">
                            <input type="hidden" name="idCursoAulaTurno" id="idCursoAulaTurno" value="<?php echo $idCursoAulaTurno; ?>">
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
