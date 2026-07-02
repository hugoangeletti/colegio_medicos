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
            "order": [[ 0, "desc" ]],
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
                <h4><b>Certificados Solicitados OnLine Finalizados</b></h4>
            </div>
            <div class="col-xs-3 text-right">
                <a href="certificados_online.php" class="btn btn-info" >Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">&nbsp;</div>            
        <?php
        if (isset($_POST['periodo']) && $_POST['periodo'] != ""){
            $periodoSeleccionado = $_POST['periodo'];
        } else {
            $periodoSeleccionado = date('Y');
        }
        ?>
        <div class="row">
            <div class="col-xs-6">
                <form method="POST" action="certificados_online_cerrados.php">
                    <div class="col-xs-3">
                        <select class="form-control" id="periodo" name="periodo" required onChange="this.form.submit()">
                            <?php
                            $periodo = date('Y');
                            while ($periodo >= 2025) {
                            ?>
                                <option value="<?php echo $periodo; ?>" <?php if($periodo == $periodoSeleccionado) { echo 'selected'; } ?>><?php echo $periodo; ?></option>
                            <?php
                                $periodo--;
                            }
                            ?>
                        </select>
                    </div>
                </form>    
            </div>
        </div>
        <?php
        $resSolicitudes = $colegiadoCertificadosLogic->obtenerSolicitudCertificadoWebFinalizados($periodoSeleccionado);
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
                        <th>Generado </th>
                        <th>Fecha descarga </th>
                        <th>Cantidad descargas </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cantidadOnLine = 0;
                    $cantidadColegio = 0;
                    foreach ($resSolicitudes['datos'] as $dato) {
                        $idSolicitudCertificadoWeb = $dato['idSolicitudCertificadoWeb'];
                        $fechaSolicitud = substr($dato['fechaSolicitud'], 0, 10);
                        $idTipoCertificado = $dato['idTipoCertificado'];
                        $nombreTipoCertificado = $dato['nombreTipoCertificado'];
                        $presentado = $dato['presentado'];
                        $entidad = $dato['entidad'];
                        $presentado = $presentado.$entidad;
                        $idColegiado = $dato['idColegiado'];
                        $matricula = $dato['matricula'];
                        $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                        $fechaEntrega = $dato['fechaEntrega'];
                        if (isset($fechaEntrega) && $fechaEntrega <> "") {
                            $fechaEntrega = cambiarFechaFormatoParaMostrar(substr($fechaEntrega, 0, 10));
                        }
                        $cantidadDescargas = $dato['cantidadDescargas'];
                        if (isset($dato['idSolicitudCertificadoWebEntidad']) && $dato['idSolicitudCertificadoWebEntidad'] <> "") {
                            $generado = 'On-Line';
                            $cantidadOnLine += 1;
                        } else {
                            $generado = 'En el Colegio';
                            $cantidadColegio += 1;
                        }
                        ?>
                        <tr>
                            <td><?php echo $idSolicitudCertificadoWeb;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaSolicitud);?></td>
                            <td  style="text-align: center;"><?php echo $matricula;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                            <td><?php echo $presentado;?></td>
                            <td><?php echo $generado;?></td>
                            <td><?php echo $fechaEntrega;?></td>
                            <td><?php echo $cantidadDescargas;?></td>
                        </tr>
                    <?php
                    }
                    ?>
        	   </tbody>
            </table>
            <div class="row">&nbsp;</div>
            <div class="row">
                <h4>Cantidad generados ON-line: <b><?php echo $cantidadOnLine; ?></b></h4>
                <h4>Cantidad generados en el Colegio: <b><?php echo $cantidadColegio; ?></b></h4>
            </div>
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
