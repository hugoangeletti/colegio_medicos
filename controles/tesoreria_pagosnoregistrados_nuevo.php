<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/condonacionLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
require_once ('../dataAccess/pagosNoRegistradosLogic.php');
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    if (isset($_POST['idColegiado'])) {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
    }
}

if (isset($idColegiado)) {
    $periodoActual = $_SESSION['periodoActual'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
    }
    
    if (isset($_POST['mensaje'])) {
    ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
     <?php
    } else {
        $idLugarPago = NULL;
        $fechaPago = date('Y-m-d');
        $observaciones = NULL;
        //obtengo la deuda, inicializo los campos a mostrar
        $totalDeuda = 0;
        $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualAPagar($idColegiado);
        if ($resDeuda['estado']) {
            //inicializo los totales
            foreach ($resDeuda['datos'] as $row) {
                $totalDeuda += $row['importeUno'];
            }
        }
        
        $totalDeudaPP = 0;
        $resDeudaPP = $colegiadoPlanPagoLogic->obtenerDeudaPlanPagosPorIdColegiado($idColegiado);
        if ($resDeudaPP['estado']) {
            foreach ($resDeudaPP['datos'] as $row) {
                $totalDeudaPP += $row['importe'];
            }
        }
    }
    ?>

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Pagos No Registrados</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_pagosnoregistrados.php?idColegiado=<?php echo $idColegiado; ?>">
                    <button type="submit"  class="btn btn-info" >Volver a Pagos No Registrados</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <div class="row">
        <div class="col-md-2">
            <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
        </div>
        <div class="col-md-4">
            <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
        </div>
        <div class="col-md-6">&nbsp;</div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center"><h4><b>Nuevos Pagos No Registrados</b></h4></div>
    </div>
    <div class="row">&nbsp;</div>
    <form id="datosPagosNoRegistrados" autocomplete="off" name="datosPagosNoRegistrados" method="POST" action="datosPagosNoRegistrados/generar_pagos_no_registrados.php?idColegiado=<?php echo $idColegiado; ?>">
        <?php
        if ($totalDeuda+$totalDeudaPP > 0) {
        ?>
            <div class="row">
                <div class="col-md-3">
                    <label>Lugar de Pago: * </label>  
                    <select class="form-control" id="idLugarPago" name="idLugarPago" required="">
                        <option value="">Seleccione Lugar de Pago</option>
                        <?php
                        $resLugares = $lugarPagoLogic->obtenerLugaresDePago();
                        if ($resLugares['estado']) {
                            foreach ($resLugares['datos'] as $row) {
                            ?>
                                <option value="<?php echo $row['id'] ?>" <?php if($idLugarPago == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                            <?php
                            }
                        } else {
                            echo $resLugares['mensaje'];
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Fecha de Pago: * </label>  
                    <input class="form-control" type="date" name="fechaPago" value="<?php echo $fechaPago; ?>" required=""/>
                </div>
                <div class="col-md-6">
                    <label>Observaciones: </label>  
                    <textarea class="form-control" name="detalle" id="detalle" rows="1" ><?php echo $observaciones; ?></textarea>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="form-check">
                    <?php
                    if ($totalDeuda > 0) {
                    ?>
                        <h4><b class="text-center">Deuda de Colegiación&nbsp;</b></h4>
                        <div class="col-md-3">
                            Período - Cuota - Importe - Nro de Recibo
                        </div>
                        <div class="col-md-3">
                            Período - Cuota - Importe - Nro de Recibo
                        </div>
                        <div class="col-md-3">
                            Período - Cuota - Importe - Nro de Recibo
                        </div>
                        <div class="col-md-3">
                            Período - Cuota - Importe - Nro de Recibo
                        </div>
                        <?php
                        foreach ($resDeuda['datos'] as $row) {
                            $idColegiadoDeudaAnualCuota = $row['idColegiadoDeudaAnualCuota'];
                            $periodo = $row['periodo'];
                            $cuota = $row['cuota'];
                            $importe = $row['importeUno'];
                        ?>
                        <div class="col-md-3">
                            <input class="form-check-input" name="lasCuotas[]" type="checkbox" value="<?php echo $idColegiadoDeudaAnualCuota ?>" 
                                   id="<?php echo $idColegiadoDeudaAnualCuota ?>">
                            <label class="form-check-label" for="<?php echo $idColegiadoDeudaAnualCuota ?>">
                              <?php echo $periodo.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                                      $cuota.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.
                                      $importe.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                                      $idColegiadoDeudaAnualCuota; ?>
                            </label>
                        </div>
                        <?php
                        }
                    }

                    if ($totalDeudaPP > 0) {
                    ?>
                        <div class="row">&nbsp;</div>
                        <h4><b class="text-center">Deuda de Plan de Pagos&nbsp;</b></h4>
                        <div class="col-md-3">
                            Nro.PP - Cuota - Importe - Nro de Recibo
                        </div>
                        <div class="col-md-3">
                            Nro.PP - Cuota - Importe - Nro de Recibo
                        </div>
                        <div class="col-md-3">
                            Nro.PP - Cuota - Importe - Nro de Recibo
                        </div>
                        <div class="col-md-3">
                            Nro.PP - Cuota - Importe - Nro de Recibo
                        </div>
                        <?php
                        foreach ($resDeudaPP['datos'] as $row) {
                            $idPlanPagosCuotas = $row['idPlanPagosCuotas'];
                            $idPlanPagos = $row['idPlanPagos'];
                            $cuota = $row['cuota'];
                            $importe = $row['importe'];
                        ?>
                        <div class="col-md-3">
                            <input class="form-check-input" name="lasCuotasPP[]" type="checkbox" value="<?php echo $idPlanPagosCuotas ?>" 
                                   id="<?php echo $idPlanPagosCuotas ?>">
                            <label class="form-check-label" for="<?php echo $idPlanPagosCuotas ?>">
                              <?php echo $idPlanPagos.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                                      $cuota.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$'.
                                      $importe.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                                      $idPlanPagosCuotas; ?>
                            </label>
                        </div>
                        <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php 
            }
            ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?php
                if ($totalDeuda > 0) {
                ?>
                    <button type="submit" name='confirma' id='confirma' class="btn btn-success" >Confirma Pagos No Registrados</button>
                <?php 
                } else {
                ?>
                    <h4 class="alert alert-warning">No registra deuda para aplicar pagos.</h4>
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
            <h4>Nueva Condonación</h4>
            <h5>Seleccione al colegiado/a</h5>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_condonacion_nueva.php">
            <div class="row">
                <div class="col-md-3" style="text-align: right;">
                    <label>Matr&iacute;cula o Apellido y Nombre *</label>
                </div>
                <div class="col-md-7">
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                    <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                </div>
                <div class="col-md-2">
                    <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                    <input type="hidden" name="origen" id="origen" value="nuevo" />
                </div>
            </div>
        </form>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row text-center">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_condonacion.php">
            <button type="submit"  class="btn btn-info" >Volver a Condonaciones</button>
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