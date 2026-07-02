<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/planPagosLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
?>
<script>
function deshabilitarCantidadCuotas(nombreRadio)
{
    switch(nombreRadio)
    {
        case '3':
            document.getElementById('numeroTarjeta').disabled=false;
            document.getElementById('numeroDocumento').disabled=false;
            document.getElementById('numeroCbu').disabled=true;
            document.getElementById('tipoCuenta3').disabled=true;
            document.getElementById('tipoCuenta4').disabled=true;
            break;

        case '6':
            document.getElementById('numeroTarjeta').disabled=true;
            document.getElementById('numeroDocumento').disabled=true;
            document.getElementById('numeroCbu').disabled=false;
            document.getElementById('tipoCuenta3').disabled=false;
            document.getElementById('tipoCuenta4').disabled=false;
            break;
            
        case '12':
            document.getElementById('numeroTarjeta').disabled=true;
            document.getElementById('numeroDocumento').disabled=true;
            document.getElementById('numeroCbu').disabled=false;
            document.getElementById('tipoCuenta3').disabled=false;
            document.getElementById('tipoCuenta4').disabled=false;
            break;
            
        default :
            document.getElementById('numeroTarjeta').disabled=true;
            document.getElementById('numeroDocumento').disabled=true;
            document.getElementById('numeroCbu').disabled=true;
            document.getElementById('tipoCuenta3').disabled=true;
            document.getElementById('tipoCuenta4').disabled=true;
            break;
    }
     
}
  
