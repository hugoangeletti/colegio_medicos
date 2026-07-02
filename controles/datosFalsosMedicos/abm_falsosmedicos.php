<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/falsosMedicosLogic.php');
$falsosMedicosLogic = new falsosMedicosLogic();

if (isset($_POST['idFalsosMedicos'])) {
    $idFalsoMedicos = $_POST['idFalsosMedicos'];
    $accion = $_POST['accion'];
} else {
    $idFalsoMedicos = NULL;
    $accion = 1;
}

$continua = TRUE;
if (isset($_POST['apellido']) && isset($_POST['nombre']) && isset($_POST['fechaDenuncia']) && isset($_POST['remitido'])) {
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    if (isset($_POST['nroDocumento'])) {
        $nroDocumento = $_POST['nroDocumento'];
    } else {
        $nroDocumento = NULL;
    }
    if (isset($_POST['matricula'])) {
        $matricula = $_POST['matricula'];
    } else {
        $matricula = NULL;
    }
    if (isset($_POST['origenMatricula'])) {
        $origenMatricula = $_POST['origenMatricula'];
    } else {
        $origenMatricula = NULL;
    }
    $fechaDenuncia = $_POST['fechaDenuncia'];
    $remitido = $_POST['remitido'];
    if (isset($_POST['observaciones'])) {
        $observaciones = $_POST['observaciones'];
    } else {
        $observaciones = NULL;
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos, verifique.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $falsosMedicosLogic->agregarFalsosMedicos($apellido, $nombre, $nroDocumento, $matricula, $origenMatricula, $fechaDenuncia, $observaciones, $remitido);
            break;
        case '3':
            $resultado = $falsosMedicosLogic->editarFalsosMedicos($idFalsoMedicos, $apellido, $nombre, $nroDocumento, $matricula, $origenMatricula, $fechaDenuncia, $observaciones, $remitido, 'A');
            break;
        case '2':
            $resultado = $falsosMedicosLogic->editarFalsosMedicos($idFalsoMedicos, $apellido, $nombre, $nroDocumento, $matricula, $origenMatricula, $fechaDenuncia, $observaciones, $remitido, 'B');
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
        <form name="myForm"  method="POST" action="../secretaria_falsosmedicos.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../secretaria_falsosmedicos_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idFalsoMedicos" id="idFalsoMedicos" value="<?php echo $idFalsoMedicos;?>">
            <input type="hidden"  name="apellido" id="apellido" value="<?php echo $apellido;?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="nroDocumento" id="nroDocumento" value="<?php echo $nroDocumento;?>">
            <input type="hidden"  name="matricula" id="matricula" value="<?php echo $matricula;?>">
            <input type="hidden"  name="origenMatricula" id="origenMatricula" value="<?php echo $origenMatricula;?>">
            <input type="hidden"  name="fechaDenuncia" id="fechaDenuncia" value="<?php echo $fechaDenuncia;?>">
            <input type="hidden"  name="remitidio" id="remitido" value="<?php echo $remitido;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

