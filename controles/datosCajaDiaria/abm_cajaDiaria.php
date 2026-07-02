<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cajaDiariaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && ($_POST['accion'] == "Abrir" || $_POST['accion'] == "Cerrar")) {
    $accion = $_POST['accion'];
} else {
    $accion = "ERROR";
    $continua = FALSE;
    $mensaje .= "Falta accion. ";
}
if ($accion == "Abrir") {
    if (isset($_POST['fechaCaja']) && $_POST['fechaCaja'] <> "") {
        $fechaCaja = $_POST['fechaCaja'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaCaja. ";
    }
    if (isset($_POST['saldoInicial']) && $_POST['saldoInicial'] <> "") {
        $saldoInicial = $_POST['saldoInicial'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta saldoInicial. ";
    }
} else {
    if ($accion == "Cerrar") {
        if (isset($_POST['idCajaDiaria']) && $_POST['idCajaDiaria'] <> "") {
            $idCajaDiaria = $_POST['idCajaDiaria'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCajaDiaria. ";
        }
        if (isset($_POST['totalRecaudacion']) && $_POST['totalRecaudacion'] <> "") {
            $totalRecaudacion = $_POST['totalRecaudacion'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta totalRecaudacion. ";
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Datos erroneos - ";
    }
}
if ($continua) {
    switch ($accion) {
        case 'Abrir':
            $cajaDiariaLogic = new cajaDiariaLogic();
            $resultado = $cajaDiariaLogic->abririCajaDiaria($fechaCaja, $saldoInicial);
            $linkOrigen = "../cajadiaria_abrir.php";
            break;
        
        case 'Cerrar':
            $cajaDiariaLogic = new cajaDiariaLogic();
            $resultado = $cajaDiariaLogic->cerrarCajaDiaria($idCajaDiaria, $totalRecaudacion);
            $linkOrigen = "../cajadiaria_cerrar.php?id=".$idCajaDiaria;
            break;

        default:
            $resultado['estado'] = FALSE;
            $linkOrigen = "../cajadiaria.php";
            break;
    }
} else {
    $resultado['estado'] = FALSE;
    $linkOrigen = "../cajadiaria.php";    
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../cajadiaria.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="<?php echo $linkOrigen; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion; ?>">
        </form>
    <?php
    }
    ?>
</body>


