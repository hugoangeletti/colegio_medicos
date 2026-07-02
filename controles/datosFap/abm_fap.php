<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/fap_pdo.php');
$continua = TRUE;
$mensaje = "";
$fapLogic = new fap_pdo();

if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    //si es una modificacion, verifico que venga el idSapCaratula
    if ($accion == 'editar' || $accion == 'editar_consulta') {
        if (isset($_POST['idSapCaratula']) && $_POST['idSapCaratula'] <> "") {
            $idSapCaratula = $_POST['idSapCaratula'];
            $resSapCartula = $fapLogic->obtenerSapCaratulaPorId($idSapCaratula);
            if ($resSapCartula['estado']) {
                $sapCaratula = $resSapCartula['datos'];
                $idSapEstado = $sapCaratula['idSapEstado'];
                $estado = $sapCaratula['estado'];
                if ($accion == 'editar') {
                    $accion = 'editar_causa';
                }
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idSapCaratula - ";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        $idSapCaratula = NULL;
    }

    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idColegiado - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['nombreCausa']) && $_POST['nombreCausa'] <> "") {
        $nombreCausa = $_POST['nombreCausa'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta nombreCausa - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
        $observaciones = $_POST['observaciones'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta observaciones - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['domicilioReal']) && $_POST['domicilioReal'] <> "") {
        $domicilioReal = $_POST['domicilioReal'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta domicilioReal - ";
        $tipoMensaje = 'alert alert-danger';
    }
    $telefonoParticular = NULL;
    if (isset($_POST['telefonoParticular']) && $_POST['telefonoParticular'] <> "") {
        $telefonoParticular = $_POST['telefonoParticular'];
    }
    $celular = NULL;
    if (isset($_POST['celular']) && $_POST['celular'] <> "") {
        $celular = $_POST['celular'];
    }
    if (isset($_POST['mail']) && $_POST['mail'] <> "") {
        $mail = $_POST['mail'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta mail - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['recepciono']) && $_POST['recepciono'] <> "") {
        $recepciono = $_POST['recepciono'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta recepciono - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['idSapTipoTramite']) && $_POST['idSapTipoTramite'] <> "") {
        $idSapTipoTramite = $_POST['idSapTipoTramite'];
        switch ($idSapTipoTramite) {
            case fap_pdo::TIPO_TRAMITE_MEDIACION:
                $estado = 'M';
                break;
                
            case fap_pdo::TIPO_TRAMITE_LITIGAR_SIN_GASTO:
                $estado = 'G';
                break;
                
            case fap_pdo::TIPO_TRAMITE_CAUSA:
                $estado = 'A';
                break;
                
            default:
                $estado = 'X';
                break;
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idSapTipoTramite - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['domicilioProfesional']) && $_POST['domicilioProfesional'] <> "") {
        $domicilioProfesional = $_POST['domicilioProfesional'];
    } else {
        $domicilioProfesional = NULL;
    }
    if ($accion <> 'agregar_consulta' && $accion <> 'editar_consulta') {
        if (isset($_POST['idTipoCausa']) && $_POST['idTipoCausa'] <> "") {
            $idTipoCausa = $_POST['idTipoCausa'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idTipoCausa - ";
            $tipoMensaje = 'alert alert-danger';
        }
        if (isset($_POST['idSapEstado']) && $_POST['idSapEstado'] <> "") {
            $idSapEstado = $_POST['idSapEstado'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idSapEstado - ";
            $tipoMensaje = 'alert alert-danger';
        }
        $caratulaDefinitiva = NULL;
        if (isset($_POST['caratulaDefinitiva']) && $_POST['caratulaDefinitiva'] <> "") {
            $caratulaDefinitiva = $_POST['caratulaDefinitiva'];
        }
        $idJuzgado = NULL;
        if (isset($_POST['idJuzgado']) && $_POST['idJuzgado'] <> "") {
            $idJuzgado = $_POST['idJuzgado'];
        }
        $idDepartamentoJudicial = NULL;
        if (isset($_POST['idDepartamentoJudicial']) && $_POST['idDepartamentoJudicial'] <> "") {
            $idDepartamentoJudicial = $_POST['idDepartamentoJudicial'];
        }
        $fechaHecho = NULL;
        if (isset($_POST['fechaHecho']) && $_POST['fechaHecho'] <> "") {
            $fechaHecho = $_POST['fechaHecho'];
        }
        $lugarHecho = NULL;
        if (isset($_POST['lugarHecho']) && $_POST['lugarHecho'] <> "") {
            $lugarHecho = $_POST['lugarHecho'];
        }
        $ambito = NULL;
        if (isset($_POST['ambito']) && $_POST['ambito'] <> "") {
            $ambito = $_POST['ambito'];
        }
        $domicilioHecho = NULL;
        if (isset($_POST['domicilioHecho']) && $_POST['domicilioHecho'] <> "") {
            $domicilioHecho = $_POST['domicilioHecho'];
        }
        $telefonoHecho = NULL;
        if (isset($_POST['telefonoHecho']) && $_POST['telefonoHecho'] <> "") {
            $telefonoHecho = $_POST['telefonoHecho'];
        }
        $fechaNotificacion = NULL;
        if (isset($_POST['fechaNotificacion']) && $_POST['fechaNotificacion'] <> "") {
            $fechaNotificacion = $_POST['fechaNotificacion'];
        }
        $lugarNotificacion = NULL;
        if (isset($_POST['lugarNotificacion']) && $_POST['lugarNotificacion'] <> "") {
            $lugarNotificacion = $_POST['lugarNotificacion'];
        }
        $recepcion = NULL;
        if (isset($_POST['recepcion']) && $_POST['recepcion'] <> "") {
            $recepcion = $_POST['recepcion'];
        }
        $especialidad = NULL;
        if (isset($_POST['especialidad']) && $_POST['especialidad'] <> "") {
            $especialidad = $_POST['especialidad'];
        }
        if (isset($_POST['idSapCondicion']) && $_POST['idSapCondicion'] <> "") {
            $idSapCondicion = $_POST['idSapCondicion'];
        } else {
            $continua = FALSE;
            $mensaje .= 'Falta idSapCondicion - ';
        }
        if (isset($_POST['inscriptoDistrito']) && $_POST['inscriptoDistrito'] <> "") {
            $inscriptoDistrito = $_POST['inscriptoDistrito'];
        } else {
            $continua = FALSE;
            $mensaje .= 'Falta inscriptoDistrito - ';
        }
        $tieneCobertura = NULL;
        if (isset($_POST['tieneCobertura']) && $_POST['tieneCobertura'] <> "") {
            $tieneCobertura = $_POST['tieneCobertura'];
        }
        $nombreCobertura = NULL;
        if (isset($_POST['nombreCobertura']) && $_POST['nombreCobertura'] <> "") {
            $nombreCobertura = $_POST['nombreCobertura'];
        }
        $coberturaDesde = NULL;
        if (isset($_POST['coberturaDesde']) && $_POST['coberturaDesde'] <> "") {
            $coberturaDesde = $_POST['coberturaDesde'];
        }
        $abogados = NULL;
        if (isset($_POST['abogados']) && $_POST['abogados'] <> "") {
            $abogados = $_POST['abogados'];
        }
        $conCedula = NULL;
        if (isset($_POST['conCedula']) && $_POST['conCedula'] <> "") {
            $conCedula = $_POST['conCedula'];
        }
        $conFotoDemanda = NULL;
        if (isset($_POST['conFotoDemanda']) && $_POST['conFotoDemanda'] <> "") {
            $conFotoDemanda = $_POST['conFotoDemanda'];
        }
    } else {
        $estado = 'C';
    }
    $litigioSinGasto = NULL;
    if (isset($_POST['litigioSinGasto']) && $_POST['litigioSinGasto'] <> "") {
        $litigioSinGasto = $_POST['litigioSinGasto'];
    }
    $fechaIngreso = date('Y-m-d');
    $fechaRecepcion = date('Y-m-d');
    $tipoSistema = 'Nuevo';
    $edad = $_POST['edad'];
    $sexo = $_POST['sexo'];
    $matricula = $_POST['matricula'];
} else {
    if (isset($_GET['cambiar_causa'])) {
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idSapCaratula = $_GET['id'];
            $accion = 'cambiar_causa';
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idSapCaratula - ";
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Ingreso incorrecto - ";
    }
}

if ($continua) {
    if (isset($idSapCaratula)) {
        $resDetalle = $fapLogic->obtenerSapCaratulaPorId($idSapCaratula);
        if ($resDetalle['estado']) {
            $datosAnteriores = $resDetalle['datos'];
        } else {
            $continua = FALSE;
            $mensaje .= $resDetalle['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    switch ($accion) {
        case 'agregar_consulta':
            $resultado = $fapLogic->agregarFapConsulta($matricula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $estado, $tipoSistema, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $mail, $recepciono, $observaciones, $idColegiado, $idSapTipoTramite);
            break;

        case 'agregar_causa':
        case 'agregar_mediacion':
        case 'agregar_litigar_sin_gasto':
            $resultado = $fapLogic->agregarFapCausa($matricula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $abogados, $idJuzgado, $idTipoCausa, $idDepartamentoJudicial, $estado, $tipoSistema, $fechaHecho, $lugarHecho, $ambito, $especialidad, $caratulaDefinitiva, $domicilioHecho, $telefonoHecho, $fechaNotificacion, $lugarNotificacion, $recepcion, $tieneCobertura, $nombreCobertura, $coberturaDesde, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $celular, $mail, $conCedula, $conFotoDemanda, $recepciono, $observaciones, $idColegiado, $inscriptoDistrito, $idSapTipoTramite, $idSapEstado, $idSapCondicion);
            break;

        case 'editar_consulta':
            $resultado = $fapLogic->editarFapConsulta($idSapCaratula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $celular, $mail, $recepciono, $observaciones, $matricula, $idColegiado, $datosAnteriores);
            break;

        case 'editar_causa':
        case 'editar_mediacion':
        case 'editar_litigar_sin_gasto':
            $resultado = $fapLogic->editarFapCaratula($idSapCaratula, $fechaRecepcion, $fechaIngreso, $nombreCausa, $abogados, $idJuzgado, $idTipoCausa, $idDepartamentoJudicial, $estado, $tipoSistema, $fechaHecho, $lugarHecho, $ambito, $especialidad, $caratulaDefinitiva, $domicilioHecho, $telefonoHecho, $fechaNotificacion, $lugarNotificacion, $recepcion, $tieneCobertura, $nombreCobertura, $coberturaDesde, $edad, $sexo, $domicilioReal, $domicilioProfesional, $telefonoParticular, $celular, $mail, $conCedula, $conFotoDemanda, $recepciono, $observaciones, $inscriptoDistrito, $idSapEstado, $idSapCondicion, $matricula, $idColegiado, $datosAnteriores);
            break;

        case 'anular':
            $resultado = $fapLogic->borrarFapCaratula($idSapCaratula);
            break;

        case 'cambiar_causa':
            $resultado = $fapLogic->cambiarAConsultaFapCaratula($idSapCaratula, $datosAnteriores);
            break;

        default:
            break;
    }
} else {
    $resultado['clase'] = "alert alert-danger";
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = "";
    $resultado['estado'] = FALSE;
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
        if ($accion == 'agregar_causa') {
            $idSapCaratula = $resultado['idSapCaratula'];
            ?>
            <form name="myForm"  method="POST" action="../fap_imprimir_caratula.php?id=<?php echo $idSapCaratula; ?>"></form>
        <?php
        } else {
        ?>
            <form name="myForm"  method="POST" action="../fap_listado.php">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            </form>
        <?php
        }
    } else {
    ?>
        <form name="myForm"  method="POST" action="../fap_form.php?idColegiado=<?php echo $idColegiado; ?>&accion=<?php echo $accion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion; ?>">
        </form>
    <?php
    }
    ?>
</body>

