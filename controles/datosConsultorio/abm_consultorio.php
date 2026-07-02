<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    if (isset($_POST['tipoConsultorio']) && $_POST['tipoConsultorio'] <> "") {
        $tipoConsultorio = $_POST['tipoConsultorio'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta tipoConsultorio - ";
    }
    if (isset($_POST['nombreConsultorio']) && $_POST['nombreConsultorio'] <> "") {
        $nombreConsultorio = $_POST['nombreConsultorio'];
    } else {
        $nombreConsultorio = NULL;
    }
    if (isset($_POST['cantidadConsultorios']) && $_POST['cantidadConsultorios'] <> "") {
        $cantidadConsultorios = $_POST['cantidadConsultorios'];
    } else {
        $cantidadConsultorios = NULL;
    }
    if (isset($_POST['calle']) && $_POST['calle'] <> "") {
        $calle = $_POST['calle'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta calle - ";
    }
    if (isset($_POST['lateral']) && $_POST['lateral'] <> "") {
        $lateral = $_POST['lateral'];
    } else {
        $lateral = NULL;
    }
    if (isset($_POST['numero']) && $_POST['numero'] <> "") {
        $numero = $_POST['numero'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta numero - ";
    }
    if (isset($_POST['piso']) && $_POST['piso'] <> "") {
        $piso = $_POST['piso'];
    } else {
        $piso = NULL;
    }
    if (isset($_POST['departamento']) && $_POST['departamento'] <> "") {
        $departamento = $_POST['departamento'];
    } else {
        $departamento = NULL;
    }
    if (isset($_POST['telefono']) && $_POST['telefono'] <> "") {
        $telefono = $_POST['telefono'];
    } else {
        $telefono = NULL;
    }
    if (isset($_POST['idLocalidad']) && $_POST['idLocalidad'] <> "") {
        $idLocalidad = $_POST['idLocalidad'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idLocalidad - ";
    }
    if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
        $observaciones = $_POST['observaciones'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta observaciones - ";
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion - ";
}

if ($continua) {
    $mesaEntradaLogic = new mesaEntradaLogic();
    switch ($accion) {
        case "AGREGAR":
            $resultado = $mesaEntradaLogic->agregarMesaEntradaConsultorio($idTipoMesaEntrada, $idColegiado, $idConsultorio, $idEspecialidad, $idEspecialidadAlternativa, $estadoMatricular, $estadoTesoreria);
            if ($resultado['estado']) {
                $idMesaEntrada = $resultado['datos']['idMesaEntrada'];
                $idMesaEntradaConsultorio = $resultado['datos']['idMesaEntradaConsultorio'];
            } else {
                $idMesaEntrada = NULL;
                $idMesaEntradaNota = NULL;
            }
            break;

        default:
            $continua = FALSE;
            break;
    }
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger';
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
        if ($accion == "BORRAR") {
        ?>
            <form name="myForm"  method="POST" action="../mesa_entrada_listado.php">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
                <?php
                if (isset($_GET['ingreso']) && ($_GET['ingreso'] == "FECHA" || $_GET['ingreso'] == "FECHA_TIPO" || $_GET['ingreso'] == "COLEGIADO" || $_GET['ingreso'] == "OTRO")) {
                    $accedePor = $_GET['ingreso'];
                } else {
                    $accedePor = NULL;
                }
                switch ($accedePor) {
                    case 'FECHA':
                        ?>
                        <input type="hidden" name="fechaIngreso" id="fechaIngreso" value="<?php echo $fechaIngreso ?>">
                        <?php
                        break;
                    
                    case 'FECHA_TIPO':
                        ?>
                        <input type="hidden" name="fechaIngreso" id="fechaIngreso" value="<?php echo $fechaIngreso ?>">
                        <input type="hidden" name="idTipoMesaEntradaSeleccionada" id="idTipoMesaEntradaSeleccionada" value="<?php echo $idTipoMesaEntrada ?>">
                        <?php
                        break;
                    
                    case 'COLEGIADO':
                        ?>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado ?>">
                        <?php
                        break;
                    
                    case 'OTRO':
                        ?>
                        <input type="hidden" name="idRemitente" id="idRemitente" value="<?php echo $idRemitente ?>">
                        <?php
                        break;
                    
                    default:
                        // code...
                        break;
                }
                ?>
            </form>
        <?php
        } else {
        ?>
            <form name="myForm"  method="POST" action="../mesa_entrada_habilitacion_consultorio.php?id=<?php echo $idMesaEntradaConsultorio; ?>">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
                <input type="hidden"  name="accion" id="accion" value="AGREGADA">
            </form>
        <?php 
        }
    } else {
    ?>
        <form name="myForm"  method="POST" action="../mesa_entrada_habilitacion_consultorio.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="idEspecialidadAlternativa" id="idEspecialidadAlternativa" value="<?php echo $idEspecialidadAlternativa;?>">
            <input type="hidden"  name="especialidadAlternativa_buscar" id="especialidadAlternativa_buscar" value="<?php echo $especialidadAlternativa_buscar;?>">
            <input type="hidden"  name="idEspecialidad" id="idEspecialidad" value="<?php echo $idEspecialidad;?>">
            <input type="hidden"  name="especialidad_buscar" id="especialidad_buscar" value="<?php echo $especialidad_buscar;?>">
            <input type="hidden"  name="idConsultorio" id="idConsultorio" value="<?php echo $idConsultorio;?>">
            <input type="hidden"  name="consultorio_buscar" id="consultorio_buscar" value="<?php echo $consultorio_buscar;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

