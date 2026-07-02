<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
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
$cajaDiariaLogic = new cajaDiariaLogic();
$resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
if ($resCajaDiaria['estado']) {
    $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
} else {
    $continua = FALSE;
}

if ($continua) {
    if (isset($idColegiado)) {
        $tituloCajaDiaria = "Por Tipo de Pago";
        include_once 'encabezado_generar_recibo.php';
?>
    <div class="panel panel-info">
        <div class="panel-body">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosCajaDiaria/generar_recibo.php">
                <div class="row">
                    <div class="col-md-2">&nbsp;</div>
                    <div class="col-md-4">
                        <h4>Concepto a abonar.</h4>
                    </div>
                </div>
                <div class="row">
                    <?php
                    $resTipos = $tipoPagoLogic->ontenerTiposPagoParaRecibo();
                    if ($resTipos['estado']) {
                        $totalActualizado = 0;
                            foreach ($resTipos['datos'] as $row) {                                        
                                $importe = $row['importe'];
                                $idTipoPago = $row['id'];
                                $nombre = $row['nombre'];

                                //si no tiene importe o es certificacion de firma, no lo muestro
                                if ($importe == 0 || $idTipoPago == 62) continue;
                                ?>
                                <div class="col-md-6">
                                    <div class="col-md-8">
                                        <div class="form-check">
                                            <input class="form-check-input" name="generarRecibo[]" type="checkbox" id="<?php echo $idTipoPago; ?>"
                                                   value="<?php echo $idTipoPago.'_'.$importe; ?>" 
                                                   onclick="cambiaTotalRecibo(<?php echo $importe ?>, <?php echo $totalActualizado; ?>, <?php echo $idTipoPago; ?>)">
                                            <label class="form-check-label" for="<?php echo $idTipoPago.'_'.$importe ?>"><?php echo $nombre; ?></label>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="importe_<?php echo $idTipoPago; ?>" id="importe_<?php echo $idTipoPago; ?>" value="<?php echo $importe; ?>" readonly >
                                    </div>
                                </div>
                            <?php
                            }
                    }
                    ?>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-3">&nbsp;</div>
                    <div class="col-md-4">
                        <h4>Total a abonar:</h4> 
                        <input type="text" name="totalActualizado" id="totalActualizado" value="<?php echo $totalActualizado; ?>" readonly>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row"><?php include 'cajadiaria_forma_pago.php'; ?></div>
                <div class="row">&nbsp;</div>
                    <div class="col-md-12 text-center" id="bloque_confirmar">
                        <button type="submit"  class="btn btn-default">Confirma recibo</button>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        <input type="hidden" name="tipoRecibo" id="tipoRecibo" value="TIPO_PAGO" />
                    </div>
                </div>
            </form>
            <div class="row">&nbsp;</div>
        </div>
    </div>
<?php
    } else {
        //debe buscar el colegiado
        ?>
        <div class="row">
            <div class="col-md-12 text-center">
                <h4>Generar recibo de Colegiación</h4>
                <h5>Seleccione al colegiado/a</h5>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <?php 
        $link_form_origen = 'cajadiaria_tipo_pago_recibo.php';
        include_once 'buscar_colegiado.php';
        ?>
        <div class="row">&nbsp;</div>
        <div class="row text-center">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
                <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
            </form>
        </div>        
        <?php 
    }
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
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php?activos=SI',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idColegiado').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
    
    /*
    function cambiaTotalRecibo(importe, totalRecibo, idTipoPago)
    {
        var totalRecibo = parseInt(document.getElementById('totalRecibo').value);
        var valor = parseInt(totalRecibo);
        var importe = parseInt(importe);
        var x = document.getElementById('conFirmaRecibo');

        if (document.getElementById(idTipoPago).checked)
        {
            var valor = totalRecibo + importe;
        } else {
            var valor = totalRecibo - importe;
        }

        if (valor > 0) {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
        document.getElementById('totalRecibo').value = valor;
        var totalActualizado = parseInt(document.getElementById('totalRecibo').value);
        if (valor > 0) {
            $('#bloque_forma_pago').fadeIn(); 
            $('#bloque_confirmar').fadeIn();
        } else {
            $('#bloque_forma_pago').fadeOut();
            $('#bloque_confirmar').fadeOut();
        }
        
        return valor;
    }
    
    function actualizarTotalConRecargo() {
        var totalReciboBase = parseInt(document.getElementById('totalRecibo').value) || 0;
        var importeRecargo = 0;

        // Solo sumamos si el radio "Con recargo" está activo y estamos en forma de pago 2
        if ($('input[name="formaPago"]:checked').val() == "2" && $('#conRecargo').is(':checked')) {
            importeRecargo = parseInt(document.getElementById('importeRecargo').value) || 0;
        }

        // El total final es la base del recibo + el recargo
        var valorFinal = totalReciboBase + importeRecargo;
        
        // Aquí puedes actualizar el mismo input o uno nuevo para mostrar al usuario
        document.getElementById('totalRecibo').value = valorFinal; 
    }
    */
      $('#bloque_forma_pago').fadeIn(); // Aparece con efecto
    $('#bloque_confirmar').fadeIn(); // Aparece con efecto
  function cambiaTotalRecibo(importe, totalRecibo, idTipoPago) {
        var totalActual = parseInt(document.getElementById('totalActualizado').value) || 0;
        var monto = parseInt(importe);
        var x = document.getElementById('conFirmaRecibo');
        var valor;

        // Lógica original de sumar/restar según el checkbox
        if (document.getElementById(idTipoPago).checked) {
            valor = totalActual + monto;
        } else {
            valor = totalActual - monto;
        }

        // Actualizamos el input y manejamos visibilidad
        document.getElementById('totalActualizado').value = valor;
        document.getElementById('totalConRecargo').value = valor;

        document.getElementById('importeRecargo').value = 0;
        if (valor > 0) {
            x.style.display = "block";
            $('#bloque_forma_pago').fadeIn();
            $('#bloque_confirmar').fadeIn();
        } else {
            x.style.display = "none";
            $('#bloque_forma_pago').fadeOut();
            $('#bloque_confirmar').fadeOut();
        }

        return valor;
    }

    /*
    // Eventos para detectar el cambio en el recargo
    $(document).ready(function() {
        // Si escriben en el monto del recargo
        $('#importeRecargo').on('input', function() {
            // Nota: Esta lógica requiere que guardes el total "base" antes del recargo
            // o que restes el recargo anterior antes de sumar el nuevo.
            actualizarTotalConRecargo();
        });

        // Si cambian entre Con/Sin recargo
        $('input[name="tipoRecargo"]').change(function() {
            if ($(this).val() == "1") {
                $('#input_importe_recargo').fadeIn();
            } else {
                $('#input_importe_recargo').hide();
                $('#importeRecargo').val(0);
                actualizarTotalConRecargo();
            }
        });
    });
    */
</script>

