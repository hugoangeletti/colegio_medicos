<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../../dataAccess/eleccionesLocalidadesListasLogic.php');
require_once ('../../dataAccess/eleccionesLocalidadesIntegrantesLogic.php');
$eleccionesLocalidadesIntegrantesLogic = new eleccionesLocalidadesIntegrantesLogic();

$continua = TRUE;
$accion = $_POST['accion'];
if (isset($_POST['idElecciones']) && isset($_POST['idEleccionesLocalidad']) && $_POST['idEleccionesLocalidadLista']){
    $idElecciones = $_POST['idElecciones'];
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
    $idEleccionesLocalidadLista = $_POST['idEleccionesLocalidadLista'];
    
    if (isset($_POST['idEleccionesLocalidadListaIntegrante'])){
        $idEleccionesLocalidadListaIntegrante = $_POST['idEleccionesLocalidadListaIntegrante'];
    } else {
        $idEleccionesLocalidadListaIntegrante = NULL;
        $accion = 1;
    }
    
    if ($accion <> 2) {
        if (isset($_POST['colegiado_buscar']) && isset($_POST['idColegiado']) && isset($_POST['cargo'])) {
            $colegiado_buscar = $_POST['colegiado_buscar'];
            $idColegiado = $_POST['idColegiado'];
            $cargo = $_POST['cargo'];
            
//            $elColegiado = explode("-", $colegiado_buscar);
//            $matricula = $elColegiado[0];
//            $elApellidoNombre = explode("(", $elColegiado[1]);
//            $apellidoNombre = $elApellidoNombre[0];
            //verifico que el matriculado ya no esta en alguna lista de las elecciones vigente,
            //si cumple con la antiguedad, si esta al dia, si esta activo, si pertence a la misma zona de la
            //lista, si no estuvo en alguna lista en la ultima eleccion
            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado'] && $resColegiado['datos']) {
                $colegiado = $resColegiado['datos'];
                $matricula = $colegiado['matricula'];            
                $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
                $estadoMatricular = $colegiado['tipoEstado'];
                
                //obtengo el estado actual con tesoreria
                $periodoActual = $_SESSION['periodoActual'];
                $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, $periodoActual);
                if ($resEstadoTeso['estado']){
                    $codigo = $resEstadoTeso['codigoDeudor'];
                    $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigo);
                    if ($resEstadoTesoreria['estado']){
                        $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                    } else {
                        $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                    }
                } else {
                    $estadoTesoreria = $resEstadoTeso['mensaje'];
                }
                
                $aniosColegiado = calcular_edad($colegiado['fechaMatriculacion']);
                $laAntiguedad = explode(" ", $aniosColegiado);
                $edad = $laAntiguedad[0];
                $antiguedad = 'Meno de 2 años';
                if (2<= $edad && $edad<=10) {
                    $antiguedad = 'C (Más de 2 años)';
                } elseif ($edad>10) {
                    $antiguedad = 'T (Más de 10 años)';
                }
                
                
            }
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = "Faltan datos, verifique.";
        }
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Mal acceso.";
}

if ($continua){
    if (isset($_POST['orden'])) {
        $orden = $_POST['orden'];
    } else {
        $orden = $obtenerOrden($idEleccionesLocalidadLista, $cargo);
    }
    switch ($accion) 
    {
        case '1':
            $resultado = $eleccionesLocalidadesIntegrantesLogic->agregarEleccionesLocalidadesListaIntegrantes($idEleccionesLocalidadLista, $matricula, $apellidoNombre, $cargo, $orden);
            break;
        case '2':
            $resultado = $eleccionesLocalidadesIntegrantesLogic->borrarEleccionesLocalidadesListaIntegrante($idEleccionesLocalidadListaIntegrante);
            break;
        case '3':
            $resultado = $eleccionesLocalidadesIntegrantesLogic->editarEleccionesLocalidadesListaIntegrante($idEleccionesLocalidadListaIntegrante, $matricula, $apellidoNombre, $cargo, $orden);
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
        <form name="myForm"  method="POST" action="../elecciones_integrantes_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
            <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
            <input type="hidden" id="idEleccionesLocalidadLista" name="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../elecciones_listas_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
            <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
            <input type="hidden" id="idEleccionesLocalidadLista" name="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="cargo" id="cargo" value="<?php echo $cargo;?>">
            <input type="hidden"  name="orden" id="orden" value="<?php echo $orden;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

