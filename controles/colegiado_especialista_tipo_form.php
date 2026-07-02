<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../dataAccess/resolucionesLogic.php');

$continua = TRUE;
$mensaje = "";

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idColegiadoEspecialista = $_GET['id'];
    $resEspecialista = $colegiadoEspecialistaLogic->obtenerColegiadoEspecialistaPorId($idColegiadoEspecialista);
    if ($resEspecialista['estado']){
        $datos = $resEspecialista['datos'];
        $nombreEspecialidad = $datos['nombreEspecialidad'];
        $idColegiado = $datos['idColegiado'];

        //busco los datos del colegiado
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        } else { 
            $mensaje .= $resColegiado['mensaje'];
            $continua = FALSE;
        }
    } else {
        //error al buscar expediente
        $mensaje .= $resEspecialista['mensaje'];
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta id - ";
}
if (isset($_GET['accion']) && $_GET['accion'] <> "") {
    $accion = $_GET['accion'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion - ";
}
if (isset($_GET['tipo']) && $_GET['tipo'] <> "") {
    $idTipoEspecialista = $_GET['tipo'];
    switch ($idTipoEspecialista) {
        case JERARQUIZADO:
            $tipoEspecialistaDetalle = "JERARQUIZADO";
            break;
        
        case CONSULTOR:
            $tipoEspecialistaDetalle = "CONSULTOR";
            break;
        
        default:
            $tipoEspecialistaDetalle = "";
            break;
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta tipo - ";
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Datos de tipo especialista <?php echo $tipoEspecialistaDetalle; ?></h4>
            </div>
            <div class="col-md-3 text-left">
                <?php 
                if (isset($idColegiado)) {
                ?>
                    <a href="colegiado_especialista.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-primary">Volver</a>
                <?php
                } else {
                ?>
                    <a href="colegiado_consulta.php">Volver</a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    if ($continua) {
        if (isset($_POST['mensaje'])) {
        ?>
           <div class="ocultarMensaje"> 
               <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
           </div>
            <?php        
            $idColegiadoEspecialistaTipo = $_POST['idColegiadoEspecialistaTipo'];
            $fecha = $_POST['fecha'];
            $distritoOtorgante = $_POST['distritoOtorgante'];
        } else {
            if ($accion <> 1) {
                $resTipoEspecialista = $colegiadoEspecialistaLogic->obtenerEspecialistaTipoPorIdColegiadoEspecialista($idColegiadoEspecialista, $idTipoEspecialista);
                if ($resTipoEspecialista['estado']) {
                    $tipoEspecialista = $resTipoEspecialista['datos'];
                    $idColegiadoEspecialistaTipo = $tipoEspecialista['idColegiadoEspecialistaTipo'];
                    $fecha = $tipoEspecialista['fecha'];
                    $distritoOtorgante = $tipoEspecialista['distritoOtorgante'];        
                } else {
                    //error al buscar tipo especialista
                    $mensaje .= $resEspecialista['mensaje'];
                    $continua = FALSE;
                }
            } else {
                $idColegiadoEspecialistaTipo = NULL;
                $fecha = NULL;
                $distritoOtorgante = NULL;
            }
        }
        ?>
        <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-4">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
            </div>
            <div class="col-md-6">&nbsp;</div>
        </div>
        <div class="row">
            <div class="col-md-6 text-center"><h4><b>Actualizar datos </b></h4></div>
        </div>
        <form id="datosColegiadoEspecialista" autocomplete="off" name="datosColegiadoEspecialista" method="POST" onSubmit="" action="datosColegiadoEspecialista\abm_especialista_tipo.php">
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-2">
                    <label>Fecha *</label>
                    <input class="form-control" type="date" id="fecha" name="fecha" value="<?php echo $fecha; ?>" required=""/>
                </div>
                <div class="col-md-2">
                    <label>Distrito otorgante *</label>
                    <input class="form-control" type="text" id="distritoOtorgante" name="distritoOtorgante" value="<?php echo $distritoOtorgante; ?>" min="1" max="10" required=""/>
                </div>
                <div class="col-md-2">
                    <br>
                    <button type="submit"  class="btn btn-success " >Confirma </button>
                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    <input type="hidden" name="idColegiadoEspecialista" id="idColegiadoEspecialista" value="<?php echo $idColegiadoEspecialista; ?>" />
                    <input type="hidden" name="idTipoEspecialista" id="idTipoEspecialista" value="<?php echo $idTipoEspecialista; ?>" />
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    <?php 
                    if (isset($idColegiadoEspecialistaTipo) && $idColegiadoEspecialistaTipo <> "") {
                    ?>
                        <input type="hidden" name="idColegiadoEspecialistaTipo" id="idColegiadoEspecialistaTipo" value="<?php echo $idColegiadoEspecialistaTipo; ?>" />
                    <?php
                    }
                    ?>
                </div>
            </div>    
        </form>
    <?php
    }
?>
    </div>    
</div>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
