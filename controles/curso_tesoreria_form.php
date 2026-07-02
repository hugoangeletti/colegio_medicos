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

$idCurso =  isset($_GET['id']) ? $_GET['id'] : NULL;

if (!empty($idCurso)) {
    $cursos_pdo = new cursos_pdo();
    $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
    if ($resCurso['estado']) {
        $curso = $resCurso['datos'];
        $titulo = $curso['titulo'];
        $valorCuotaLiquidacion = $curso['valorCuotaLiquidacion'];
        $porcentajeRetencionColegio = $curso['porcentajeRetencionColegio'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resCurso['mensaje'];
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
                    <h5>Datos de Tesorería del CURSO (<?php echo '['.$idCurso.'] '.$titulo; ?>).</h5>
                </div>
                <div class="col-md-3 text-right">
                    <a href="curso_listado.php" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosCurso" name="datosCurso" method="POST" action="datosCurso\abm_curso.php?id=<?php echo $idCurso; ?>&tesoreria">
                <div class="row">
                    <div class="col-md-3">
                        <label for="valorCuotaLiquidacion">Valor cuota para liquidación: </label>
                        <input class="form-control" type="decimal" name="valorCuotaLiquidacion" id="valorCuotaLiquidacion" value="<?php echo $valorCuotaLiquidacion; ?>" />
                    </div>
                    <div class="col-md-3">
                        <label for="porcentajeRetencionColegio">Porcentaje retención Colegio: </label>
                        <input class="form-control" type="number" name="porcentajeRetencionColegio" id="porcentajeRetencionColegio" value="<?php echo $porcentajeRetencionColegio; ?>" required/>
                    </div>
                    <div class="col-md-3">
                        <br>
                        <button type="submit" name='confirma' id='confirma' class="btn btn-primary">Guardar</button>
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
            <a href="curso_listado.php" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
