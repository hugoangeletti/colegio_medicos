<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoDebitosLogic.php');
$colegiadoDebitosLogic = new colegiadoDebitosLogic();

$idColegiado = $_POST['idColegiado'];
$continua = TRUE;
$mensaje = "";

if (isset($_POST['tipo']) && isset($_POST['idBanco'])) {
    $accion = '1';
    $tipo = $_POST['tipo'];
    $tipoAnterior = $_POST['tipoAnterior'];
    $idBanco = $_POST['idBanco'];
    $incluyePP = $_POST['incluyePP'];
    $incluyeTotal = $_POST['incluyeTotal'];
    $numeroDocumento = NULL;
    $numeroTarjeta = NULL;
    $numeroCbu = NULL;
    $tipoCuenta = NULL;
    if ($tipo == 'C') {
        if (isset($_POST['numeroTarjeta']) && isset($_POST['numeroDocumento'])) {
            $numeroTarjeta = $_POST['numeroTarjeta'];
            $numeroDocumento = $_POST['numeroDocumento'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje .= "Faltan datos de la tarjeta, verifique.";
        }
    } else {
        if (isset($_POST['numeroCbu']) && isset($_POST['tipoCuenta'])) {
            $numeroCbu = $_POST['numeroCbu'];
            $tipoCuenta = $_POST['tipoCuenta'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje .= "Faltan datos del CBU, verifique.";
        }
    }
} else {
    if (isset($_GET['idColegiado']) && isset($_GET['tipo'])) {
        $idColegiado = $_GET['idColegiado'];
        $tipo = $_GET['tipo'];
        $accion = $_GET['accion'];
        if (isset($_POST['tipoBaja'])) {
            $tipoBaja = $_POST['tipoBaja'];
        } else {
            $mensaje .= 'Falta tipo de baja';
            $continua = FALSE;
        }
        if (isset($_POST['idDebito'])) {
            $idDebito = $_POST['idDebito'];
        } else {
            $mensaje .= 'Falta idDebito';
            $continua = FALSE;
        }
    } else {
        $mensaje .= 'Falta tipo de debito/banco';
        $continua = FALSE;
    }
}
if ($continua){
    switch ($accion) {
        case '1':
            $resultado = $colegiadoDebitosLogic->agregarColegiadoDebito($idColegiado, $idBanco, $tipo, $numeroTarjeta, $numeroDocumento, $incluyePP, $incluyeTotal, $tipoAnterior, $numeroCbu, $tipoCuenta);
            if ($resultado['estado']) {
                $pathOrigen = '../';
                include 'colegiado_debito_generar_pdf.php';            
            }

            break;
        
        case '2':
            $resultado = $colegiadoDebitosLogic->bajaColegiadoDebito($idDebito, $tipo, $tipoBaja);
            break;

        default:
            // code...
            break;
    }
    
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS. ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        if ($accion == '1') {
            $link = "../colegiado_debito_imprimir.php?idColegiado=".$idColegiado."&tipo=".$tipo;
        } else {
            $link = "../colegiado_debito_envia_mail_baja.php?idColegiado=".$idColegiado."&tipo=".$tipo."&id=".$idDebito;
        }
        ?>
        <form name="myForm" method="POST" action="<?php echo $link; ?>">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_debito.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="tipo" id="tipo" value="<?php echo $tipo;?>">
            <input type="hidden"  name="tipoAnterior" id="tipoAnterior" value="<?php echo $tipoAnterior;?>">
            <input type="hidden"  name="idBanco" id="idBanco" value="<?php echo $idBanco;?>">
            <input type="hidden"  name="numeroTarjeta" id="numeroTarjeta" value="<?php echo $numeroTarjeta;?>">
            <input type="hidden"  name="numeroDocumento" id="numeroDocumento" value="<?php echo $numeroDocumento;?>">
            <input type="hidden"  name="numeroCbu" id="numeroCbu" value="<?php echo $numeroCbu;?>">
            <input type="hidden"  name="tipoCuenta" id="tipoCuenta" value="<?php echo $tipoCuenta;?>">
            <input type="hidden"  name="incluyePP" id="incluyePP" value="<?php echo $incluyePP;?>">
            <input type="hidden"  name="incluyeTotal" id="incluyeTotal" value="<?php echo $incluyeTotal;?>">
        </form>
    <?php
    }
    ?>
</body>

