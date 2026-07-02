<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/especialidades_pdo.php');

// 1. Controlar y limpiar los datos cargados en el formulario
$action = isset($_POST['action']) ? $_POST['action'] : '';
$idCalificacion = isset($_POST['id_calificacion']) ? intval($_POST['id_calificacion']) : 0;
$especialidad = isset($_POST['especialidad']) ? trim($_POST['especialidad']) : '';
$codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$codigoRes = isset($_POST['codigo_res']) ? trim($_POST['codigo_res']) : '';

// Los padres asociados vienen como un arreglo tradicional
$padresAsociados = isset($_POST['padres']) ? $_POST['padres'] : array();

// Validación estricta de datos obligatorios
if (empty($especialidad) || empty($codigoRes)) {
    echo "<script>alert('Error: Los campos Especialidad y Código Resolución son obligatorios.'); window.history.back();</script>";
    exit;
}

if ($action != 'create' && $action != 'edit') {
    echo "<script>alert('Error: Acción no válida.'); window.history.back();</script>";
    exit;
}

// 2. Instanciar la clase y pasarle los datos a la función encargada de la persistencia
$objEspecialidades = new especialidades_pdo();
$resultado = $objEspecialidades->guardar_calificacion($action, $idCalificacion, $especialidad, $codigo, $codigoRes, $padresAsociados);

// Evaluar la respuesta devuelta por el modelo
if ($resultado['estado']) {
    // Éxito: Redirigir al listado principal
    echo "<script>window.location.href = '../calificaciones_agregadas_listado.php';</script>";
} else {
    // Error: Mostrar alerta con el mensaje detallado del Catch y volver atrás
    echo "<script>alert('Error en la base de datos: " . addslashes($resultado['mensaje']) . "'); window.history.back();</script>";
}
exit;
?>