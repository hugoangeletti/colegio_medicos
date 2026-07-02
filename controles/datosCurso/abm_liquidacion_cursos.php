<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
$cursos_pdo = new cursos_pdo();

if (isset($_GET['agregar'])) {
    $accion = 'agregar';
    if ($_POST) {
        $anio_periodo = $_POST['anio_periodo'];
        $mes_periodo = $_POST['mes_periodo'];
        $periodo_liquidacion = $anio_periodo . str_pad($mes_periodo, 2, "0", STR_PAD_LEFT);
        $fecha_cobranza = $_POST['fecha_cobranza'];
        $criterio_liquidacion = $_POST['criterio_liquidacion'];

        if (isset($_POST['pagos_seleccionados'])) {
            // Captura de datos del formulario
            $id_curso = $_POST['id_curso'];
            $monto_total = $_POST['monto_total'];
            $monto_base_total = $_POST['monto_base_total'];
            $monto_liquidar_total = $_POST['monto_liquidar_total'];
            $cuotasIds = $_POST['pagos_seleccionados']; // Es un array

            $resultado = $cursos_pdo->generarLiquidacion($id_curso, $fecha_cobranza, $periodo_liquidacion, $cuotasIds, $monto_total, $monto_base_total, $monto_liquidar_total, $criterio_liquidacion);
        } else {
            if (isset($_POST['cursos_seleccionados'])) {
                //por cada curso obtenemos los pagos que se incluyen segun los parametros
                foreach ($_POST['cursos_seleccionados'] as $curso) {
                    $id_curso = $curso;
                    $monto_total = 0;
                    $cuotasIds = array();
                    $resPagos = $cursos_pdo->obtenerDetalleCobranzaPorPeriodoFechaPago($id_curso, $fecha_cobranza, $anio_periodo, $mes_periodo, $criterio_liquidacion);
                    if ($resPagos['estado']) {
                        foreach ($resPagos['datos'] as $cuota) {
                            $cuotasIds[] = $cuota['idCursosAsistenteCuotas'];
                            $monto_total += $cuota['importe'];
                        }
                    }
                    $resultado = $cursos_pdo->generarLiquidacion($id_curso, $fecha_cobranza, $periodo_liquidacion, $cuotasIds, $monto_total, $monto_base_total, $monto_liquidar_total, $criterio_liquidacion);
                }
            } else {
                $mensaje = "No se seleccionaron cursos para liquidar.";
                $clase = "alert alert-warning";
            }
        }
    } else {
        $mensaje = "No se seleccionaron cuotas para liquidar.";
        $clase = "alert alert-warning";
    }
} else {
    if (isset($_GET['anular'])) {
        $accion = 'anular';
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $id_liquidacion = $_GET['id'];
            $resLiquidacion = $cursos_pdo->obtenerLiquidacionPorId($id_liquidacion);
            if ($resLiquidacion['estado']) {
                $datosAnteriores = $resLiquidacion['datos'];
            } else {
                $datosAnteriores = array('error' => $resLiquidacion['mensaje']);
            }
            $resultado = $cursos_pdo->anularLiquidacion($id_liquidacion, $datosAnteriores);
        } else {
            $mensaje = "No se seleccionó liquidación para anular.";
            $clase = "alert alert-warning";
        }
    } else {
        $resultado['mensaje'] = "Ingreso incorrecto.";
        $resultado['clase'] = "alert alert-danger";
    }
}
/*
var_dump($_GET);
echo '<br>';
var_dump($_POST);
echo '<br>';
var_dump($resultado);
echo '<br>';
exit;
*/
// Redireccionar con el mensaje
?>
<form id="redirect" action="../liquidacion_cursos_listado.php" method="POST">
    <input type="hidden" name="mensaje" value="<?php echo $mensaje; ?>">
    <input type="hidden" name="clase" value="<?php echo $clase; ?>">
</form>
<script type="text/javascript">
    document.getElementById('redirect').submit();
</script>
