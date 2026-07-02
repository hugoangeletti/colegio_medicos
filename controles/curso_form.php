<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');

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
    $idCurso = $_GET['id'];
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
        $idCurso = NULL;

        $titulo = NULL;
        $director = NULL;
        $fechaInicio = date('Y-m-d');
        $estadoCurso = "A";
        $tema = NULL;
        $dias = NULL;
        $fechas = NULL;
        $salon = NULL;
        $lugar = NULL;
        $coordinador = NULL;
        $vigenciaHasta = NULL;
        $inscripcionDesde = NULL;
        $inscripcionHasta = NULL;
        if (isset($_POST['mensaje'])) {
            $titulo = $_POST['titulo'];
            $director = $_POST['director'];
            $fechaInicio = $_POST['fechaInicio'];
            $estadoCurso = $_POST['estado'];
            $tema = $_POST['tema'];
            $dias = $_POST['dias'];
            $fechas = $_POST['fechas'];
            $salon = $_POST['salon'];
            $lugar = $_POST['lugar'];
            $coordinador = $_POST['coordinador'];
            $vigenciaHasta = $_POST['vigenciaHasta'];
            $inscripcionDesde = $_POST['inscripcionDesde'];
            $inscripcionHasta = $_POST['inscripcionHasta'];
        }
    }
}

if (isset($accion)) {
    if (isset($idCurso) && $idCurso <> "") {
        $cursos_pdo = new cursos_pdo();
        $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
        if ($resCurso['estado']) {
            $curso = $resCurso['datos'];
            $titulo = $curso['titulo'];
            $director = $curso['director'];
            $fechaInicio = $curso['fechaInicio'];
            $estadoCurso = $curso['estado'];
            $tema = $curso['tema'];
            $dias = $curso['dias'];
            $fechas = $curso['fechas'];
            $salon = $curso['salon'];
            $lugar = $curso['lugar'];
            $coordinador = $curso['coordinador'];
            $vigenciaHasta = $curso['vigenciaHasta'];
            $inscripcionDesde = $curso['inscripcionDesde'];
            $inscripcionHasta = $curso['inscripcionHasta'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resCurso['mensaje'];
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
                    <h5><?php echo $accion; ?> CURSO.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="curso_listado.php" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCurso" name="datosCurso" method="POST" action="datosCurso\abm_curso.php">
                <div class="row">
                    <div class="col-md-6">
                        <label for="titulo">Título: *</label>
                        <input class="form-control" type="text" name="titulo" id="titulo" value="<?php echo $titulo; ?>" placeholder="Ingrese el título del curso" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-6">
                        <label for="tema">Tema: </label>
                        <input class="form-control" type="text" name="tema" id="tema" value="<?php echo $tema; ?>" placeholder="Ingrese el tema del curso" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="director">Director: </label>
                        <input class="form-control" type="text" name="director" id="director" value="<?php echo $director; ?>" placeholder="Ingrese el director del curso" <?php echo $readOnly; ?> />
                    </div>
                    <div class="col-md-6">
                        <label for="coordinador">Coordinador: </label>
                        <input class="form-control" type="text" name="coordinador" id="coordinador" value="<?php echo $coordinador; ?>" placeholder="Ingrese el coordinador del curso" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        <label for="fechaInicio">Fecha de Inicio: *</label>
                        <input class="form-control" type="date" name="fechaInicio" id="fechaInicio" value="<?php echo $fechaInicio; ?>" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="vigenciaHasta">Fecha de Finalización: </label>
                        <input class="form-control" type="date" name="vigenciaHasta" id="vigenciaHasta" value="<?php echo $vigenciaHasta; ?>" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="estadoCurso">Estado del curso: *</label>
                        <select class="form-control" id="estadoCurso" name="estadoCurso"  <?php echo $readOnly.$requerido; ?>>
                            <option value="A" <?php if($estadoCurso == "A") { echo 'selected'; } ?>>Activo</option>
                            <option value="F" <?php if($estadoCurso == "F") { echo 'selected'; } ?>>Finalizado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="inscripcionDesde">Inicio Inscripciones: *</label>
                        <input class="form-control" type="date" name="inscripcionDesde" id="inscripcionDesde" value="<?php echo $inscripcionDesde; ?>" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="inscripcionHasta">Finalización Inscripciones: *</label>
                        <input class="form-control" type="date" name="inscripcionHasta" id="inscripcionHasta" value="<?php echo $inscripcionHasta; ?>" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="dias">Días: </label>
                        <input class="form-control" type="text" name="dias" id="dias" value="<?php echo $dias; ?>" placeholder="Ingrese los días que se dicta" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-3">
                        <label for="fechas">Fechas: </label>
                        <input class="form-control" type="text" name="fechas" id="fechas" value="<?php echo $fechas; ?>" placeholder="Ingrese las fechas que se dicta" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-3">
                        <label for="salon">Salon: </label>
                        <input class="form-control" type="text" name="salon" id="salon" value="<?php echo $salon; ?>" placeholder="Ingrese el salon donde se dicta" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-3">
                        <label for="lugar">Lugar: </label>
                        <input class="form-control" type="text" name="lugar" id="lugar" value="<?php echo $lugar; ?>" placeholder="Ingrese el lugar donde se dicta" <?php echo $readOnly; ?>/>
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
                            <input type="hidden" name="idCurso" id="idCurso" value="<?php echo $idCurso; ?>">
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
            <a href="curso_listado.php" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
