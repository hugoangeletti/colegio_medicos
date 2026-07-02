<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoSancionLogic.php');
$colegiadoSancionLogic = new colegiadoSancionLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaSanciones').DataTable({
                    "iDisplayLength":10,
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
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Sanciones";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $muestraMenuCompleto = TRUE;
        include 'menuColegiado.php';
        ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                <label>- Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-3"><h4><b>Sanciones disciplinarias</b></h4></div>
            <div class="col-md-3 text-right">
                <form method="POST" action="secretaria_sanciones_form.php?idColegiado=<?php echo $idColegiado; ?>">
                    <button type="submit" class="btn btn-success btn-lg">Nueva Sanción</button>
                    <input type="hidden" id="accion" name="accion" value="1">
                </form>
            </div>
        </div>
        <?php
        //busco las especialidades
        $resSanciones = $colegiadoSancionLogic->obtenerSancionesPorIdColegiado($idColegiado);
        if ($resSanciones['estado']){
        ?>
            <div class="row">
                <div class="col-md-12">
                <table  id="tablaSanciones" class="display">
                    <thead>
                        <tr>
                            <th style="display: none;">Id</th>
                            <th style="text-align: center;">Ley</th>
                            <th style="text-align: center;">Fecha Desde</th>
                            <th style="text-align: center;">Fecha Hasta</th>
                            <th style="text-align: center;">Art&iacute;culo</th>
                            <th style="text-align: center;">Detalle</th>
                            <th style="text-align: center;">Distrito</th>
                            <th style="text-align: center;">Provincia</th>
                            <th style="text-align: center;">Estado</th>
                            <th style="text-align: center;"></th>
                            <th style="text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resSanciones['datos'] as $dato){
                            $idColegiadoSancion = $dato['idColegiadoSancion'];
                            $ley = $dato['ley'];
                            if (isset($dato['fechaDesde']) && $dato['fechaDesde'] <> '0000-00-00') {
                                $fechaDesde = cambiarFechaFormatoParaMostrar($dato['fechaDesde']);
                            } else {
                                $fechaDesde = NULL;
                            }
                            if (isset($dato['fechaHasta']) && $dato['fechaHasta'] <> '0000-00-00') {
                                $fechaHasta = cambiarFechaFormatoParaMostrar($dato['fechaHasta']);
                            } else {
                                $fechaHasta = NULL;
                            }
                            $articulo = $dato['articulo'];
                            $detalle = $dato['detalle'];
                            $distrito = $dato['distrito'];
                            $provincia = $dato['provincia'];
                            $estado = $dato['estado'];
                            $estadoDetalle = $dato['estadoDetalle'];
                            if ($estado == 'B') {
                                $estiloEstado = 'style="text-align: center; color: red;"';                                
                            } else {
                                $estiloEstado = 'style="text-align: center; color: green;"';
                            }
                            ?>
                            <tr>
                                <td style="display: none"><?php echo $idColegiadoSancion;?></td>
                                <td style="text-align: center;"><?php echo $ley;?></td>
                                <td style="text-align: center;"><?php echo $fechaDesde;?></td>
                                <td style="text-align: center;"><?php echo $fechaHasta;?></td>
                                <td style="text-align: center;"><?php echo $articulo;?></td>
                                <td style="text-align: center;"><?php echo $detalle;?></td>
                                <td style="text-align: center;"><?php echo $distrito;?></td>
                                <td style="text-align: center;"><?php echo $provincia;?></td>
                                <td <?php echo $estiloEstado; ?>><?php echo $estadoDetalle;?></td>
                                <td>
                                    <form method="POST" action="secretaria_sanciones_form.php?idColegiado=<?php echo $idColegiado; ?>">
                                        <button type="submit" class="btn btn-info">Editar</button>
                                        <input type="hidden" id="idColegiadoSancion" name="idColegiadoSancion" value="<?php echo $idColegiadoSancion ?>">
                                        <input type="hidden" id="accion" name="accion" value="3">
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="secretaria_sanciones_form.php?idColegiado=<?php echo $idColegiado; ?>">
                                        <button type="submit" class="btn btn-danger">Borrar</button>
                                        <input type="hidden" id="idColegiadoSancion" name="idColegiadoSancion" value="<?php echo $idColegiadoSancion ?>">
                                        <input type="hidden" id="accion" name="accion" value="2">
                                    </form>
                                </td>
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
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resSanciones['clase']; ?>" role="alert">
                <span class="<?php echo $resSanciones['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resSanciones['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
    } else {
    ?>
        <div class="row">&nbsp;</div>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php        
    }
}
require_once '../html/footer.php';
