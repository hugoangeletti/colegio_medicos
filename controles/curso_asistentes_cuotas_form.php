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
    $cuota = $_POST['cuota'];
    $detalleCuota = $_POST['detalleCuota'];
    $importe = $_POST['importe'];
    $fechaVencimiento = $_POST['fechaVencimiento'];
    $fechaPago = $_POST['fechaPago'];
    $recibo = $_POST['recibo'];
}
if (isset($_GET['idCursosAsistente']) && $_GET['idCursosAsistente'] <> "") {
    $idCursosAsistente = $_GET['idCursosAsistente'];
    $resAsistente = $cursos_pdo->obtenerAsistentePorId($idCursosAsistente);
    if ($resAsistente['estado']) {
        $asistente = $resAsistente['datos'];
        $apellidoNombre = $asistente['apellidoNombre'];
        $matricula = $asistente['matricula'];

        $idCurso = $asistente['idCurso'];
        $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
        if ($resCurso['estado']) {
            $curso = $resCurso['datos'];
            $idCurso = $curso['idCurso'];
            $titulo = $curso['titulo'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resCurso['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resAsistente['mensaje'];
    }

    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idCursosAsistenteCuota = $_GET['id'];
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
            $idCursosAsistente = NULL;

            if (!isset($_POST['mensaje'])) {
                $idColegiado = NULL;
                $apellidoNombre = NULL;
                $esColegiado = "S";
            }
        }
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCursosAsistente - ";
}

if (isset($accion)) {
    if (isset($idCursosAsistenteCuota) && $idCursosAsistenteCuota <> "") {
        $resCuota = $cursos_pdo->obtenerCursosAsistenteCuotaPorId($idCursosAsistenteCuota);
        if ($resCuota['estado']) {
            $cuotaAsistente = $resCuota['datos'];
            $cuota = $cuotaAsistente['cuota'];
            $detalleCuota = $cuotaAsistente['detalleCuota'];
            $importe = $cuotaAsistente['importe'];
            $fechaVencimiento = $cuotaAsistente['fechaVencimiento'];
            $fechaPago = $cuotaAsistente['fechaPago'];
            $recibo = $cuotaAsistente['recibo'];
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
                    <h5>
                        <?php echo $accion; ?> ASISTENTE DEL CURSO (#<?php echo $idCurso;?>) - <?php echo $titulo; ?>.
                        <br>
                        Apellido y nombre: <b><?php echo $apellidoNombre.'</b>'; if (isset($matricula) && $matricula <> "") { echo ' - Matrícula: <b>'.$matricula.'</b>'; }?>
                    </h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="curso_asistentes_cuotas.php?id=<?php echo $idCursosAsistente; ?>" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCurso" name="datosCurso" method="POST" action="datosCurso\abm_curso_asistente_cuota.php">
                <div class="row">
                    <div class="col-md-2">
                        <label for="cuota">Cuota: *</label>
                        <input class="form-control" type="number" name="cuota" id="cuota" value="<?php echo $cuota; ?>" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-2">
                        <label for="detalleCuota">Detalle cuota: *</label>
                        <input class="form-control" type="text" name="detalleCuota" id="detalleCuota" value="<?php echo $detalleCuota; ?>" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-2">
                        <label for="importe">Importe: *</label>
                        <input class="form-control" type="decimal" name="importe" id="importe" value="<?php echo $importe; ?>" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-2">
                        <label for="fechaVencimiento">Fecha Vencimiento: *</label>
                        <input class="form-control" type="date" name="fechaVencimiento" id="fechaVencimiento" value="<?php echo $fechaVencimiento; ?>" <?php echo $readOnly.$requerido; ?> />
                    </div>
                    <div class="col-md-2">
                        <label for="fechaPago">Fecha Pago: </label>
                        <input class="form-control" type="date" name="fechaPago" id="fechaPago" value="<?php echo $fechaPago; ?>" <?php echo $readOnly; ?> />
                    </div>
                    <div class="col-md-2">
                        <label for="recibo">Recibo: </label>
                        <input class="form-control" type="number" name="recibo" id="recibo" value="<?php echo $recibo; ?>" <?php echo $readOnly; ?> />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <?php 
                if ($accion <> "CONSULTAR") {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <br>
                            <button type="submit" name='confirma' id='confirma' class="btn btn-primary">Guardar</button>
                            <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                            <input type="hidden" name="idCursosAsistenteCuota" id="idCursosAsistenteCuota" value="<?php echo $idCursosAsistenteCuota; ?>">
                            <input type="hidden" name="idCursosAsistente" id="idCursosAsistente" value="<?php echo $idCursosAsistente; ?>">
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
