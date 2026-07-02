<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();
require_once ('../../dataAccess/funcionesPhp.php');

if (isset($_POST['idEticaExpediente'])) {
    $idEticaExpediente = $_POST['idEticaExpediente'];
    $accion = $_POST['accion'];
} else {
    $idEticaExpediente = NULL;
    $accion = 1;
}
$estadoExpediente = $_POST['estadoExpediente'];

$continua = TRUE;
if (isset($_POST['caratula']) && isset($_POST['nroExpediente']) && isset($_POST['idColegiado']) && isset($_POST['fechaReunionConsejo'])) {
    $caratula = $_POST['caratula'];
    $nroExpediente = $_POST['nroExpediente'];
    $idColegiado = $_POST['idColegiado'];
    $observaciones = $_POST['observaciones'];
    $colegiadoBuscar = $_POST['colegiado_buscar'];
    if (isset($_POST['denunciante'])) {
        $denunciante = $_POST['denunciante'];
    } else {
        $denunciante = NULL;
    }
    $fechaReunionConsejo = $_POST['fechaReunionConsejo'];

    if ($estadoExpediente == "S"){
        if (isset($_POST['sumarianteTitular']) && $_POST['sumarianteTitular'] != ""){
            $idSumarianteTitular = $_POST['idSumarianteTitular'];
            $sumarianteTitularBuscar = $_POST['sumarianteTitular'];
        } else {
            $idSumarianteTitular = NULL;
            $sumarianteTitularBuscar = NULL;
        }
        if (isset($_POST['sumarianteSuplente']) && $_POST['sumarianteSuplente'] != ""){
            $idSumarianteSuplente = $_POST['idSumarianteSuplente'];
            $sumarianteSuplenteBuscar = $_POST['sumarianteSuplente'];
        } else {
            $idSumarianteSuplente = NULL;
            $sumarianteSuplenteBuscar = NULL;
        }
        if (isset($_POST['secretarioadhoc']) && $_POST['secretarioadhoc'] != ""){
            $idSecretarioadhoc = $_POST['idSecretarioadhoc'];
            $secretarioadhoc = $_POST['secretarioadhoc'];
        } else {
            $idSecretarioadhoc = NULL;
            $secretarioadhoc = NULL;
        }
    } else {
        $idSumarianteTitular = NULL;
        $idSumarianteSuplente = NULL;
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos en el expediente, verifique.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $eticaExpedienteLogic->agregarEticaExpediente($idColegiado, $caratula, $nroExpediente, $observaciones, $idSumarianteTitular, $idSumarianteSuplente, $estadoExpediente, $idSecretarioadhoc, $denunciante, $fechaReunionConsejo);
            break;
        case '2':
            $resultado = $eticaExpedienteLogic->borrarEticaExpediente($idEticaExpediente);
            break;
        case '3':
            $resultado = $eticaExpedienteLogic->editarEticaExpediente($idEticaExpediente, $idColegiado, $caratula, $nroExpediente, $observaciones, $idSumarianteTitular, $idSumarianteSuplente, $estadoExpediente, $idSecretarioadhoc, $denunciante, $fechaReunionConsejo);
            break;
        default:
            break;
    }

    if($resultado['estado']) {
        $tipoMensaje = 'alert alert-success';
    } else {
        $tipoMensaje = 'alert alert-danger';
    }
    $mensaje = $resultado['mensaje'];
}

?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../eticaExpediente_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../eticaExpediente_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente;?>">
            <input type="hidden"  name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente;?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiadoBuscar;?>">
            <input type="hidden"  name="caratula" id="caratula" value="<?php echo $caratula;?>">
            <input type="hidden"  name="nroExpediente" id="nroExpediente" value="<?php echo $nroExpediente;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

