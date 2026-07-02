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

    if (isset($_POST['idMesaEntradaConsultorio']) && $_POST['idMesaEntradaConsultorio'] <> "") {
        //da de alta la nueva solicitud de especialista
        $idMesaEntradaConsultorio = $_POST['idMesaEntradaConsultorio'];
    } else {
        if ($accion == "AGREGAR") {
            $idMesaEntradaConsultorio = NULL;
        } else {
            $continua = FALSE;
            $mensaje = "Falta idMesaEntradaConsultorio - ";
        }
    } 
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idColegiado - ";
    }
    if (isset($_POST['colegiado_buscar']) && $_POST['colegiado_buscar'] <> "") {
        $colegiado_buscar = $_POST['colegiado_buscar'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta colegiado_buscar - ";
    }
    if (isset($_POST['estadoMatricular']) && $_POST['estadoMatricular'] <> "") {
        $estadoMatricular = $_POST['estadoMatricular'];
    } else {
        $continua = FALSE;
        $mensaje = "Falta estadoMatricular - ";
    } 
    if (isset($_POST['codigoDeudor']) && $_POST['codigoDeudor'] <> "") {
        $estadoTesoreria = $_POST['codigoDeudor'];
    } else {
        $continua = FALSE;
        $mensaje = "Falta estadoTesoreria - ";
    } 
    if (isset($_POST['idEspecialidad']) && $_POST['idEspecialidad'] <> "") {
        $idEspecialidad = $_POST['idEspecialidad'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idEspecialidad - ";
    }
    if (isset($_POST['especialidad_buscar']) && $_POST['especialidad_buscar'] <> "") {
        $especialidad_buscar = $_POST['especialidad_buscar'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta especialidad_buscar - ";
    }
    if (isset($_POST['idEspecialidadAlternativa']) && $_POST['idEspecialidadAlternativa'] <> "") {
        $idEspecialidadAlternativa = $_POST['idEspecialidadAlternativa'];
    } else {
        $idEspecialidadAlternativa = NULL;
    }
    if (isset($_POST['especialidadAlternativa_buscar']) && $_POST['especialidadAlternativa_buscar'] <> "") {
        $especialidadAlternativa_buscar = $_POST['especialidadAlternativa_buscar'];
    } else {
        $especialidadAlternativa_buscar = NULL;
    }

    //datos del consultorio
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
        $cantidadConsultorios = 1;
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
        $numero = NULL;
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
        $continua = FALSE;
        $mensaje .= "Falta telefono - ";
    }
    if (isset($_POST['localidad_buscar']) && $_POST['localidad_buscar'] <> "") {
        $localidad_buscar = $_POST['localidad_buscar'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta localidad_buscar - ";
    }
    if (isset($_POST['idLocalidad']) && $_POST['idLocalidad'] <> "") {
        $idLocalidad = $_POST['idLocalidad'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idLocalidad - ";
    }
    if (isset($_POST['codigoPostal']) && $_POST['codigoPostal'] <> "") {
        $codigoPostal = $_POST['codigoPostal'];
    } else {
        $codigoPostal = NULL;
    }
    if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
        $observaciones = $_POST['observaciones'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta dias y horarios - ";
    }

} else {
    if (isset($_GET['borrar'])) {
        $accion = "BORRAR";
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idMesaEntrada = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idMesaEntrada - ";
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion - ";
    }
}

if ($continua) {
    $mesaEntradaLogic = new mesaEntradaLogic();
    switch ($accion) {
        case "AGREGAR":
            //continuar con el alta       
            $idTipoMesaEntrada = 4; //habilitacion consultorio
            $tipoRemitente = 'C'; //colegiado
            $idRemitente = NULL;
            $datosTipoMesaEntrada = array(
                                    'idEspecialidad' => $idEspecialidad,
                                    'idEspecialidadAlternativa' => $idEspecialidadAlternativa,
                                    'tipoConsultorio' => $tipoConsultorio,
                                    'nombreConsultorio' => $nombreConsultorio,
                                    'cantidadConsultorios' => $cantidadConsultorios,
                                    'calle' => $calle,
                                    'lateral' => $lateral,
                                    'numero' => $numero,
                                    'piso' => $piso,
                                    'departamento' => $departamento,
                                    'telefono' => $telefono,
                                    'idLocalidad' => $idLocalidad,
                                    'codigoPostal' => $codigoPostal,
                                    'observaciones' => $observaciones
                                );
            $resultado = $mesaEntradaLogic->agregarMesaEntrada($idTipoMesaEntrada, $tipoRemitente, $idColegiado, $idRemitente, $estadoMatricular, $estadoTesoreria, NULL, $datosTipoMesaEntrada);
            break;

        case "EDITAR":
            $resMesaEntrada = $mesaEntradaLogic->obtenerMesaEntradaConsultorioPorId($idMesaEntradaConsultorio, NULL);
            if ($resMesaEntrada['estado']) {
                $datosAnteriores = $resMesaEntrada['datos'];
            } else {
                $datosAnteriores = array();
            }
            $resultado = $mesaEntradaLogic->modificarMesaEntradaConsultorio($idMesaEntradaConsultorio, $idEspecialidad, $idEspecialidadAlternativa, $tipoConsultorio, $nombreConsultorio, $cantidadConsultorios, $calle, $lateral, $numero, $piso, $departamento, $telefono, $idLocalidad, $codigoPostal, $datosAnteriores);
            break;

        case "BORRAR":
            $resMesaEntrada = $mesaEntradaLogic->obtenerMesaEntradaPorId($idMesaEntrada);
            if ($resMesaEntrada['estado']) {
                $datosAnteriores = $resMesaEntrada['datos'];
            } else {
                $datosAnteriores = array();
            }

            $resultado = $mesaEntradaLogic->borrarMesaEntradaConsultorio($idMesaEntrada, $datosAnteriores);
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
        if ($accion == 'BORRAR' || $accion == 'EDITAR') {
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
            $idMesaEntradaConsultorio = $resultado['idMesaEntradaConsultorio'];
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
            <input type="hidden"  name="tipoConsultorio" id="tipoConsultorio" value="<?php echo $tipoConsultorio;?>">
            <input type="hidden"  name="calle" id="calle" value="<?php echo $calle;?>">
            <input type="hidden"  name="lateral" id="lateral" value="<?php echo $lateral;?>">
            <input type="hidden"  name="numero" id="numero" value="<?php echo $numero;?>">
            <input type="hidden"  name="piso" id="piso" value="<?php echo $piso;?>">
            <input type="hidden"  name="departamento" id="departamento" value="<?php echo $departamento;?>">
            <input type="hidden"  name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad;?>">
            <input type="hidden"  name="localidad_buscar" id="localidad_buscar" value="<?php echo $localidad_buscar;?>">
            <input type="hidden"  name="codigoPostal" id="codigoPostal" value="<?php echo $codigoPostal;?>">
            <input type="hidden"  name="telefono" id="telefono" value="<?php echo $telefono;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
        </form>
    <?php
    }
    ?>
</body>