</script>
<?php
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
        //obtengo la deuda, inicializo los campos a mostrar
        $totalDeuda = 0;
        $totalDeudaActualizada = 0;
        $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualAPagar($idColegiado);
        if ($resDeuda['estado']) {
            //inicializo los totales
            $periodoAnterior = 0;
            $arrayPeriodos = array();
            foreach ($resDeuda['datos'] as $row) {
                $periodo = $row['periodo'];
                if ($periodo <> $periodoActual) {
                    if ($row['periodo'] <> $periodoAnterior) {
                        if ($periodoAnterior <> 0) {
                            $lineaPeriodo = array('Periodo' => $periodoAnterior, 'Cuotas' => $cuota, 'Importe' => $totalPeriodo, 'Actualizado' => $totalPeriodoActualizado);
                            array_push($arrayPeriodos, $lineaPeriodo);
                        }
                        $periodoAnterior = $periodo;
                        $totalPeriodo = 0;
                        $totalPeriodoActualizado = 0;
                        $cuota = '';
                    }
                    $cuota .= $row['cuota'].'-';
                    $totalPeriodo += $row['importeUno'];
                    $totalPeriodoActualizado += $row['importeActualizado'];

                    $totalDeuda += $row['importeUno'];
                    $totalDeudaActualizada += $row['importeActualizado'];
                    
                }
            }
            if ($periodoAnterior <> 0) {
                $lineaPeriodo = array('Periodo' => $periodoAnterior, 'Cuotas' => $cuota, 'Importe' => $totalPeriodo, 'Actualizado' => $totalPeriodoActualizado);
                array_push($arrayPeriodos, $lineaPeriodo);
            }
        }
        
        //obtengo deuda por plan de pago anterior
        /*
            loop
                next(paraconsulta)
                if errorcode() then break.
                PPCuo:Importe = ParC:c1
                PPCuo:Vencimiento = date(sub(ParC:c2,6,2),sub(ParC:c2,9,2),sub(ParC:c2,1,4))
                Loc:Recargo = 0
                if Loc:CondonarInteresDeuda = 'N' then
                    if (PPCuo:Vencimiento < Glo:Hoy) then
                        Loc:Recargo = CalculaRecargoPlanPago(PPCuo:Vencimiento,PPCuo:Importe)
                    end
                end
                PPag:RecargoFinanciero += Loc:Recargo
                PPag:ImporteOtroPP += (PPCuo:Importe + Loc:Recargo)
            end
            PPag:ImporteTotal += PPag:ImporteOtroPP
        */
        $deudaPlanPagosOriginal = 0;
        $deudaPlanPagosActualizado = 0;
        $resDeudaPP = $colegiadoPlanPagoLogic->obtenerDeudaPlanPagosPorIdColegiado($idColegiado);
        if ($resDeudaPP['estado']) {
            foreach ($resDeudaPP['datos'] as $row) {
                $deudaPlanPagosOriginal += $row['importe'];
                $deudaPlanPagosActualizado += $row['importeActualizado'];
            }
        }
    }
    ?>

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Generación de Plan de Pagos</h4>
            </div>
            <div class="col-md-3 text-left">
                <?php 
                if (isset($_POST['origen']) && $_POST['origen'] == 'nuevo') {
                    //accede desde la lista de planes de pagos, entonces debe volver al listado
                ?>
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_planesdepago.php">
                        <button type="submit"  class="btn btn-info" >Volver a Planes de Pago</button>
                    </form>
                <?php
                } else {
                    //sino, fue llamado desde el colegiado, debe volver a los datos de tesoreria
                ?>
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_tesoreria.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-info" >Volver a Datos de Tesorería</button>
                    </form>
                <?php
                }
                ?>
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
        <div class="col-md-12 text-center"><h4><b>Nuevo Plan de Pagos</b></h4></div>
    </div>
    <div class="row">&nbsp;</div>
    <form id="datosPlanPagos" autocomplete="off" name="datosPlanPagos" method="POST" target="_BLANK" onSubmit="" action="datosColegiadoPlanPagos\generar_plan_pagos.php">
        <div class="row">
            <div class="col-md-7">
                <?php
                if ($totalDeuda > 0) {
                ?>
                <div class="col-md-12 text-center">
                    <div class="col-md-4">
                        <label>Períodos y cuotas adeudadas</label>  
                    </div>
                    <div class="col-md-3">
                        <label>Importe Original</label>  
                    </div>
                    <div class="col-md-2">
                        <label>Actualizado</label>  
                    </div>
                    <div class="col-md-3">
                        <label>Sin punitorios</label>  
                    </div>
                    <?php
                    foreach ($arrayPeriodos as $key => $value) {
                        $periodo = $value['Periodo'];
                    ?>
                        <div class="col-md-4">
                            <p><?php echo $value['Periodo'].' - '.$value['Cuotas']; ?></p>  
                        </div>
                        <div class="col-md-3">
                            <p><?php echo number_format($value['Importe'], 2, ',', '.'); ?></p>  
                        </div>
                        <div class="col-md-2">
                            <p><?php echo number_format($value['Actualizado'], 2, ',', '.'); ?></p>  
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" name="<?php echo $periodo; ?>" id="<?php echo $periodo; ?>" onclick="cambiaTotal(<?php echo $periodo ?>, <?php echo $value['Actualizado']-$value['Importe'] ?>)" value="<?php echo $periodo; ?>">
                        </div>
                    <?php
                    }
                ?>
                </div>
                <?php
                }
                if ($deudaPlanPagosOriginal > 0) {
                ?>
                    <div class="col-md-12">
                        <label>Total Plan de pagos adeudado: &nbsp;</label><?php echo number_format($deudaPlanPagosActualizado, 2, ',', '.'); ?>
                    </div>
                <?php
                    $totalDeuda += $deudaPlanPagosActualizado;
                    $totalDeudaActualizada += $deudaPlanPagosActualizado;
                }
                
                //calcula el total a financiar y el valor de la cuota
                $totalPlanPagos = round($totalDeudaActualizada * ((((5/12) * 3) / 100) + 1), 0);
                $valorCuota = round($totalPlanPagos / 6, 0);
                ?>
            </div>
            <?php 
            if ($totalDeuda > 0) {
            ?>
            <div class="col-md-5">
                <div class="col-md-6">
                    <h4>Total deuda original: 
                        <b><input type="text" name="totalOriginal" id="totalOriginal" value="<?php echo $totalDeuda; ?>" readonly=""></b>
                    </h4>  
                </div>
                <div class="col-md-6">
                    <h4>Total deuda actualizada: 
                        <b><input type="text" name="totalActualizado" id="totalActualizado" value="<?php echo $totalDeudaActualizada; ?>" readonly=""></b>
                    </h4>
                </div>
                <div class="col-md-12">
                    <h4>Cuotas (hasta 6 cuotas 5% - hasta 12 cuotas 10% anual)
                         </h4>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="2" onclick="cambiaTotalCuotas(2, <?php echo $totalDeudaActualizada; ?>)">2</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="3" onclick="cambiaTotalCuotas(3, <?php echo $totalDeudaActualizada; ?>)">3</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="4" onclick="cambiaTotalCuotas(4, <?php echo $totalDeudaActualizada; ?>)">4</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="5" onclick="cambiaTotalCuotas(5, <?php echo $totalDeudaActualizada; ?>)">5</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="6" checked="" onclick="cambiaTotalCuotas(6, <?php echo $totalDeudaActualizada; ?>)">6</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="7" onclick="cambiaTotalCuotas(7, <?php echo $totalDeudaActualizada; ?>)">7</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="8" onclick="cambiaTotalCuotas(8, <?php echo $totalDeudaActualizada; ?>)">8</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="9" onclick="cambiaTotalCuotas(9, <?php echo $totalDeudaActualizada; ?>)">9</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="10" onclick="cambiaTotalCuotas(10, <?php echo $totalDeudaActualizada; ?>)">10</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="11" onclick="cambiaTotalCuotas(11, <?php echo $totalDeudaActualizada; ?>)">11</label>
                    </div>
                    <div class="radio-inline" >
                        <label><input type="radio" name="cuotas" id="cuotas" value="12" onclick="cambiaTotalCuotas(12, <?php echo $totalDeudaActualizada; ?>)">12</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <h4>Total a financiar: &nbsp;&nbsp;
                        <b><input type="text" name="totalFinanciar" id="totalFinanciar" value="<?php echo $totalPlanPagos; ?>" readonly=""></b>
                    </h4>
                </div>
                <div class="col-md-12">
                    <h4>Valor de la cuota: &nbsp;
                        <b><input type="text" name="valorCuota" id="valorCuota" value="<?php echo $valorCuota; ?>" readonly=""></b>
                    </h4>
                </div>
            </div>
            <?php 
            }
            ?>
        </div>
        
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?php
                if ($totalDeuda > 0) {
                ?>
                <h4 id="informe" style="display: none; color: #F8BB00; ">El Plan de Pagos se generó con éxito.</h4>
                <button type="submit" name='confirma' id='confirma' class="btn btn-success" onclick="show('confirma', 'informe')">Confirma Plan de Pagos</button>
                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                <input type="hidden" name="deudaPlanPago" id="deudaPlanPago" value="<?php echo $deudaPlanPagosActualizado; ?>" />
                <?php 
                } else {
                ?>
                    <h4 class="alert alert-warning">No registra deuda para generar Plan de Pagos.</h4>
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
            <h4>Nuevo Plan de Pagos</h4>
            <h5>Seleccione al colegiado/a</h5>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_planesdepago_nuevo.php">
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
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_planesdepago.php">
            <button type="submit"  class="btn btn-info" >Volver a Planes de Pago</button>
        </form>
    </div>
<?php
}
require_once '../html/footer.php';
?>
<script type="text/javascript">
    function show(bloq1, bloq2) {
	 obj = document.getElementById(bloq1);
	 obj.style.display = (obj.style.display=='none') ? 'block' : 'none';
         
	 obj2 = document.getElementById(bloq2);
	 obj2.style.display = (obj2.style.display=='none') ? 'block' : 'none';
    }
    
    function cambiaTotal(periodo, descuento){
        var actualizado = parseInt(document.getElementById('totalActualizado').value);
        //var total = document.getElementById('totalFinanciar').value;
        //var valor = parseInt(total);
        if (document.getElementById(periodo).checked)
        {
            actualizado -= descuento;
        } else {
            actualizado += descuento;
        }
        
        var cuotas = parseInt(capturarCuota());
        var interes = parseInt(5);
        if (cuotas > 6) {
            interes = parseInt(10);
        }
        var recargo = ((interes/12) * cuotas / 100) + 1;
        var valor = Math.round((actualizado * recargo));
        var valorCuota = Math.round(valor / cuotas, 0); 
        document.getElementById('valorCuota').value = valorCuota;
        
        document.getElementById('totalFinanciar').value = valor;
        document.getElementById('totalActualizado').value = actualizado;
    }

    function cambiaTotalCuotas(cuotas, totalActualizado){
        var totalActualizado = parseInt(document.getElementById('totalActualizado').value);
        var valor = parseInt(totalActualizado);
        var cuotas = parseInt(cuotas);
        
        var interes = parseInt(5);
        if (cuotas > 6) {
            interes = parseInt(10);
        }
        var recargo = ((interes/12) * cuotas / 100) + 1;
        var valor = Math.round((totalActualizado * recargo));
        var valorCuota = Math.round(valor / cuotas, 0); 

        document.getElementById('valorCuota').value = valorCuota;
        document.getElementById('totalFinanciar').value = valor;
        
        return valorCuota;
    }
    
    function capturarCuota()
    {
        var resultado = 3;
 
        var porNombre=document.getElementsByName("cuotas");
        // Recorremos todos los valores del radio button para encontrar el
        // seleccionado
        for(var i=0;i<porNombre.length;i++)
        {
            if(porNombre[i].checked)
                resultado=porNombre[i].value;
        }
 
        return resultado;
    }

</script>
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