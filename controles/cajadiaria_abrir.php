<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
$fechaCaja = date('Y-m-d');
$saldoInicial = 0;    
?>
<form id="formCajaDiaria" name="formCajaDiaria" method="POST" onSubmit="" action="datosCajaDiaria/abm_cajaDiaria.php">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3>Caja Diaria del &nbsp;<?php echo cambiarFechaFormatoParaMostrar($fechaCaja); ?></h3>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-2">
                    <label>Saldo inicial * </label>
                    <input type="float" class="form-control" name="saldoInicial" id="saldoInicial" value="<?php echo $saldoInicial; ?>" required="" />
                </div>
                <div class="col-md-2">
                    <br>
                    <button type="submit"  class="btn btn-info" >Confirmar</button>
                    <input type="hidden" name="fechaCaja" id="fechaCaja" value="<?php echo $fechaCaja; ?>" required="" />
                    <input type="hidden" name="accion" id="accion" value="Abrir" required="" />
                </div>
            </div>
        </div>

        <div class="row">&nbsp;</div>
    </div>
</form>        
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-12">
        <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="cajadiaria.php">
            <button type="submit"  class="btn btn-info" >Volver</button>
        </form>
    </div>
</div>
<?php 
require_once '../html/footer.php';
