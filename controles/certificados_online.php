<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();
require_once ('../dataAccess/funcionesPhp.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "asc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });

    function confirmaAnular()
    {
        var leyenda = '¿Estas seguro de ANULAR esta solicitud?';
        if(confirm(leyenda))
            return true;
        else
            return false;
    }

</script>

<?php
$accedePor = "FECHA";
if (isset($_POST['mensaje'])) {
    $conPermiso = TRUE;
    ?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
}
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b>Certificados Solicitados OnLine</b></h4>
            </div>
            <div class="col-xs-3 text-right">
                <a href="certificados_online_cerrados.php" class="btn btn-info" >Ver certificados generados</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">&nbsp;</div>            
        <?php
        $resSolicitudes = $colegiadoCertificadosLogic->obtenerSolicitudCertificadoWebPendientes();
        if ($resSolicitudes['estado']) {
        ?>    
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha Solicitud</th>
                        <th style="text-align: center;">Matrícula</th>
                        <th>Apellido y Nombre</th>
                        <th>Para ser presentado</th>
                        <th style="width: 400px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resSolicitudes['datos'] as $dato) {
                        $idSolicitudCertificadoWeb = $dato['idSolicitudCertificadoWeb'];
                        $fechaSolicitud = substr($dato['fechaSolicitud'], 0, 10);
                        $idTipoCertificado = $dato['idTipoCertificado'];
                        $nombreTipoCertificado = $dato['nombreTipoCertificado'];
                        $presentado = $dato['presentado'];
                        $idColegiado = $dato['idColegiado'];
                        $matricula = $dato['matricula'];
                        $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                        ?>
                        <tr>
                            <td><?php echo $idSolicitudCertificadoWeb;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaSolicitud);?></td>
                            <td style="text-align: right;"><?php echo $matricula;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                            <td><?php echo $presentado;?></td>
                            <td style="width: 400px; text-align: center;">
                                <a href="colegiado_certificados_alta.php?id=<?php echo $idSolicitudCertificadoWeb; ?>&idColegiado=<?php echo $idColegiado; ?>&tramites_web" class="btn btn-info">Generar certificado</a>
                                <a href="datosColegiadoCertificado\abm_certificado_online.php?borrar&id=<?php echo $idSolicitudCertificadoWeb; ?>" class="btn btn-info" onclick="return confirmaAnular()">Anular Solicitud</a>
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
            <div class="<?php echo $resSolicitudes['clase']; ?>" role="alert">
                <span class="<?php echo $resSolicitudes['icono']; ?>" ></span>
                <span><strong><?php echo $resSolicitudes['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    
</script>
