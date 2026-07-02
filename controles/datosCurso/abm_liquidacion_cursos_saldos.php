<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
$cursos_pdo = new cursos_pdo();

$resultado = [
    'estado'  => false,
    'mensaje' => 'Ocurrió un error inesperado.',
    'clase'   => 'alert alert-danger',
    'icono'   => 'glyphicon glyphicon-exclamation-sign'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['agregar'])) {
    
    // 1. Capturar y sanear los datos del formulario
    $criterio_liquidacion = 'saldos';
    $monto_total_cursos   = isset($_POST['monto_total']) ? floatval($_POST['monto_total']) : 0.0;
    $monto_liquidar       = isset($_POST['monto_liquidar']) ? floatval($_POST['monto_liquidar']) : 0.0;
    
    $liquidaciones_seleccionadas = isset($_POST['liquidaciones_seleccionadas']) ? $_POST['liquidaciones_seleccionadas'] : [];
    $docentes_liquidados  = isset($_POST['docentes_liquidados']) ? $_POST['docentes_liquidados'] : [];
    $montos_docentes      = isset($_POST['monto_docente']) ? $_POST['monto_docente'] : [];

    /*
    echo 'monto_liquidar->'.$monto_liquidar.'<br>';
    var_dump($liquidaciones_seleccionadas);
    echo '<br>';
    var_dump($docentes_liquidados);
    echo '<br>';
    var_dump($montos_docentes);
    echo '<br>';
    exit;
    */
    // Validaciones básicas de seguridad en el servidor
    if (empty($liquidaciones_seleccionadas) || empty($docentes_liquidados)) {
        $resultado['mensaje'] = "Debe seleccionar al menos una liquidación y un docente para liquidar.";
        $_SESSION['alerta'] = [
            'estado'  => false,
            'mensaje' => 'Error: Debe seleccionar al menos una liquidación y un docente para liquidar.',
            'clase'   => 'alert alert-danger',
            'icono'   => 'glyphicon glyphicon-exclamation-sign'
        ];
        header("Location: ../liquidacion_cursos_saldos_form.php?agregar");
        exit;
    }

    if ($monto_liquidar > $monto_total_cursos) {
        $resultado['mensaje'] = "Error de validación: El monto asignado a los docentes supera el total de los cursos.";
        echo json_encode($resultado);
        //exit;
    }

    $resultado = $cursos_pdo->generarLiquidacionSaldos($monto_liquidar, $liquidaciones_seleccionadas, $docentes_liquidados, $montos_docentes);

} else {
    if (isset($_GET['anular'])) {
        $id_liquidacion_cursos_saldos = $_GET['id'];
        $resultado = $cursos_pdo->anularLiquidacionCursosSaldos($id_liquidacion_cursos_saldos);
    }
}

var_dump($_POST);
echo '<br>';
var_dump($resultado);

// Redirección con alertas mediante sesión tradicional
$_SESSION['alerta'] = $resultado;
if ($resultado['estado']) {
    header("Location: ../liquidacion_cursos_docentes_listado.php?saldos"); 
} else {
    header("Location: ../liquidacion_cursos_saldos_form.php?agregar"); 
}
exit;
