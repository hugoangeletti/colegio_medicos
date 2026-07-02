<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/distritoLogic.php');
$distritoLogic = new distritoLogic();

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
$continua = TRUE;
if (isset($_POST['idDistrito']) && $_POST['idDistrito']) {
    $idDistrito = $_POST['idDistrito'];
} else {
    $continua = FALSE;
}

if ($continua) {
    $resDistrito = $distritoLogic->obtenerDistritoPorId($idDistrito);
    if ($resDistrito['estado']) {
        $distrito = $resDistrito['datos'];
        $numeroDistrito = $distrito['distrito'];
        $presidente = $distrito['presidente'];
        $domicilio = $distrito['domicilio'];
        $mail = $distrito['mail'];
        $pagina = $distrito['pagina'];
    ?>
        <form id="formDistrito" name="formDistrito" method="POST" onSubmit="" action="datosDistritos/abm_distritos.php">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3>Datos del Distrito &nbsp;<?php echo $numeroDistrito; ?></h3>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-4">
                        <label>Presidente * </label>
                        <input type="text" class="form-control" name="presidente" id="presidente" value="<?php echo $presidente; ?>" required="" />
                    </div>
                    <div class="col-md-4">
                        <label>Domicilio *</label>
                        <input type="text" class="form-control" name="domicilio" id="domicilio" value="<?php echo $domicilio; ?>" required="" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-4">
                        <label>Email *</label>
                        <input class="form-control" type="email" name="mail" id="mail" value="<?php echo $mail; ?>" required="" />
                    </div>
                    <div class="col-md-4">
                        <label>Página </label>
                        <input class="form-control" type="text" name="pagina" id="pagina" value="<?php echo $pagina; ?>" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-8 text-center">
                        <label>&nbsp;</label>
                        <button type="submit"  class="btn btn-info" >Confirmar</button>
                        <input class="form-control" type="hidden" name="idDistrito" id="idDistrito" value="<?php echo $idDistrito; ?>" />
                    </div>
                </div>

                <div class="row">&nbsp;</div>
            </div>
        </form>        
    <?php 
    } else {
    ?>
        <div class="<?php echo $resDistrito['clase']; ?>" role="alert">
            <span class="<?php echo $resDistrito['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resDistrito['mensaje']; ?></strong></span>
        </div>
    <?php    
    }
} else {
    echo 'datos mal ingresados<br>';
}
?>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-12">
        <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="distritos_listado.php">
            <button type="submit"  class="btn btn-info" >Volver</button>
        </form>
    </div>
</div>
<?php 
require_once '../html/footer.php';
