<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/remitenteLogic.php');
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
    <div class="panel-heading"><h4><b>Listado de Remitentes para Notas/Oficios</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
            </div>
            <div class="col-md-6 text-right">
                <a href="remitente_form.php?agregar" class="btn btn-info">Agregar remitente</a> 
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <?php
        $remitenteLogic = new remitenteLogic();
        $resRemitentes = $remitenteLogic->obtenerRemitentes();
        if ($resRemitentes['estado']) {
        ?>    
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th style="width: 50px">Id</th>
                        <th style="width: 120px">Nombre</th>
                        <th style="width: 50px">Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resRemitentes['datos'] as $dato) {
                        $idRemitente = $dato['id'];
                        $nombre = $dato['nombre'];
                        ?>
                        <tr>
                            <td><?php echo $idRemitente;?></td>
                            <td><?php echo $nombre;?></td>
                            <td>
                                <a href="remitente_form.php?id=<?php echo $idRemitente; ?>&editar" class="btn btn-info">Editar</a>
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
            <div class="<?php echo $resRemitentes['clase']; ?>" role="alert">
                <span class="<?php echo $resRemitentes['icono']; ?>" ></span>
                <span><strong><?php echo $resRemitentes['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';