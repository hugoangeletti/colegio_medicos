<?php
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');
// 2. Validar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_curso'])) {
    
    $cursos_pdo = new cursos_pdo(); // Instancia de tu clase

    // 3. Sanitizar y recibir datos
    $id_curso     = intval($_POST['id_curso']);
    $fecha_pago   = $_POST['fecha_pago'];
    $anio_periodo = $_POST['anio'];
    $mes_periodo  = $_POST['mes'];
    $criterio_liquidacion  = $_POST['criterio_liquidacion'];

    // 4. Ejecutar la consulta que ya tienes programada
    $resDetalle = $cursos_pdo->obtenerDetalleCobranzaPorPeriodoFechaPago(
        $id_curso, 
        $fecha_pago, 
        $anio_periodo, 
        $mes_periodo,
        $criterio_liquidacion
    );

    // 5. Generar la respuesta visual
    if ($resDetalle['estado']) { ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover table-condensed">
                <thead>
                    <tr class="info">
                        <th>Asistente</th>
                        <th class="text-center">Colegiado</th>
                        <th class="text-center">Cuota</th>
                        <th class="text-center">Vencimiento</th>
                        <th class="text-right">Importe</th>
                        <th class="text-center">Fecha Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($resDetalle['datos'] as $item) { 
                        $total += $item['importe'];
                    ?>
                    <tr>
                        <td><?php echo $item['apellidoNombre']; ?></td>
                        <td class="text-center"><?php echo $item['idColegiado']; ?></td>
                        <td class="text-center"><?php echo $item['cuota']; ?></td>
                        <td class="text-center"><?php echo date('d/m/Y', strtotime($item['fechaVencimiento'])); ?></td>
                        <td class="text-right">$ <?php echo number_format($item['importe'], 2, ',', '.'); ?></td>
                        <td class="text-center"><?php echo date('d/m/Y', strtotime($item['fechaPago'])); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="active">
                        <th colspan="4" class="text-right">TOTAL DETALLE:</th>
                        <th class="text-right">$ <?php echo number_format($total, 2, ',', '.'); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

    <?php } else { ?>
        <!-- Mensaje en caso de que no haya datos -->
        <div class="<?php echo $resDetalle['clase']; ?>">
            <i class="<?php echo $resDetalle['icono']; ?>"></i> <?php echo $resDetalle['mensaje']; ?>
        </div>
    <?php }
} else {
    echo "Acceso no autorizado.";
}
?>
