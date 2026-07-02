<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
require_once ('../dataAccess/usuarioLogic.php');
require_once ('../dataAccess/tipoPagoLogic.php');
$tipoPagoLogic = new tipoPagoLogic();

$idUsuario = $_SESSION['user_id'];
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    if (isset($_POST['idColegiado'])) {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
    }
}
$continua = TRUE;
$resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
if ($resCajaDiaria['estado']) {
    $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
} else {
    $continua = FALSE;
}

if ($continua) {
    $nombre = NULL;
    $cuit = NULL;
    $domicilio = NULL;
    $totalRecibo = 0;
    $concepto = NULL;
?>
    <div class="col-md-12 alert alert-info">
        <div class="row">
            <div class="col-md-9">
                <h4>Caja Diaria - Otros ingresos personalizado</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
                    <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-body">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosCajaDiaria/generar_recibo.php" >
                <div class="row">
                    <div class="col-md-3">
                        <label>Apellido y Nombre / Razón Social: * </label>
                        <input class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" autofocus type="text" name="nombre" value="<?php echo $nombre; ?>" required=""/>
                    </div>
                    <div class="col-md-2">
                        <label>Cuit/Cuil: * </label>
                        <input class="form-control" type="text" name="cuit" id="cuit" value="<?php echo $cuit; ?>" required=""/>
                    </div>
                    <div class="col-md-4">
                        <label>Domicilio completo: * </label>
                        <input class="form-control" type="text" name="domicilio" id="domicilio" value="<?php echo $domicilio; ?>"  required="" placeholder="Ingrese Calle / Numero / Localidad"/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <?php
                    $resTipos = $tipoPagoLogic->ontenerTiposPagoParaRecibo();
                    if ($resTipos['estado']) {
                        ?>
                        <div class="col-md-3">
                            <label>Concepto: * </label>
                            <select class="form-control" name="tipoPago" id="tipoPago" required>
                                <option value="">Seleccione concepto a abonar</option>
                                <?php
                                foreach ($resTipos['datos'] as $row) {                                        
                                    $idTipoPago = $row['id'];
                                    $nombre = $row['nombre'];
                                    $importe = $row['importe'];

                                    if ($importe == 0 || $idTipoPago == 62) {
                                    ?>
                                        <option value="<?php echo $idTipoPago ?>"><?php echo $nombre; ?></option>
                                    <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    <?php 
                    }
                    ?>
                    <div class="col-md-4">
                        <label>Descripción: * </label>
                        <input class="form-control" type="text" name="concepto" id="concepto" value="<?php echo $concepto; ?>" required="" placeholder="Ingrese el concepto que abona"/>
                    </div>
                    <div class="col-md-2">
                        <label>Total a abonar: *</label>
                        <input class="form-control" type="decimal" name="importe" id="importe" value="<?php echo $totalRecibo; ?>" required=""/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                    <?php
                    include 'cajadiaria_forma_pago.php'; 
                    ?>   

                <div class="row">
                    <div class="col-md-12 text-center" id="bloque_confirmar" style="display: none;">
                        <button type="submit"  class="btn btn-default">Confirma recibo</button>
                        <input type="hidden" name="tipoRecibo" id="tipoRecibo" value="OTROS_INGRESOS" />
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php    
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="alert alert-warning">NO HAY CAJA ABIERTA, DEBE IR A CAJAS DIARIAS Y ABRIR PRIMERO UNA CAJA DEL DIA</div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row text-center">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
            <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
        </form>
    </div>
<?php      
}
require_once '../html/footer.php';
?>
<script language="JavaScript">
    $('#bloque_forma_pago').fadeIn(); // Aparece con efecto
    $('#bloque_confirmar').fadeIn(); // Aparece con efecto
    
    function cambiaTotalCuotas(importe, totalActualizado, idColegiadoDeudaAnualCuota){
        var totalActualizado = parseInt(document.getElementById('totalActualizado').value);
        var valor = parseInt(totalActualizado);
        var importe = parseInt(importe);

        if (document.getElementById(idColegiadoDeudaAnualCuota).checked)
        {
            var valor = totalActualizado + importe;
        } else {
            var valor = totalActualizado - importe;
        }

        if (valor > 0) {
            $('#bloque_forma_pago').fadeIn(); // Aparece con efecto
            $('#bloque_confirmar').fadeIn(); // Aparece con efecto
        } else {
            $('#bloque_forma_pago').fadeOut(); // Se oculta
            $('#bloque_confirmar').fadeOut(); // Se oculta
        }
        document.getElementById('totalActualizado').value = valor;
        document.getElementById('importeRecargo').value = 0;
        
        return valor;
    }

</script>