<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/remitenteLogic.php');

$continua = TRUE;
$mensaje = "";
$accion = NULL;
$titulo = "REMITENTE.";
$nombre = "";
$remitenteLogic = new remitenteLogic();
if (isset($_GET['editar'])) {
    if (isset($_GET['id'])){
        $accion = "EDITAR";
        $titulo="Editar Remitente";
        $idRemitente = $_GET['id'];
        $resRemitente = $remitenteLogic->obtenerRemitentePorId($idRemitente);
        if ($resRemitente['estado']){
            $remitente = $resRemitente['datos'];
            $idRemitente = $remitente['idRemitente'];
            $nombre = $remitente['nombre'];
        } else {
            $continua = FALSE;
            $mensaje = $resRemitente['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idRemitente - ';
    }
} else {
    if (isset($_GET['agregar'])) {
        $idRemitente = NULL;
        $accion = "AGREGAR";
        $titulo="Nuevo Remitente.";
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
                <a href="remitente_lista.php" class="btn btn-primary" >Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formRemitente" name="formRemitente" method="POST" onSubmit="" action="datosRemitente\abm_remitente.php">
                <div class="row">
                    <div class="col-md-7">
                        <label for="nombre">Nombre Remitente *</label>
                        <input class="form-control" autocomplete="OFF" type="text" name="nombre" id="nombre" placeholder="Ingrese nombre del Remitente para Notas/Oficios" value="<?php echo $nombre; ?>" required=""/>
                    </div>
                    <div class="col-md-5">
                        <br>
                        <button type="submit" class="btn btn-success" >Guardar</button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                        <input type="hidden" name="idRemitente" id="idRemitente" value="<?php echo $idRemitente; ?>">
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
