<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();

$continua = TRUE;
$mensaje = "";
$accion = NULL;
$titulo = "ENTIDAD PARA CERTIFICADOS ONLINE.";
$nombre = "";
if (isset($_GET['editar'])) {
    if (isset($_GET['id'])){
        $accion = "EDITAR";
        $titulo="Editar Entidad";
        $idEntidad = $_GET['id'];
        $resEntidad = $colegiadoCertificadosLogic->obtenerSolicitudCertificadoWebEntidadPorId($idEntidad);
        if ($resEntidad['estado']){
            $entidad = $resEntidad['datos'];
            $nombre = $entidad['nombre'];
            $visible = $entidad['visible'];
            $borrado = $entidad['borrado'];
        } else {
            $continua = FALSE;
            $mensaje = $resEntidad['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idEntidad - ';
    }
} else {
    if (isset($_GET['agregar'])) {
        $idEntidad = NULL;
        $accion = "AGREGAR";
        $titulo="Nueva Entidad.";
        $visible = 1;
        $borrado = 0;
    } else {
        $continua = FALSE;
        $mensaje .= 'Ingreso incorrecto - ';        
    }
}
if (isset($_POST['mensaje'])) {
?>
    <div id="divMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
    <?php    
    $nombre = $_POST['nombre'];
}   
?>
<div class="container-fluid">
    <div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
                <a href="certificado_online_entidad_lista.php" class="btn btn-primary" >Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formEntidad" name="formEntidad" method="POST" onSubmit="" action="datosColegiadoCertificado\abm_entidad.php">
                <div class="row">
                    <div class="col-md-7">
                        <label for="nombre">Nombre Entidad *</label>
                        <input class="form-control" autocomplete="OFF" type="text" name="nombre" id="nombre" placeholder="Ingrese nombre de la Entidad" value="<?php echo $nombre; ?>" required=""/>
                    </div>
                    <div class="col-md-1">
                        <label class="control-label">Visible: *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="visible" value="1" <?php if ($visible == '1') { ?> checked="" <?php } ?>>Si</label>
                        <label class="radio-inline"><input type="radio" name="visible" value="0" <?php if ($visible == '0') { ?> checked="" <?php } ?>>No</label>
                    </div>
                    <div class="col-md-1">
                        <label class="control-label">Borrado: *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="borrado" value="1" <?php if ($borrado == '1') { ?> checked="" <?php } ?>>Si</label>
                        <label class="radio-inline"><input type="radio" name="borrado" value="0" <?php if ($borrado == '0') { ?> checked="" <?php } ?>>No</label>
                    </div>
                    <div class="col-md-3">
                        <br>
                        <button type="submit" class="btn btn-success" >Guardar</button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                        <?php 
                        if (isset($idEntidad)) {
                        ?>
                            <input type="hidden" name="idEntidad" id="idEntidad" value="<?php echo $idEntidad; ?>">
                        <?php 
                        }
                        ?>
                    </div>
                </div>  
            </form>   
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="<?php echo $resRemitente['clase']; ?>" role="alert">
                        <span class="<?php echo $resRemitente['icono']; ?>" ></span>
                        <span><strong><?php echo $resRemitente['mensaje']; ?></strong></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';
?>
