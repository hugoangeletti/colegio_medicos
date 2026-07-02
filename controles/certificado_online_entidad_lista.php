<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();
require_once ('../dataAccess/funcionesPhp.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 1, "asc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });
</script>

<?php
if (isset($_POST['mensaje']))
{
    $conPermiso = TRUE;
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}

?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Listado de Entidades para Certificados OnLine</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
            </div>
            <div class="col-md-6 text-right">
                <a href="certificado_online_entidad_form.php?agregar" class="btn btn-info">Agregar entidad</a>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <?php
        $resEntidades = $colegiadoCertificadosLogic->obtenerSolicitudCertificadoWebEntidades(NULL, NULL);
        if ($resEntidades['estado']) {
        ?>    
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th style="text-align: center;">Visible Online</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="width: 50px">Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resEntidades['datos'] as $dato) {
                        $idEntidad = $dato['id'];
                        $nombre = $dato['nombre'];
                        $visible = $dato['visible'];
                        $borrado = $dato['borrado'];
                        ?>
                        <tr>
                            <td><?php echo $idEntidad;?></td>
                            <td><?php echo $nombre;?></td>
                            <td style="text-align: center;"><?php if ($visible == 1) { echo 'Visible'; } else { echo 'NO visible'; } ?></td>
                            <td style="text-align: center;"><?php if ($borrado == 1) { echo 'BORRADO'; } else { echo 'Activo'; } ?></td>
                            <td>
                                <a href="certificado_online_entidad_form.php?id=<?php echo $idEntidad; ?>&editar" class="btn btn-info">Editar</a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
        	   </tbody>
            </table>
        <?php
        } else {
        ?>
            <div class="<?php echo $resEntidades['clase']; ?>" role="alert">
                <span class="<?php echo $resEntidades['icono']; ?>" ></span>
                <span><strong><?php echo $resEntidades['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';