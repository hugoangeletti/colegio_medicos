<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php'); 
require_once ('../../dataAccess/colegiadoCargoLogic.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/reunion_consejo_pdo.php');

$reunionConsejoLogic = new reunion_consejo_pdo();
$resultado = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Captura de datos del formulario (0 si es Nueva, ID numérico si es Edición)
    $idReunionConsejo = (!empty($_POST['idReunionConsejo'])) ? intval($_POST['idReunionConsejo']) : 0;
    
    $fecha            = $_POST['fecha'];
    $tipoReunion      = $_POST['tipoReunion'];
    $numeroActa       = $_POST['numeroActa'];
    $observacion      = $_POST['observacion'];
    $idUsuarioCarga   = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1; 

    // Si no marcaron a nadie, definimos el array vacío en lugar de saltarnos el guardado
    $asistentesIds = (isset($_POST['asistencia']) && is_array($_POST['asistencia'])) ? $_POST['asistencia'] : array();
    
    // Llamada única al método lógico de negocio
    $resultado = $reunionConsejoLogic->guardarReunionAsistentes($idReunionConsejo, $fecha, $numeroActa, $tipoReunion, $observacion, $idUsuarioCarga, $asistentesIds);
    
    // ¡CRUCIAL! Si fue un ALTA exitosa, el método lógico debe retornar el nuevo ID en $resultado['idNuevaReunion']
    if ($idReunionConsejo === 0 && $resultado['estado'] && !empty($resultado['idNuevaReunion'])) {
        $idReunionConsejo = $resultado['idNuevaReunion'];
    }

} else {
    $resultado['estado'] = FALSE;
    $resultado['mensaje'] = "Método de solicitud no válido.";
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    $idReunionConsejo = 0;
}
/*
var_dump($resultado);
exit();
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    // Si la operación fue exitosa y tenemos un ID válido (sea nuevo o editado), volvemos a la gestión de esa reunión
    if ($resultado['estado'] && $idReunionConsejo > 0) {
    ?>
        <form name="myForm" method="POST" action="../reunion_consejo_lista.php">
            <input type="hidden" name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden" name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden" name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        </form>
    <?php 
    } else {
        // Si hubo un error o no hay ID, volvemos a la lista general
    ?>
        <form name="myForm" method="POST" action="../reunion_consejo_lista.php">
            <input type="hidden" name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden" name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden" name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        </form>
    <?php 
    }
    ?>
</body>