<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();

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
    $periodoActual = $_SESSION['periodoActual'];

    if (isset($idColegiado)) {
        $tituloCajaDiaria = "Cobranza de colegiación";
        include_once 'encabezado_generar_recibo.php';

        $idResponsable = NULL;
        $idTipoCondonacion = NULL;
        $observaciones = NULL;
        //obtengo la deuda, inicializo los campos a mostrar
        $totalDeuda = 0;
        $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualAPagar($idColegiado);
        if ($resDeuda['estado']) {
            //inicializo los totales
            foreach ($resDeuda['datos'] as $row) {
                $totalDeuda += $row['importeActualizado'];
            }
        }

        $totalDeudaPP = 0;
        $resDeudaPP = $colegiadoPlanPagoLogic->obtenerDeudaPlanPagosPorIdColegiado($idColegiado);
        if ($resDeudaPP['estado']) {
            foreach ($resDeudaPP['datos'] as $row) {
                $totalDeudaPP += $row['importeActualizado'];
            }
        }

        if (isset($_POST['conRecargo'])) {
            $conRecargo = $_POST['conRecargo'];
        } else {
            $conRecargo = 'SI';
        }

    ?>        
    <div class="panel panel-info">
        <div class="panel-body">
            <div class="row">
                <?php 
                if ($usuarioLogic->verificarRolUsuario($idUsuario, 22)) {
                 //tiene permiso para condonar deuda
                ?>
                    <div class="col-md-2">
                        <label><b>Con recargo: </b></label>
                        <form method="POST" action="cajadiaria_colegiacion_recibo.php?idColegiado=<?php echo $idColegiado; ?>">
                            <select class="form-control" id="conRecargo" name="conRecargo" required onChange="this.form.submit()">
                                <option value="SI" <?php if ($conRecargo == 'SI') { echo 'selected'; } ?> >Si</option>
                                <option value="NO" <?php if ($conRecargo == 'NO') { echo 'selected'; } ?> >No</option>
                            </select>
                        </form>
                    </div>
                <?php 
                }
                ?>
            </div>
            <form id="datosPlanPagos" autocomplete="off" name="datosCondonacion" method="POST" action="datosCajaDiaria\generar_recibo.php">
                <?php
                $totalDeudaActualizada = 0;
                if ($totalDeuda+$totalDeudaPP > 0) {
                ?>
                    <div class="row">
                        <?php
                        if ($totalDeuda > 0) {
                        ?>
                            <div class="col-md-12">
                                <h4><b class="text-center">Cuotas de Colegiación &nbsp;</b></h4>
                                <hr>
                            </div>
                            <div class="form-check">
                                <?php
                                //si el pago total esta vigente, muestro la opcion
                                $resPagoTotal = $colegiadoDeudaAnualLogic->obtenerPagoTotalVigentePorIdColegiado($idColegiado, $periodoActual);
                                if ($resPagoTotal['estado']) {
                                    $pagoTotal = $resPagoTotal['datos'];
                                    if ($pagoTotal['fechaVencimiento'] >= date('Y-m-d')) {
                                        $idPagoTotal = $pagoTotal['idColegiadoDeudaAnualTotal'];
                                        $importe = $pagoTotal['importe'];
                                    ?>
                                        <div class="col-md-4">
                                            <input class="form-check-input" name="generarRecibo[]" type="checkbox" value="<?php echo $idPagoTotal.'_'.$importe; ?>_PT" 
                                                   id="<?php echo $idPagoTotal ?>" onclick="cambiaTotalCuotas(<?php echo $importe ?>, <?php echo $totalDeudaActualizada; ?>, <?php echo $idPagoTotal ?>)">
                                            <label class="form-check-label" for="<?php echo $idPagoTotal ?>">
                                              <?php echo $periodoActual.' - Pago Total: $'.$importe ?>
                                            </label>
                                        </div>
                                        <div class="row">&nbsp;</div>
                                    <?php                                        
                                    }
                                }

                                //muestro cuotas impagas
                                foreach ($resDeuda['datos'] as $row) {
                                    if ($conRecargo == 'SI') {                                    
                                        $importe = $row['importeActualizado'];
                                    } else {
                                        $importe = $row['importeUno'];
                                    }
                                    $idColegiadoDeudaAnualCuota = $row['idColegiadoDeudaAnualCuota'];
                                    $periodo = $row['periodo'];
                                    $cuota = $row['cuota'];
                                    $fechaVencimiento = $row['fechaVencimiento'];

                                    $vencimientoTrimestre = NULL;
                                    if (CHEQUERA_TRIMESTRAL) {
                                        $fechaVencimientoTrimestre = date('Y-m-d');
                                        if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_UNO) {
                                            $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_UNO;
                                        } else {
                                            if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_DOS) {
                                                $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_DOS;
                                            } else {
                                                if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_TRES) {
                                                    $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_TRES;
                                                } else {
                                                    if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_CUATRO) {
                                                        $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_CUATRO;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($vencimientoTrimestre < $fechaVencimiento) { continue; }
                                    if ($fechaVencimiento > date('Y-m-d')) {
                                        $checked = '';
                                    } else {
                                        $checked = 'checked="checked"';
                                        $totalDeudaActualizada += $importe;
                                    }
                                ?>
                                <div class="col-md-2">
                                    <input class="form-check-input" name="generarRecibo[]" type="checkbox" <?php echo $checked; ?> value="<?php echo $idColegiadoDeudaAnualCuota.'_'.$importe; ?>" 
                                           id="<?php echo $idColegiadoDeudaAnualCuota ?>" onclick="cambiaTotalCuotas(<?php echo $importe ?>, <?php echo $totalDeudaActualizada; ?>, <?php echo $idColegiadoDeudaAnualCuota ?>)">
                                    <label class="form-check-label" for="<?php echo $idColegiadoDeudaAnualCuota ?>">
                                      <?php echo $periodo.'-'.$cuota.': $'.$importe ?>
                                    </label>
                                </div>
                                <?php
                                }
                                ?>
                            </div>
                        <?php
                        }

                        if ($totalDeudaPP > 0) {
                        ?>
                            <div class="row">&nbsp;</div>
                            <div class="col-md-12"><h4><b class="text-center">Deuda de Plan de Pagos  &nbsp;</b></h4></div>
                            <div class="form-check">
                                <?php
                                foreach ($resDeudaPP['datos'] as $row) {
                                    $idPlanPagosCuotas = $row['idPlanPagosCuotas'];
                                    $idPlanPagos = $row['idPlanPagos'];
                                    $cuota = $row['cuota'];
                                    if ($conRecargo == 'SI') {                                    
                                        $importe = $row['importeActualizado'];
                                    } else {
                                        $importe = $row['importe'];
                                    }
                                    $fechaVencimiento = $row['vencimiento'];
                                    if ($fechaVencimiento > date('Y-m-d')) {
                                        $checked = '';
                                    } else {
                                        $checked = 'checked="checked"';
                                        $totalDeudaActualizada += $importe;
                                    }
                                    ?>
                                    <div class="col-md-2">
                                        <input class="form-check-input" name="generarReciboPP[]" type="checkbox" <?php echo $checked; ?> value="<?php echo $idPlanPagosCuotas ?>" 
                                               id="<?php echo $idPlanPagosCuotas ?>"  onclick="cambiaTotalCuotas(<?php echo $importe ?>, <?php echo $totalDeudaActualizada; ?>, <?php echo $idPlanPagosCuotas ?>)">
                                        <label class="form-check-label" for="<?php echo $idPlanPagosCuotas ?>">
                                          <?php echo $cuota.' vto: '.cambiarFechaFormatoParaMostrar($fechaVencimiento).' - Importe: $'.$importe ?>
                                        </label>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        <?php 
                        }
                        ?>
                    </div>
                    <div class="row">
                    <div class="col-md-4">
                        <h4>Total a pagar: 
                            <b><input type="text" name="totalActualizado" id="totalActualizado" value="<?php echo $totalDeudaActualizada; ?>" readonly=""></b>
                        </h4>
                    </div>
                    </div>
                    <?php 
                    }
                    ?>
                    <?php
                    include 'cajadiaria_forma_pago.php'; 
                    ?>   
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center" id="bloque_confirmar" style="display: none;">
                        <?php
                        if ($totalDeuda > 0 || $totalDeudaPP > 0) {
                        ?>
                        <h4 id="informe" style="display: none; color: #F8BB00; ">El recibo se generó con éxito.</h4>
                        <button type="submit" name='confirma' id='confirma' class="btn btn-success" onclick="show('confirma', 'informe')">Confirma Recibo</button>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        <input type="hidden" name="tipoRecibo" id="tipoRecibo" value="CUOTAS" />
                        <input type="hidden" name="conRecargo" id="conRecargo" value="<?php echo $conRecargo; ?>" />
                        <?php 
                        } else {
                        ?>
                            <h4 class="alert alert-warning">No registra deuda para generar Recibo.</h4>
                        <?php
                        }
                        ?>
                    </div>
                </div>    
            </form>
        </div>
    </div>
    <?php
    } else {
        //debe seleccionar al colegiado    
    ?>
        <div class="row">
            <div class="col-md-12 text-center">
                <h4>Generar recibo de Colegiación</h4>
                <h5>Seleccione al colegiado/a</h5>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <?php 
        $link_form_origen = 'cajadiaria_colegiacion_recibo.php';
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
/*
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php',
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
</script>    
*/
?><script language="JavaScript">
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
        document.getElementById('totalConRecargo').value = valor;
        
        return valor;
    }

</script>