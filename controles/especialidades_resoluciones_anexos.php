<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaColegiacion').DataTable({
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
if (isset($_GET['idResolucion']) && $_GET['idResolucion'] <> "" && $_GET['estado'] && $_GET['anio']) {
    $idResolucion = $_GET['idResolucion'];
    $resResolucion = $resolucionesLogic->obtenerResolucionPorId($idResolucion);
    if ($resResolucion['estado']) {
        $resolucion = $resResolucion['datos'];
    } else {
        $continua = FALSE;
    }
    $estadoResoluciones = $_GET['estado'];
    $anioResoluciones = $_GET['anio'];
} else {
    $continua = FALSE;
}
if ($continua) {
?>
    <div class="panel panel-info">
    <div class="panel-heading"><h4><b>ANEXOS Resolución: <?php echo $resolucion['numero'].' con fecha '.cambiarFechaFormatoParaMostrar($resolucion['fecha']); ?></b></h4></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-9">&nbsp;</div>
                <div class="col-md-3">
                    <?php
                    if ($resolucion['estado'] != 'C') {
                    ?>
                    <form method="POST" action="especialidades_resoluciones_anexos_form.php">
                        <div align="right">
                            <button type="submit" class="btn btn-primary">Nuevo ANEXO</button>
                            <input type="hidden" id="accion" name="accion" value="1">
                            <input type="hidden" id="idResolucion" name="idResolucion" value="<?php echo $idResolucion; ?>">
                            <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="<?php echo $estadoResoluciones; ?>">
                            <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
                        </div>
                    </form>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php
            $resAnexos = $resolucionesLogic->obtenerAnexosResolucion($idResolucion);
            if ($resAnexos['estado']) {
            ?>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <table  id="tablaColegiacion" class="display">
                            <thead>
                                <tr>
                                    <th style="display: none;">Id</th>
                                    <th>Observacion</th>
                                    <th style="text-align: center;">Estado</th>
                                    <th style="text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resAnexos['datos'] as $dato){
                                    $idAnexo = $dato['idResolucionAnexo'];
                                    $observacion = $dato['observacion'];
                                    $borrado = $dato['borrado'];
                                    switch ($borrado) {
                                        case '1':
                                            $borradoLeyenda = "Eliminado";
                                            break;
                                        
                                        case '0':
                                            $borradoLeyenda = "Activo";
                                            break;
                                        
                                        default:
                                            $borradoLeyenda = "";
                                            break;
                                    }
                                    ?>
                                    <tr>
                                        <td style="display: none"><?php echo $idResolucionAnexo;?></td>
                                        <td><?php echo $observacion;?></td>
                                        <td style="text-align: center;"><?php echo $borradoLeyenda;?></td>
                                        <td>
                                            <?php
                                            if ($resolucion['estado'] != 'C') {
                                            ?>
                                            <form method="POST" action="especialidades_resoluciones_anexos_form.php">
                                                <button type="submit" class="btn btn-info">Editar</button>
                                                <input type="hidden" id="accion" name="accion" value="3">
                                                <input type="hidden" id="idResolucion" name="idResolucion" value="<?php echo $idResolucion; ?>">
                                                <input type="hidden" id="idAnexo" name="idAnexo" value="<?php echo $idAnexo; ?>">
                                                <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="<?php echo $estadoResoluciones; ?>">
                                                <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
                                            </form>
                                            <?php
                                            }
                                            ?>
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
                <div class="<?php echo $resAnexos['clase']; ?>" role="alert">
                    <span class="<?php echo $resAnexos['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resAnexos['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
            ?>
        </div>
    </div>
<?php
} else {
?>
    <h3>Ingreso incorrecto</h3>
<?php
}
?>
    <!-- BOTON VOLVER -->    
    <div class="col-md-12">
        <form  method="POST" action="especialidades_resoluciones.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
            <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="<?php echo $estadoResoluciones; ?>">
            <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
       </form>
    </div>
    <br>
<?php
require_once '../html/footer.php';
