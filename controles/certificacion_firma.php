<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/constanciaFirmaLogic.php');
$constanciaFirmaLogic = new constanciaFirmaLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaDetalleCaja').DataTable({
                    "iDisplayLength":25,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmaAnular()
{
    if(confirm('¿Estas seguro de ANULAR este RECIBO?'))
        return true;
    else
        return false;
}
</script>
<?php
if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
       <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php
}
$continua = TRUE;
$mensaje = '';
?>

<div class="panel panel-info">
<div class="panel-heading">
    <h4>
        <b>Certificación de firma</b>
    </h4>
</div>
<?php
if ($continua) {
    if (isset($_POST['fecha']) && $_POST['fecha'] <> "") {
        $fechaActual = $_POST['fecha'];
    } else {
        $fechaActual = date('Y-m-d');
    }
    $importeTotal = 0;
    $resCertificaciones = $constanciaFirmaLogic->obtenerCertificacionFirmaPorFecha($fechaActual);
    if ($resCertificaciones['estado']) {
        foreach ($resCertificaciones['datos'] as $dato){
            if (isset($dato['numeroComprobante']) && $dato['numeroComprobante'] <> "") { continue; }

            $importeTotal += $dato['importe'];
        }
    }
    ?>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <form method="POST" action="certificacion_firma.php">
                    <input type="date" name="fecha" id="fecha" value="<?php echo $fechaActual; ?>" required onChange="this.form.submit()">
                </form>
            </div>
            <div class="col-md-3">
                <form method="POST" action="certificacion_firma_nueva.php">
                    <button type="submit" class="btn btn-success">Nueva certificación de firma</button>
                </form>
            </div>
            <?php             
            if ($importeTotal > 0) {
            ?><!--
                <div class="col-md-3">
                    <form method="POST" action="datosCajaDiaria\generar_recibo.php">
                        <button type="submit" class="btn btn-default">Generar recibo de firmas</button>
                        <input type="hidden" name="importe" id="importe" value="<?php echo $importeTotal; ?>">
                        <input type="hidden" name="tipoRecibo" id="tipoRecibo" value="FIRMA">
                    </form>
                </div>-->
            <?php 
            }
            ?>
        </div>
        <?php
        if ($resCertificaciones['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaDetalleCaja" class="display">
                        <thead>
                            <tr>
                                <th style="display: none;">Id</th>
                                <th style="text-align: center;">Matricula</th>
                                <th style="text-align: center;">Apellido y Nombre</th>
                                <th style="text-align: center;">Importe</th>
                                <th style="text-align: center;">Estado</th>
                                <th style="text-align: center;">Recibo</th>
                                <th style="text-align: center;">PDF</th>
                                <th style="text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resCertificaciones['datos'] as $dato){
                                $idCertificacionFirma = $dato['idCertificacionFirma'];
                                $idColegiado = $dato['idColegiado'];
                                $matricula = $dato['matricula'];
                                $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                                $importe = $dato['importe'];
                                if (isset($dato['nombreArchivo']) && $dato['nombreArchivo']) {
                                    $nombreArchivoArray = explode('/', $dato['nombreArchivo']);
                                    $nombreArchivo = $nombreArchivoArray[4];
                                } else {
                                    $nombreArchivo = "No generado";
                                }
                                $estado = $dato['estado'];
                                if (isset($estado)) {
                                    if ($estado == 'B') {
                                        $estadoDetalle = 'ANULADO';
                                        $style = 'color: red;';
                                    } else {
                                        $estadoDetalle = 'OK';
                                        $style = '';
                                    }
                                }
                                if (isset($dato['numeroComprobante']) && $dato['numeroComprobante'] <> "") {
                                    $comprobante = $dato['tipoComprobante'].'-'.$dato['numeroComprobante'];    
                                } else {
                                    $comprobante = 'Pendiente de emisión';
                                }
                                
                                ?>
                                <tr>
                                    <td style="display: none;"><?php echo $idCertificacionFirma; ?></td>
                                    <td style="text-align: center;"><?php echo $matricula?></td>
                                    <td style="text-align: center;"><?php echo $apellidoNombre;?></td>
                                    <td style="text-align: center;"><?php echo $importe;?></td>
                                    <td style="text-align: center; <?php echo $style; ?>"><?php echo $estadoDetalle;?></td>
                                    <td style="text-align: center; <?php echo $style; ?>"><?php echo $comprobante;?></td>
                                    <td ><?php echo $nombreArchivo;?></td>
                                    <td>
                                        <?php 
                                        if (isset($nombreArchivo) && $nombreArchivo <> "No generado") {
                                        ?>
                                            <a href="certificacion_firma_nueva.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idCertificacionFirma; ?>" class="btn btn-primary" >Imprimir</a>       
                                        <?php
                                        } else {
                                        ?>
                                            <a href="datosCertificacionFirma/generar_recibo_firma.php?id=<?php echo $idCertificacionFirma; ?>" class="btn btn-success" >Imprimir</a>
                                        <?php
                                        }

                                        if ($estado == 'A') {
                                        ?>
                                            <a href="datosCertificacionFirma/anular_certificacion_firma.php?id=<?php echo $idCertificacionFirma; ?>" class="btn btn-danger" onclick="return confirmaAnular()">Anular</a>
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
            <div class="<?php echo $resCertificaciones['clase']; ?>" role="alert">
                <span class="<?php echo $resCertificaciones['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resCertificaciones['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
<?php
}
?>
</div>
<?php
require_once '../html/footer.php';
