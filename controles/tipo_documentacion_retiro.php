<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/retiroDocumentacionLogic.php');
$retiroDocumentacionLogic = new retiroDocumentacionLogic();
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
<div class="panel panel-info">
<div class="panel-heading"><h4><b>Tipo de documentación para retiros</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-9">&nbsp;</div>
            <div class="col-md-3">
                <form method="POST" action="tipo_documentacion_retiro_form.php">
                    <div align="right">
                        <button type="submit" class="btn btn-primary">Nuevo tipo</button>
                        <input type="hidden" id="accion" name="accion" value="1">
                    </div>
                </form>
            </div>
        </div>
        <?php
        $resTipo = $retiroDocumentacionLogic->obtenerTipoDocumentacionRetiro();
        if ($resTipo['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Nombre</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resTipo['datos'] as $dato){
                                $idTipoDocumentacionRetiro = $dato['id'];
                                $nombre = $dato['nombre'];
                                ?>
                                <tr>
                                    <td><?php echo $idTipoDocumentacionRetiro;?></td>
                                    <td><?php echo $nombre;?></td>
                                    <td>
                                        <form method="POST" action="tipo_documentacion_retiro_form.php">
                                            <button type="submit" class="btn btn-info">Editar</button>
                                            <input type="hidden" id="accion" name="accion" value="3">
                                            <input type="hidden" id="idTipoDocumentacionRetiro" name="idTipoDocumentacionRetiro" value="<?php echo $idTipoDocumentacionRetiro; ?>">
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
            <div class="<?php echo $resTipo['clase']; ?>" role="alert">
                <span class="<?php echo $resTipo['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resTipo['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
</div>
<?php
require_once '../html/footer.php';
