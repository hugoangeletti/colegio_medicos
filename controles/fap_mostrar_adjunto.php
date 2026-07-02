<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
?>
<script language="javascript" type="text/javascript"> 
    function closed() { 
       window.open('','_parent',''); 
       window.close(); 
    } 
</script>
<?php
$continua = TRUE;
$mensaje = "";
$fapLogic = new fapLogic();
if (isset($_GET['id'])) {
    $idSapCaratulaArchivo = $_GET['id'];
    $resCaratulaArchivo = $fapLogic->obtenerCaratulaArchivoPorId($idSapCaratulaArchivo);
    if ($resCaratulaArchivo['estado']) {
        $caratulaArchivo = $resCaratulaArchivo['datos'];
        $idSapCaratula = $caratulaArchivo['idSapCaratula'];
        $path = $caratulaArchivo['path'];
        $nombreArchivo = $caratulaArchivo['nombreArchivo'];
        $extensionAdjunto = $caratulaArchivo['extensionAdjunto'];

        $resRegistro = $fapLogic->obtenerSapCaratulaPorId($idSapCaratula);
        if ($resRegistro['estado']) {
            $registro = $resRegistro['datos'];
            $idColegiado = $registro['idColegiado'];
            $nombreCausa = $registro['nombreCausa'];

            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado']) {
                $colegiado = $resColegiado['datos'];
                $matricula = $colegiado['matricula'];
                $apellido = $colegiado['apellido'];
                $nombre = $colegiado['nombre'];
            } else {
                $continua = FALSE;
                $mensaje .= $resColegiado['mensaje'];
            }
        } else {
            $continua = FALSE;
            $mensaje .= $resRegistro['mensaje'];
        }

    } else {
        $continua = FALSE;
        $mensaje .= $resCaratulaArchivo['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idSapCaratulaArchivo';
}

if ($continua){
?>
    <div class="row">
        <div class="col-md-12">
            <h3>Archivos adjuntos al expediente FAP</h3>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-8">
            <h4>Carátula: <b><?php echo $nombreCausa; ?></b></h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <label>Apellido y Nombre </label>
            <input class="form-control" type="text" name="apellido" value="<?php echo trim($apellido).' '.trim($nombre); ?>" readonly="" />
        </div>
        <div class="col-md-2">
            <label>Matrícula </label>
            <input class="form-control" type="text" name="matricula" value="<?php echo $matricula; ?>" readonly=""/>
        </div>
    </div>
    <div class="row">&nbsp;</div>    <div class="row">
        <div class="col-md-12 text-center">
            <?php 
            if (strtoupper($extensionAdjunto) == "PDF") {
                echo '<embed src="'.$path.'/'.$nombreArchivo.'" type="application/pdf" width="100%" height="600px" />';
            } else {
                echo '<img src="'.$path.'/'.$nombreArchivo.'" alt="Archivo adjunto" >';
            }
            ?>
        </div>
    </div>
<?php
} else {
?>
    <div class="alert alert-danger" role="alert">
        <h3><?php echo $mensaje; ?></h3>
    </div>
<?PHP    
}
?>
<div class="row">
    <div class="col-md-12">
        <a href="fap_adjunto_form.php?id=<?php echo $idSapCaratula; ?>" class="btn btn-secondary" title="">Volver</a>
    </div>
</div>
<?php
require_once '../html/footer.php';