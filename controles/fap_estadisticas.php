<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    Chart.register(ChartDataLabels); // Registrar plugin
</script>

<script>
    /*
    fetch('fap_cantidad_anual.php')
    .then(response => response.json())
    .then(data => {
        const ctx = document.getElementById('miGrafico').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Causas FAP Aprobadas de los últimos 15 años.',
                    data: data.data,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    });
    */
    fetch('fap_cantidad_anual.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('miGrafico').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    plugins: [ChartDataLabels], // Activar plugin
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Causas',
                            data: data.data,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#333',
                                font: {
                                    weight: 'bold',
                                    size: 12
                                },
                                formatter: (value) => value.toLocaleString() // Formato con separadores
                            }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            });

</script>
<?php
if (isset($_POST['idSapTipoTramite']) && isset($_POST['tipoListado']) && isset($_POST['fechaDesde']) && isset($_POST['fechaHasta'])) {
    $idSapTipoTramiteSolicitado = $_POST['idSapTipoTramite'];
    $tipoListado = $_POST['tipoListado'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
} else {
    $idSapTipoTramiteSolicitado = NULL;
    $tipoListado = NULL;
    $fechaDesde = NULL;
    $fechaHasta = NULL;
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Estadísticas FAP</h4>
            </div>
            <?php
            if (isset($_GET['procesar'])) {
            ?>
                <div class="col-md-3">
                    <a href="fap_estadisticas.php" class="btn btn-info">Otra consulta</a>
                </div>
            <?php 
            } 
            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_GET['procesar'])) {
            $fapLogic = new fapLogic();
            $resEstadisticas = $fapLogic->obtenerTotalesParaEstadisticas($fechaDesde, $fechaHasta);
            if ($resEstadisticas['estado']) {
            ?>
                <div class="col-md-3"></div>
                <div class="col-md-6 text-center">
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Tipo causa</th>
                            <th>Tipo trámite</th>
                            <th>Estado</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>                
                    <?php
                    foreach ($resEstadisticas['datos'] as $dato) {
                        $idSapTipoTramite = $dato['idSapTipoTramite'];
                        $nombreSapTipoTramite = $dato['nombreSapTipoTramite'];
                        $cantidad = $dato['cantidad'];
                        $idTipoCausa = $dato['idTipoCausa'];
                        $nombreTipoCausa = $dato['nombreTipoCausa'];
                        if (!isset($nombreTipoCausa) || $nombreTipoCausa == "") {
                            $nombreTipoCausa = 'Sin Tipo de Causa';
                        }
                        $nombreSapEstado = $dato['nombreSapEstado'];
                        if ($idSapTipoTramiteSolicitado == 2 && $idSapTipoTramite == 1) { continue; } 
                        if ($idSapTipoTramiteSolicitado == 3 && $idSapTipoTramite != 1) { continue; } 
                        ?>
                        <tr>
                            <td><?php echo $nombreTipoCausa;?></td>
                            <td><?php echo $nombreSapTipoTramite;?></td>
                            <td><?php echo $nombreSapEstado;?></td>
                            <td><?php echo $cantidad;?></td>
                        </tr>
                    <?php    
                    }
                    ?>
                    </tbody>
                </table>
                </div>
            <?php
            } else {
            ?>
                <div class="<?php echo $resEstadisticas['clase']; ?>" role="alert">
                    <span class="<?php echo $resEstadisticas['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resEstadisticas['mensaje']; ?></strong></span>
                </div>
            <?php
            }
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <form id="datosBusqueda" autocomplete="off" name="datosBusqueda" method="POST" action="fap_estadisticas.php?procesar">
                <div class="row">
                    <div class="col-md-2">
                        <label for="fechaDesde">Fecha desde: *</label>
                        <input class="form-control" type="date" name="fechaDesde" id="fechaDesde" required>
                    </div>
                    <div class="col-md-2">
                        <label for="fechaHasta">Fecha hasta: *</label>
                        <input class="form-control" type="date" name="fechaHasta" id="fechaHasta" required>
                    </div>
                    <div class="col-md-2">
                        <label class="control-label">Tipo trámite: *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="idSapTipoTramite" value="1" checked>Todos</label>
                        <label class="radio-inline"><input type="radio" name="idSapTipoTramite" value="2">Solo causas</label>
                        <label class="radio-inline"><input type="radio" name="idSapTipoTramite" value="3">Solo consultas</label>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label">Tipo listado: *</label>
                        <br>
                        <label class="radio-inline"><input type="radio" name="tipoListado" value="1" checked>Solo totales</label>
                        <label class="radio-inline"><input type="radio" name="tipoListado" value="2">Con detalle de causas</label>
                    </div>
                    <div class="col-md-1 text-left">
                        <br>
                        <button type="submit"  class="btn btn-success btn-lg" onclick="waitingDialog.show('Generando Listado...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar listado </button>
                    </div>
                </div>    
            </form>
            <div class="row" style="background-color: #428bca;"></div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center"><h4>Causa FAP Aprobadas de los últimos 15 años.</h4></div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <canvas id="miGrafico" width="600" height="200" class="text-center"></canvas>
                </div>
                <div class="row">&nbsp;</div>
                <div class="col-md-12 text-center">
                    <a href="fap_estadisticas_imprimir_causas.php" class="btn btn-info" target="_BLANK">Imprimir totales de causas</a>
                </div>
            </div>
        <?php
        }
        ?>
    </div>    
</div>
<?php
require_once '../html/footer.php';
?>
<script type="text/javascript">     
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": false,
            "bFilter": false,
            "order": [[ 0, "asc" ], [ 1, "asc"]],
            dom: 'T<"clear">lfrtip',
        });
    });
</script>