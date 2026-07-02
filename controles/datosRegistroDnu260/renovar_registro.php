<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();

$continua = TRUE;

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idRegistro = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= 'MAL ingresado idRegistro - ';
}

if (isset($_POST['fechaRenovacion']) && $_POST['fechaRenovacion'] <> "") {
    $fechaRenovacion = $_POST['fechaRenovacion'];
} else {
    $continua = FALSE;
    $mensaje .= 'MAL ingresada fechaRenovacion - ';
}

if ($continua) {
	$resultado = $registroDNU260Logic->renovarRegistro($idRegistro, $fechaRenovacion); 
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = $continua;
}

//var_dump($resultado);
//exit;
?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../registro_dnu260_lista.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>