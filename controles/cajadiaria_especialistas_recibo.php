<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 1, "desc" ]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'                    
                });
    }
);
</script>
<?php
$continua = TRUE;
$resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
if ($resCajaDiaria['estado']) {
    $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
} else {
    $continua = FALSE;
}

if ($continua) {
    if (isset($_POST['listaIdMesaEntrada'])) {
        $listaIdMesaEntrada = $_POST['listaIdMesaEntrada'];
    } else {
        $listaIdMesaEntrada = NULL;
    }

    if (isset($listaIdMesaEntrada)) {
        $idsMesaEntrada = explode(',', $listaIdMesaEntrada);
        $idColegiado = $_POST['idColegiado'];
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
            $resDeuda = $mesaEntradaEspecialistaLogic->obtenerMesaEntradaEspecialistasAPagar($listaIdMesaEntrada);
            if ($resDeuda['estado']) {
                //inicializo los totales
                foreach ($resDeuda['datos'] as $row) {
                    $totalDeuda += $row['importe'];
                }
            }
        }
        ?>

    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Cobranza expedientes de especialistas</h4>
                </div>
                <div class="col-md-3 text-left">
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria_especialistas_listado.php?id=<?php echo $idCajaDiaria; ?>">
                        <button type="submit"  class="btn btn-info" >Volver </button>
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
            <div class="col-md-12 text-center"><h4><b>Generar recibo de pago</b></h4></div>
        </div>
        <form id="datosPlanPagos" autocomplete="off" name="datosRecibo" method="POST" action="datosCajaDiaria\generar_recibo.php">
            <?php
            if ($totalDeuda > 0) {
                //<div class="row">&nbsp;</div>
            ?>
                <div class="row">
                    <div class="form-check col-md-12">
                        <h4><b class="text-center">Expedientes a cobrar &nbsp;</b></h4>
                        <table class="table table-hover" border="true">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Fecha Ingreso</th>
                                    <th>Especialidad</th>
                                    <th>Tipo</th>
                                    <th>Importe</th>
                                    <th>Abonar</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php
                        $totalActualizado = 0;
                        foreach ($resDeuda['datos'] as $row) {
                            $idMesaEntrada = $row['idMesaEntrada'];
                            $idCheckbox = "check_exp_" . $idMesaEntrada;
                            $especialidad = $row['especialidad'];
                            $nombreTipoEspecialidad = $row['nombreTipoEspecialidad'];
                            $importe = $row['importe'];
                            $fecha = cambiarFechaFormatoParaMostrar($row['fechaIngreso']);
                            $incisoArticulo8 = $row['incisoArticulo8'];
                            $numeroExpediente = $row['numeroExpediente'];
                            $anioExpediente = $row['anioExpediente'];
                            $totalActualizado += $importe;
                            ?>
                            <tr>
                                <td><?php echo $numeroExpediente.'/'.$anioExpediente; ?></td>
                                <td><?php echo $fecha; ?></td>
                                <td><?php echo $especialidad; ?></td>
                                <td><?php echo $nombreTipoEspecialidad; if (!empty($row['incisoArticulo8'])) { echo " Inciso ".$row['incisoArticulo8']; } ?></td>
                                <td><?php echo $importe; ?></td>
                                <td>
                                    <input type="checkbox" 
                                   name="generarRecibo[]" 
                                   id="<?php echo $idCheckbox; ?>" 
                                   checked="checked" 
                                   value="<?php echo $idMesaEntrada ?>"
                                   onclick="cambiaTotalCuotas('<?php echo $importe; ?>', $('#totalActualizado').val(), '<?php echo $idCheckbox; ?>')">
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="col-md-6">
                        <h4>Total a pagar: 
                            <b><input type="text" name="totalActualizado" id="totalActualizado" value="<?php echo $totalActualizado; ?>" readonly=""></b>
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
                <div class="col-md-12 text-center" id="bloque_confirmar" >
                    <?php
                    if ($totalDeuda > 0) {
                    ?>
                        <button type="submit" name='confirma' id='confirma' class="btn btn-success" >Confirma Recibo</button>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        <input type="hidden" name="listaIdMesaEntrada" id="listaIdMesaEntrada" value="<?php echo $listaIdMesaEntrada; ?>" />
                        <input type="hidden" name="tipoRecibo" id="tipoRecibo" value="ESPECIALISTAS" />
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
            <div class="row">&nbsp;</div>
        </div>
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
        document.getElementById('totalConRecargo').value = valor;

        return valor;
    }

</script>