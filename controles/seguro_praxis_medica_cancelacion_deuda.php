<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiado_seguro_Logic.php');
require_once ('../dataAccess/usuarioLogic.php');
?>
<script>
$(document).ready(
    function () {
                $('#tablaEnvios').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 1, "asc" ]],
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
if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
       <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php
}
$continua = TRUE;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSeguro = $_GET['id'];
    $colegiado_seguro_Logic = new colegiado_seguro_Logic();
    $resSeguroProcesado = $colegiado_seguro_Logic->obtenerSeguroProcesadoPorId($idSeguro);
    if ($resSeguroProcesado['estado']) {
        $seguroProcesado = $resSeguroProcesado['datos'];
        $fechaLimiteProceso = $seguroProcesado['fechaLimiteProceso'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idSeguro - ";
}
?>
<div class="panel panel-info">
<div class="panel-heading">
    <h4>Listado matrículas que cancelaron deuda (más de 6 cuotas) al <?php echo cambiarFechaFormatoParaMostrar($fechaLimiteProceso); ?></h4>
</div>
<div class="panel-body">
    <div class="row">
        <div class="col-md-2">
            <a href="seguro_praxis_medica_cancelacion_deuda_archivo.php?id=<?php echo $idSeguro; ?>" class="btn btn-primary">Descargar archivo</a>
        </div>
        <div class="col-md-10 text-right">
            <a href="seguro_praxis_medica_listado.php" class="btn btn-primary">Volver</a>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <?php
            $resCancelaDeuda = $colegiado_seguro_Logic->obtenerCancelacionDeuda($idSeguro, $fechaLimiteProceso);
            if ($resCancelaDeuda['estado']) {
            ?>
                <table  id="tablaEnvios" class="display">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Apellido y Nombre</th>
                            <th>Lugar de Pago</th>
                            <th>Cuotas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $anular = TRUE;
                        foreach ($resCancelaDeuda['datos'] as $dato) {
                            $matricula = $dato['matricula'];
                            $apellidoNombre = trim($dato['apellido']).', '.trim($dato['nombre']);
                            $lugarPago = $dato['lugarPago'];
                            $cuotasAbonadas = $dato['cuotasAbonadas'];
                            ?>
                            <tr>
                                <td><?php echo $matricula; ?></td>
                                <td><?php echo $apellidoNombre; ?></td>
                                <td><?php echo $lugarPago; ?></td>
                                <td><?php echo $cuotasAbonadas;?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php
            } else {
            ?>
                <div class="row">&nbsp;</div>
                <div class="<?php echo $resCancelaDeuda['clase']; ?>" role="alert">
                    <span class="<?php echo $resCancelaDeuda['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resCancelaDeuda['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';
