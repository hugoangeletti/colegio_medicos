<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoSancionLogic.php');
$colegiadoSancionLogic = new colegiadoSancionLogic();

if (isset($_POST['idColegiadoSancion'])) {
    $idColegiadoSancion = $_POST['idColegiadoSancion'];
    $accion = $_POST['accion'];
} else {
    $idColegiadoSancion = NULL;
    $accion = 1;
}

if (isset($_POST['estadoSancion'])) {
    $estadoSancion = $_POST['estadoSancion'];
} else {
    $estadoSancion = 'A';
}

$continua = TRUE;
if (isset($_POST['apellidoNombre']) && isset($_POST['fechaDesde'])) {
    $apellidoNombre = $_POST['apellidoNombre'];
    $fechaDesde = $_POST['fechaDesde'];
    if (isset($_POST['matricula']) && $_POST['matricula'] <> '') {
        $matricula = $_POST['matricula'];
    } else {
        $matricula = NULL;
    }
    if (isset($_POST['ley']) && $_POST['ley'] <> '') {
        $ley = $_POST['ley'];
    } else {
        $ley = NULL;
    }
    if (isset($_POST['articulo']) && $_POST['articulo'] <> '') {
        $articulo = $_POST['articulo'];
    } else {
        $articulo = NULL;
    }
    if (isset($_POST['codigo']) && $_POST['codigo'] <> '') {
        $codigo = $_POST['codigo'];
    } else {
        $codigo = NULL;
    }
    if (isset($_POST['fechaHasta']) && $_POST['fechaHasta'] <> '') {
        $fechaHasta = $_POST['fechaHasta'];
    } else {
        $fechaHasta = NULL;
    }
    if (isset($_POST['distrito']) && $_POST['distrito'] <> '') {
        $distrito = $_POST['distrito'];
    } else {
        $distrito = NULL;
    }
    if (isset($_POST['provincia']) && $_POST['provincia'] <> '') {
        $provincia = $_POST['provincia'];
    } else {
        $provincia = NULL;
    }
    if (isset($_POST['detalle']) && $_POST['detalle'] <> '') {
        $detalle = $_POST['detalle'];
    } else {
        $detalle = NULL;
    }
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> '') {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
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
            $resultado = $colegiadoSancionLogic->agregarSancion($matricula, $apellidoNombre, $ley, $fechaDesde, $fechaHasta, $articulo, $codigo, $detalle, $distrito, $provincia, $idColegiado);
            break;
        case '3':
            $resultado = $colegiadoSancionLogic->editarSancion($idColegiadoSancion, $matricula, $apellidoNombre, $ley, $fechaDesde, $fechaHasta, $articulo, $codigo, $detalle, $distrito, $provincia, $idColegiado, 'A');
            break;
        case '2':
            $resultado = $colegiadoSancionLogic->editarSancion($idColegiadoSancion, $matricula, $apellidoNombre, $ley, $fechaDesde, $fechaHasta, $articulo, $codigo, $detalle, $distrito, $provincia, $idColegiado, 'B');
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
        if (isset($idColegiado)) {
            $action = '../colegiado_sanciones.php?idColegiado='.$idColegiado;
        } else {
            $action = '../secretaria_sanciones.php';
        }
    ?>
        <form name="myForm"  method="POST" action="<?php echo $action; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="estadoSancion" id="estadoSancion" value="<?php echo $estadoSancion;?>">
        </form>
    <?php
    } else {
        if (isset($idColegiado)) {
            $action = '../secretaria_sanciones_form.php?idColegiado='.$idColegiado;
        } else {
            $action = '../secretaria_sanciones_form.php';
        }
    ?>
        <form name="myForm"  method="POST" action="<?php echo $action; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="estadoSancion" id="estadoSancion" value="<?php echo $estadoSancion;?>">
            <input type="hidden"  name="idColegiadoSancion" id="idColegiadoSancion" value="<?php echo $idColegiadoSancion;?>">
            <input type="hidden"  name="apellidoNombre" id="apellidoNombre" value="<?php echo $apellidoNombre;?>">
            <input type="hidden"  name="matricula" id="matricula" value="<?php echo $matricula;?>">
            <input type="hidden"  name="ley" id="ley" value="<?php echo $ley;?>">
            <input type="hidden"  name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde;?>">
            <input type="hidden"  name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta;?>">
            <input type="hidden"  name="articulo" id="articulo" value="<?php echo $articulo;?>">
            <input type="hidden"  name="codigo" id="codigo" value="<?php echo $codigo;?>">
            <input type="hidden"  name="detalle" id="detalle" value="<?php echo $detalle;?>">
            <input type="hidden"  name="distrito" id="distrito" value="<?php echo $distrito;?>">
            <input type="hidden"  name="provincia" id="provincia" value="<?php echo $provincia;?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

