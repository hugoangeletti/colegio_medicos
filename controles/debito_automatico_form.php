<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/debitoAutomaticoLogic.php');

$continua = TRUE;
$mensaje = "";

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
if (isset($_POST['tipoDebito']) && $_POST['tipoDebito'] <> "") {
    $tipoDebito = $_POST['tipoDebito'];
} else {
    $tipoDebito = "";
}
if (isset($_POST['fechaDebito']) && $_POST['fechaDebito'] <> "") {
    $fechaDebito = $_POST['fechaDebito'];
} else {
    $fechaDebito = date('Y-m-d');
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Liquidación Débito Automático.</h4>
            </div>
            <div class="col-md-3 text-right">
                <a href="home_banking.php" class="btn btn-primary" >Volver</a>
            </div> 
        </div>
    </div>
    <div class="panel-body">
        <form id="datosDebitoAutomatico" name="datosDebitoAutomatico" method="POST" action="datosDebitoAutomatico\genera_archivos.php">
            <div class="row">
                <div class="col-md-3">
                    <label for="tipoDebito">Tipo débito</label>
                    <select class="form-control" id="tipoDebito" name="tipoDebito" required >
                        <option value="" selected>Seleccione tipo débito</option>
                        <option value="<?php echo TARJETA_DEBITO; ?>" <?php if($tipoDebito == TARJETA_DEBITO) { echo 'selected'; } ?>>TARJETA DEBITO</option>
                        <option value="<?php echo TARJETA_CREDITO; ?>" <?php if($tipoDebito == TARJETA_CREDITO) { echo 'selected'; } ?>>TARJETA CREDITO</option>
                        <option value="<?php echo CBU; ?>" <?php if($tipoDebito == CBU) { echo 'selected'; } ?>>CBU</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="fechaDebito">Fecha de Débito: </label>
                    <input class="form-control" type="date" name="fechaDebito" id="fechaDebito" value="<?php echo $fechaDebito; ?>" min="<?php echo date('Y-m-d'); ?>" required />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-6 text-center">
                    <br>
                    <button type="submit" name='confirma' id='confirma' class="btn btn-primary">Generar envio</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <a href="home_banking.php" class="btn btn-primary" >Volver</a>
    </div>
</div>
<?php            
require_once '../html/footer.php';
