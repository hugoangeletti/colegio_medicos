<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();

$continua = TRUE;
$accion = $_POST['accion'];

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idRegistro = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= 'MAL ingresado idRegistro - ';
}
$matricula = NULL;
$tipoBaja = NULL;
$motivoBaja = array();
$mensaje = "";

if ($accion == 2) {
    if (isset($_POST['tipoBaja']) && $_POST['tipoBaja'] <> "") {
        $tipoBaja = $_POST['tipoBaja'];
        if ($tipoBaja == '1') {
            if (isset($_POST['matricula']) && $_POST['matricula'] > 0) {
                $matricula = $_POST['matricula'];
            } else {
                $continua = FALSE;
                $mensaje .= 'MAL ingresada matricula - ';
            }
        } else {
            if ($tipoBaja == '2') {
                if (isset($_POST['motivoBaja']) && sizeof($_POST['motivoBaja']) > 0) {
                    $motivoBaja = $_POST['motivoBaja'];
                } else {
                    $continua = FALSE;
                    $mensaje .= 'MAL ingresada motivoBaja - ';
                }
            }
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'MAL ingresado tipoBaja - ';
    }
}

if ($continua) {
    if ($accion == 2) {
        $revalida = 0;
        $convalida = 0;
        $constanciaLaboral = 0;
        foreach ($motivoBaja as $value) {
            switch ($value) {
                case '1':
                    $revalida = 1;
                    break;
                
                case '2':
                    $convalida = 1;
                    break;
                
                case '3':
                    $constanciaLaboral = 1;
                    break;
                
                default:
                    break;
            }
        }
    	$resultado = $registroDNU260Logic->borrarRegistro($idRegistro, $tipoBaja, $matricula, $revalida, $convalida, $constanciaLaboral); 
    } else {
        $resultado = $registroDNU260Logic->activarRegistro($idRegistro); 
    }
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