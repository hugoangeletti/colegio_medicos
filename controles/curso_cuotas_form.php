<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
$accion = NULL;
$readOnly = NULL;
$requerido = NULL;
$cursos_pdo = new cursos_pdo();

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
if (isset($_GET['idCurso']) && $_GET['idCurso'] <> "") {
    $idCurso = $_GET['idCurso'];
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
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resCurso['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCurso - ";
}

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCursoCuota = $_GET['id'];
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
        $idCursoCuota = NULL;

        if (isset($_POST['mensaje'])) {
            $cuota = $_POST['cuota'];
            $detalleCuota = $_POST['detalleCuota'];
            $importe = $_POST['importe'];
            $fechaVencimiento = $_POST['fechaVencimiento'];
        } else {
            $resProximaCuota = $cursos_pdo->obtenerProximaCuotaPorIdCurso($idCurso);
            $cuota = $resProximaCuota['cuota'];
            $detalleCuota = $resProximaCuota['detalleCuota'];
            $importe = $resProximaCuota['importe'];
            $fechaVencimiento = $resProximaCuota['fechaVencimiento'];
        }
    }
}

if (isset($accion)) {
    if (isset($idCursoCuota) && $idCursoCuota <> "") {
        $resCuota = $cursos_pdo->obtenerCuotaDelCursoPorId($idCursoCuota);
        if ($resCuota['estado']) {
            $cursoCuota = $resCuota['datos'];
            $cuota = $cursoCuota['cuota'];
            $detalleCuota = $cursoCuota['detalleCuota'];
            $importe = $cursoCuota['importe'];
            $fechaVencimiento = $cursoCuota['fechaVencimiento'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resCuota['mensaje'];
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
                    <h5><?php echo $accion; ?> CUOTA DEL CURSO (#<?php echo $idCurso;?>) - <?php echo $titulo; ?>.</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="curso_cuotas.php?id=<?php echo $idCurso; ?>" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCuota" name="datosCuota" method="POST" action="datosCurso\abm_curso_cuota.php">
                <div class="row">
                    <div class="col-md-2">
                        <label for="cuota">Cuota: *</label>
                        <input class="form-control" type="number" name="cuota" id="cuota" value="<?php echo $cuota; ?>" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-2">
                        <label for="detalleCuota">Detalle de la cuota: *</label>
                        <input class="form-control" type="text" name="detalleCuota" id="detalleCuota" value="<?php echo $detalleCuota; ?>" placeholder="Ingrese el detalle" <?php echo $readOnly.$requerido; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label for="importe">Importe: *</label>
                        <input class="form-control" type="decimal" name="importe" id="importe" value="<?php echo $importe; ?>" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-2">
                        <label for="fechaVencimiento">Fecha de Vencimiento: *</label>
                        <input class="form-control" type="date" name="fechaVencimiento" id="fechaVencimiento" value="<?php echo $fechaVencimiento; ?>" min="<?php echo date('Y-m-d'); ?>" <?php echo $readOnly.$requerido; ?>/>
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
                            <input type="hidden" name="idCursoCuota" id="idCursoCuota" value="<?php echo $idCursoCuota; ?>">
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
