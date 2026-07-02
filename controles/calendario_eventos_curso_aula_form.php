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

if (isset($_GET['idCursoEntidad']) && $_GET['idCursoEntidad'] <> "") {
    $idCursoEntidad = $_GET['idCursoEntidad'];
    $calendarioLogic = new calendario_eventosLogic();
    $resCursoEntidad = $calendarioLogic->obtenerCursoEntidadPorId($idCursoEntidad);
    if ($resCursoEntidad['estado']) {
        $cursoEntidad = $resCursoEntidad['datos'];
        $tituloCursoEntidad = $cursoEntidad['titulo'];
        $estadoCurso = $cursoEntidad['estado'];
    } else {
        $continua = FALSE;
        $mensaje .= $resCursoEntidad['mensaje'];
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
    $idCursoAula = $_GET['id'];
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
        $idCursoAula = NULL;

        $idAula = NULL;
        $idDia = NULL;
        $fechaInicio = NULL;
        $fechaFin = NULL;
        $horaInicio = NULL;
        $horaFin = NULL;
        $autorizado = NULL;
        $periodicidad = 'SEMANAL';
        if (isset($_POST['mensaje'])) {
            $idAula = $_POST['idAula'];
            $idDia = $_POST['idDia'];
            $fechaInicio = $_POST['fechaInicio'];
            $fechaFin = $_POST['fechaFin'];
            $horaInicio = $_POST['horaInicio'];
            $horaFin = $_POST['horaFin'];
            $autorizado = $_POST['autorizado'];
            $periodicidad = $_POST['periodicidad'];
        }
    }
}

if (isset($accion)) {
    if (isset($idCursoAula) && $idCursoAula <> "") {
        $calendarioLogic = new calendario_eventosLogic();
        $resCursoAula = $calendarioLogic->obtenerCursoAulaPorId($idCursoAula);
        if ($resCursoAula['estado']) {
            $cursoAula = $resCursoAula['datos'];
            $idAula = $cursoAula['idAula'];
            $idDia = $cursoAula['idDia'];
            $fechaInicio = $cursoAula['fechaInicio'];
            $fechaFin = $cursoAula['fechaFin'];
            $horaInicio = $cursoAula['horaInicio'];
            $horaFin = $cursoAula['horaFin'];
            $autorizado = $cursoAula['autorizado'];
            $periodicidad = $cursoAula['periodicidad'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resCursoAula['mensaje'];
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
                    <h5><?php echo $accion; ?> TURNOS A <b><?php echo $tituloCursoEntidad; ?></b>.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="calendario_eventos_administrar_turnos.php?id=<?php echo $idCursoEntidad; ?>" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCurso" name="datosCurso" method="POST" action="datosCalendarioEventos/abm_curso_aula.php">
                <div class="row">
                    <div class="col-md-4">
                        <label for="idAula">Aula: *</label>
                        <?php 
                        $resAula = $calendarioLogic->obtenerAulasPorEstado('A');
                        if ($resAula['estado']) {
                        ?>
                            <select name="idAula" id="idAula" class="form-control" required>
                                <option value="">Seleccione un Aula</option>
                                <?php 
                                foreach ($resAula['datos'] as $dato) {
                                    $id = $dato['idAula'];
                                    $nombreAula = $dato['nombreAula'];
                                    ?>
                                    <option value="<?php echo $id; ?>" <?php if ($idAula == $id ) { echo 'selected'; } ?>><?php echo $nombreAula; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        <?php 
                        } else {
                            echo $resAula['mensaje'];
                        }
                        ?>
                    </div>
                    <div class="col-md-2">
                        <label for="idDia">Dia de la semana: *</label>
                        <?php 
                        $resDias = $calendarioLogic->obtenerDias();
                        if ($resDias['estado']) {
                        ?>
                            <select name="idDia" id="idDia" class="form-control" required>
                                <option value="">Seleccione un día</option>
                                <?php 
                                foreach ($resDias['datos'] as $dato) {
                                    $id = $dato['idDia'];
                                    $nombreDia = $dato['nombreDia'];
                                    ?>
                                    <option value="<?php echo $id; ?>" <?php if ($idDia == $id ) { echo 'selected'; } ?>><?php echo $nombreDia; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        <?php 
                        } else {
                            echo $resDias['mensaje'];
                        }
                        ?>
                    </div>
                    <div class="col-md-2">
                        <label for="periodicidad">Periodicidad: *</label>
                        <select name="periodicidad" id="periodicidad" class="form-control" required>
                            <option value="DIARIO" <?php if ($periodicidad == 'DIARIO' ) { echo 'selected'; } ?>>Diario</option>
                            <option value="SEMANAL" <?php if ($periodicidad == 'SEMANAL' ) { echo 'selected'; } ?>>Semanal</option>
                            <option value="QUINCENAL" <?php if ($periodicidad == 'QUINCENAL' ) { echo 'selected'; } ?>>Quincenal</option>
                            <!--<option value="MENSUAL" <?php if ($periodicidad == 'MENSUAL' ) { echo 'selected'; } ?>>Mensual</option>-->
                        </select>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="fechaInicio">Fecha Inicio: *</label>
                        <input class="form-control" type="date" name="fechaInicio" id="fechaInicio" value="<?php echo $fechaInicio; ?>" min="<?php echo $fechaInicio; ?>" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="fechaFin">Fecha Fin: *</label>
                        <input class="form-control" type="date" name="fechaFin" id="fechaFin" value="<?php echo $fechaFin; ?>" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="horaInicio">Hora Inicio: *</label>
                        <input class="form-control" type="text" name="horaInicio" id="horaInicio" value="<?php echo $horaInicio; ?>" placeholder="HH.MM" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="horaFin">Hora Fin: *</label>
                        <input class="form-control" type="text" name="horaFin" id="horaFin" value="<?php echo $horaFin; ?>" placeholder="HH.MM" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="autorizado">Autorizado por: *</label>
                        <input class="form-control" type="text" name="autorizado" id="autorizado" value="<?php echo $autorizado; ?>" <?php echo $readOnly; ?>/>
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
                            <input type="hidden" name="idCursoEntidad" id="idCursoEntidad" value="<?php echo $idCursoEntidad; ?>">
                            <input type="hidden" name="periodo" id="periodo" value="<?php echo $periodo; ?>">
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
            <a href="calendario_eventos_administrar_turnos.php?id=<?php echo $idCursoEntidad; ?>" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
