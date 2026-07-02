<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
?>
<script>
    $(document).ready(
        function () {
                    $('#tablaMovimientos').DataTable({
                        "iDisplayLength":10,
                        "order": [[ 0, "desc" ], [ 1, "asc"]],
                        "language": {
                            "url": "../public/lang/esp.lang"
                        },
                        "bLengthChange": false,
                        "bFilter": false,
                        //dom: 'T<"clear">lfrtip'
                    });
        }
    );
    $(document).ready(function()
    {
       $("#myModal").modal("show");
    });
</script>
<?php
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
require_once ('../dataAccess/colegiadoArchivoLogic.php');
require_once ('../dataAccess/tipoMovimientoLogic.php');
$tipoMovimientoLogic = new tipoMovimientoLogic();

$continua = TRUE;
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Alta";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $panel = "panel-primary";
    $titulo = "Carga de Matrícula dada de baja";
    $botonConfirma = "btn-primary";
    $display = 'none';
    
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        
        if (isset($_POST['mensaje'])) {
            ?>
            <div class="ocultarMensaje"> 
                <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
            </div>
            <?php
        } 
        if (isset($_POST['idTipoMovimiento'])) {
            $idTipoMovimiento = $_POST['idTipoMovimiento'];
        } else {
            $idTipoMovimiento = "";            
        }
        if (isset($_POST['distritoOrigen'])) {
            $distritoOrigen = $_POST['distritoOrigen'];
        } else {
            $distritoOrigen = "";
        }
        if (isset($_POST['fechaDesde'])) {
            $fechaDesde = $_POST['fechaDesde'];
        } else {
            $fechaDesde = "";
        }
        if (isset($_POST['fechaHasta'])) {
            $fechaHasta = $_POST['fechaHasta'];
        } else {
            $fechaHasta = "";
        }
        ?>
        <div class="panel <?php echo $panel; ?>">
            <div class="panel-heading">
                <h4><?php echo $titulo; ?></h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        Apellido y Nombres
                        <b><input class="form-control" type="text" value="<?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-2">
                        Matr&iacute;cula
                        <b><input class="form-control" type="text" value="<?php echo $colegiado['matricula']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-7">
                        &nbsp;
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">&nbsp;</div>
                        <h4><b>Movimientos matriculares</b></h4>
                        <div class="row">
                            <form autocomplete="off" method="POST" onSubmit="" action="datosColegiadoMovimiento/altaMovimiento.php?idColegiado=<?php echo $idColegiado; ?>">
                                <div class="col-md-4">
                                    <label>Tipo de ingreso *</label>
                                    <select class="form-control" id="idTipoMovimiento" name="idTipoMovimiento" required="">
                                        <option value="" selected>Seleccione Tipo de Movimiento</option>
                                        <?php
                                        $resTipoMovimiento = $tipoMovimientoLogic->obtenerTipoMovimiento();
                                        if ($resTipoMovimiento['estado']) {
                                            foreach ($resTipoMovimiento['datos'] as $row) {
                                            ?>
                                            <option value="<?php echo $row['id']; ?>" <?php if($idTipoMovimiento == $row['id']) { ?> selected <?php } ?>><?php echo $row['detalleCompleto']; ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Distrito de Cambio</label>
                                    <select class="form-control" id="distritoOrigen" name="distritoOrigen">
                                        <option value="" selected>Seleccione Distrito</option>
                                        <option value="2" <?php if($distritoOrigen == '2') { ?> selected <?php } ?>>Distrito 2</option>
                                        <option value="3" <?php if($distritoOrigen == '3') { ?> selected <?php } ?>>Distrito 3</option>
                                        <option value="4" <?php if($distritoOrigen == '4') { ?> selected <?php } ?>>Distrito 4</option>
                                        <option value="5" <?php if($distritoOrigen == '5') { ?> selected <?php } ?>>Distrito 5</option>
                                        <option value="6" <?php if($distritoOrigen == '6') { ?> selected <?php } ?>>Distrito 6</option>
                                        <option value="7" <?php if($distritoOrigen == '7') { ?> selected <?php } ?>>Distrito 7</option>
                                        <option value="8" <?php if($distritoOrigen == '8') { ?> selected <?php } ?>>Distrito 8</option>
                                        <option value="9" <?php if($distritoOrigen == '9') { ?> selected <?php } ?>>Distrito 9</option>
                                        <option value="10" <?php if($distritoOrigen == '10') { ?> selected <?php } ?>>Distrito 10</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Fecha desde *</label>
                                    <input class="form-control" type="date" name="fechaDesde" value="<?php echo $fechaDesde; ?>" required=""/>
                                </div>
                                <div class="col-md-2">
                                    <label>Fecha hasta</label>
                                    <input class="form-control" type="date" name="fechaHasta" value="<?php echo $fechaHasta; ?>"/>
                                </div>
                                <div class="col-md-2 text-right">
                                    <br>
                                    <button type="submit"  class="btn btn-default" >Confirma movimiento </button>
                                </div>
                            </form>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <?php
                            $resColegiadoMov = $colegiadoMovimientoLogic->obtenerMovimientosPorIdColegiado($idColegiado);
                            if ($resColegiadoMov['estado']) {
                            ?>
                                <div class="col-md-12">
                                    <table  id="tablaMovimientos" class="display">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center; display: none;">Id</th>
                                                <th>Tipo movimiento</th>
                                                <th style="text-align: center;">Fecha Desde</th>
                                                <th style="text-align: center;">Fecha Hasta</th>
                                                <th>Distrito de cambio</th>
                                                <th>Acci&oacute;n</th>
                                            </tr>
                                        </thead>
                                    <tbody>
                                        <?php
                                        foreach ($resColegiadoMov['datos'] as $dato){
                                            $idColegiadoMovimiento = $dato['idColegiadoMovimiento'];
                                            $detalleMovimiento= $dato['detalleMovimiento'];
                                            $fechaDesde = $dato['fechaDesde'];
                                            $fechaHasta = $dato['fechaHasta'];
                                            $distritoCambio = $dato['distritoCambio'];
                                            ?>
                                            <tr>
                                                <td style="display: none"><?php echo $idColegiadoMovimiento;?></td>
                                                <td><?php echo $detalleMovimiento; ?></td>
                                                <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaDesde);?></td>
                                                <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaHasta);?></td>
                                                <td><?php echo $distritoCambio; ?></td>
                                                <td>&nbsp;</td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <form autocomplete="off" method="POST" onSubmit="" action="datosColegiadoMovimiento/cierraCargaMovimientos.php?idColegiado=<?php echo $idColegiado; ?>">
                                <div class="col-md-12 text-right">
                                    <button type="submit"  class="btn <?php echo $botonConfirma; ?> btn-lg" >Finaliza carga de movimientos </button>
                                </div>
                            </form>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php
    }
}
require_once '../html/footer.php';
?>
<script type="text/javascript">
	function show(bloq) {
	 obj = document.getElementById(bloq);
	 obj.style.display = (obj.style.display=='none') ? 'block' : 'none';
}

</script>