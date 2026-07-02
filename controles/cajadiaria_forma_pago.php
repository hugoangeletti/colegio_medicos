<?php
//require_once ('../dataAccess/config.php');
//permisoLogueado();
//require_once ('../html/head.php');
//require_once ('../html/header.php');
//require_once ('../dataAccess/funcionesConector.php');
//require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/formaPagoLogic.php');
require_once ('../dataAccess/bancoLogic.php');
$totalRecibo = 1000;
$importe = $totalRecibo;
$importe_efectivo = $importe;
$idBanco = NULL;
?>
<div class="row" id="bloque_forma_pago" style="display: none;">
    <hr>
    <div class="col-md-2">
        <h4><b>Forma de pago.</b></h4>
    </div>
    <?php
    $formaPagoLogic = new formaPagoLogic();
    $bancoLogic = new bancoLogic();
    $resBancos = $bancoLogic->obtenerBancos();
    $resFormaPago = $formaPagoLogic->obtenerFormasPago();
    if ($resFormaPago['estado']) {
    ?>
        <div class="col-md-2">
            <?php
            foreach ($resFormaPago['datos'] as $row) {                                   
                $idFormaPago = $row['id'];
                $nombre = $row['leyenda'];
                ?>
                <!--<div class="col-md-2">-->
                    <div class="form-check">
                        <input class="form-check-input" name="formaPago" type="radio" id="formaPago_<?php echo $idFormaPago; ?>" value="<?php echo $idFormaPago; ?>" required>
                        <label class="form-check-label" for="formaPago_<?php echo $idFormaPago; ?>"><?php echo $nombre; ?></label>
                    </div>
                <!--</div>-->
            <?php 
            }
            ?>
        </div>    
        <div class="col-md-6">
            <div class="row" id="bloque_recargo" style="display: none;">
            <?php 
            //si el usuario tiene permisos para el cobro con intereses, entonces se muestra el bloque
            if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 131)) {
            ?>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipoRecargo" id="sinRecargo" value="0" checked>
                        <label class="form-check-label" for="sinRecargo">Sin intereses</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipoRecargo" id="conRecargo" value="1">
                        <label class="form-check-label" for="conRecargo">Con intereses</label>
                    </div>
                </div>
                <!-- Campo para cargar el importe del recargo -->
                <div class="col-md-3" id="input_importe_recargo" style="display: none;">
                    <label>Importe intereses</label>
                    <input type="number" step="1" class="form-control" name="importeRecargo" id="importeRecargo" value="">
                </div>
                <!-- Visualización del total actualizado -->
                <div class="col-md-3" id="total_con_recargo" style="display: none;">
                    <label>Total con intereses</label>
                    <input type="text" class="form-control" name="totalConRecargo" id="totalConRecargo" readonly>
                </div>
            <?php 
            }
            ?>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row" id="bloque_banco" style="display: none;">
                <div class="col-md-4">
                    <label>Banco emisor:</label>
                    <select class="form-control" id="idBanco" name="idBanco">
                        <option value="">Seleccione banco</option>
                        <?php
                        foreach ($resBancos['datos'] as $banco) {                                        
                        ?>
                            <option value="<?php echo $banco['id']; ?>" <?php if ($idBanco == $banco['id']) { echo 'selected'; } ?> ><?php echo $banco['nombre']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Comprobante / Transferencia N°:</label>
                    <input type="text" class="form-control" name="comprobante" id="comprobante" placeholder="Número de transacción o transferencia">
                </div>        
            </div>
        </div>
    <?php
    }
    ?>
</div>
<script>
$(document).ready(function() {
    $('input[name="formaPago"]').change(function() {
        // Verificamos si el valor seleccionado es 2
        if ($(this).val() == "2") {
            $('#bloque_recargo').fadeIn(); // Aparece con efecto
        } else {
            $('#bloque_recargo').fadeOut(); // Se oculta
            // Opcional: Resetear a "Sin recargo" al ocultarse
            $('#sinRecargo').prop('checked', true);
        }
    });
});

$(document).ready(function() {
    $('input[name="formaPago"]').change(function() {
        // Verificamos si el valor seleccionado es 2
        if ($(this).val() != "1") {
            $('#bloque_banco').fadeIn(); // Aparece con efecto
        } else {
            $('#bloque_banco').fadeOut(); // Se oculta
        }
    });
});

$(document).ready(function() {
    function calcularTotal() {
        // Aquí debes capturar el valor real de tu total base
        //var totalComprobanteBase = parseFloat($("#total_original").val()) || 0; 
        var totalComprobanteBase = parseFloat($("#totalActualizado").val()) || 0; 

        var importeRecargo = parseFloat($('#importeRecargo').val()) || 0;
        var nuevoTotal = totalComprobanteBase + importeRecargo;
        $('#totalConRecargo').val(nuevoTotal.toFixed(2));
    }

    // 1. Lógica para Forma de Pago
    $('input[name="formaPago"]').change(function() {
        if ($(this).val() == "2") {
            $('#bloque_recargo').fadeIn();
            $('#fila_total_actualizado').fadeIn();
            calcularTotal();
        } else {
            $('#bloque_recargo').hide();
            $('#fila_total_actualizado').hide();
            resetearRecargo();
        }
    });

    // 2. Lógica para Activar el campo de Importe (tipoRecargo)
    $('input[name="tipoRecargo"]').change(function() {
        if ($(this).val() == "1") { // "1" es Con Recargo
            $('#input_importe_recargo').fadeIn();
            $('#total_con_recargo').fadeIn();
        } else {
            $('#input_importe_recargo').hide();
            $('#total_con_recargo').hide();
            $('#importeRecargo').val(0); // Resetea el valor si vuelve a "Sin recargo"
            $('#totalConRecargo').val(0); // Resetea el valor si vuelve a "Sin recargo"
            calcularTotal();
        }
    });

    // 3. Escuchar cambios en el input del importe
    $('#importeRecargo').on('input', function() {
        calcularTotal();
    });

    function resetearRecargo() {
        $('#sinRecargo').prop('checked', true);
        $('#importeRecargo').val(0);
        $('#input_importe_recargo').hide();
    }
});</script>