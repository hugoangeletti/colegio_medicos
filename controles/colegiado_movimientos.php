<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaMovimientos').DataTable({
                    "iDisplayLength":8,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3,4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de Llamadas",
                                "sPdfOrientation": "portrait",
                                "sFileName": "ListadoDeLlamadas.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
    }
);
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $idColegiado = NULL;
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Movimientos matriculares</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">

<?php
if (isset($idColegiado)) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        //include 'menuColegiado.php';
    ?>
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
            </div>
            <div class="col-md-4 text-right"><b>Estado actual: <?php echo $colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']).$colegiado['movimientoCompleto']; ?></b></div>
        </div>
        <div class="row">&nbsp;</div>
        <?php
        //busco los movimientos matriculares
        $resMovimientos = $colegiadoMovimientoLogic->obtenerMovimientosPorIdColegiado($idColegiado);
        if ($resMovimientos['estado']){
        ?>
        <div class="row">
            <div class="col-md-12">
            <table  id="tablaMovimientos" class="display">
                <thead>
                    <tr>
                        <th style="text-align: center; display: none;">Id</th>
                        <th style="text-align: center;">Movimiento matricular</th>
                        <th style="text-align: center;">Fecha Desde</th>
                        <th style="text-align: center;">Fecha Hasta</th>
                        <th style="text-align: center;">Distrito de Cambio</th>
                        <th style="text-align: center;">Distrito de Origen</th>
                        <th style="text-align: center;">Patolog&iacute;a</th>
                        <th style="text-align: center;">Fecha Actualizaci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resMovimientos['datos'] as $dato){
                        $idColegiadoMovimiento = $dato['idColegiadoMovimiento'];
                        $idTipoMovimietno = $dato['idTipoMovimietno'];
                        $fechaDesde = cambiarFechaFormatoParaMostrar($dato['fechaDesde']);
                        $fechaHasta = cambiarFechaFormatoParaMostrar($dato['fechaHasta']);
                        $distritoCambio = $dato['distritoCambio'];
                        $distritoOrigen = $dato['distritoOrigen'];
                        $idPatologia = $dato['idPatologia'];
                        $detalleMovimiento = $dato['detalleMovimiento'];
                        $nombrePatologia = $dato['nombrePatologia'];
                        $fechaCarga = cambiarFechaFormatoParaMostrar($dato['fechaCarga']);
                        ?>
                        <tr>
                            <td style="display: none"><?php echo $idColegiadoMovimiento;?></td>
                            <td style="text-align: left;"><?php echo $detalleMovimiento;?></td>
                            <td style="text-align: center;"><?php echo $fechaDesde;?></td>
                            <td style="text-align: center;"><?php echo $fechaHasta;?></td>
                            <td style="text-align: center;"><?php echo $distritoCambio;?></td>
                            <td style="text-align: center;"><?php echo $distritoOrigen;?></td>
                            <td style="text-align: left;">
                                <?php 
                                if (isset($idPatologia) && $idPatologia > 0) {
                                ?>
                                    <a href="colegiado_movimientos_patologia.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoMovimiento; ?>"><?php echo $nombrePatologia;?></a>                    
                                <?php
                                } else {
                                    if ($idTipoMovimietno == 2 || $idTipoMovimietno == 14 || $idTipoMovimietno == 26) {
                                        /*
                                        <a href="colegiado_movimientos_patologia.php?idColegiado=<?php echo $idColegiado; ?>&id=1" class="btn btn-success" role="button">Agregar</a>                    
                                         * 
                                         */
                                    ?>
                                        <a href="colegiado_movimientos_patologia.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoMovimiento; ?>">Agregar</a>                    
                                    <?php
                                    } else {
                                        echo 'NO';
                                    }
                                }
                                ?>
                            </td>
                            <td style="text-align: center;"><?php echo $fechaCarga;?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    <?php
        } else {
        ?>
            <div class="<?php echo $resMovimientos['clase']; ?>" role="alert">
                <span class="<?php echo $resMovimientos['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resMovimientos['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
    } else {
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php        
    }
}
?>
    </div>
</div>
<br>
<?php
if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 43)) {
?>
    <a href="colegiado_nuevo_baja.php?idColegiado=<?php echo $idColegiado; ?>">Agregar movimientos matricula de baja</a>
<?php
}
require_once '../html/footer.php';
