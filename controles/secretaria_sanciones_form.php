<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoSancionLogic.php');
$colegiadoSancionLogic = new colegiadoSancionLogic();

if (isset($_GET['accion']) || isset($_POST['accion'])) {
    if (isset($_GET['accion'])) {
        $accion = $_GET['accion'];
    } else {
        if (isset($_POST['accion'])) {
            $accion = $_POST['accion'];
        } else {
            $accion = 1;
        }
    }
} else {
    $accion = 1;
}

$continua = TRUE;
if ($accion <> 1) {
    if (isset($_GET['idColegiadoSancion']) || isset($_POST['idColegiadoSancion'])) {
        if (isset($_GET['idColegiadoSancion'])) {
            $idColegiadoSancion = $_GET['idColegiadoSancion'];
        } else {
            $idColegiadoSancion = $_POST['idColegiadoSancion'];
        }
    } else {
        $resFalsosMedicos['clase'] = "alert alert-warning";
        $resFalsosMedicos['icono'] = "glyphicon glyphicon-exclamation-sign";
        $resFalsosMedicos['mensaje'] = "Datos mal ingresados";
        $continua = FALSE;
    }
} else {
    $idColegiadoSancion = NULL;
}

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $idColegiado = NULL;
}

switch ($accion) {
    case 1:
        $titulo = 'Nueva Sanción';
        $panel = 'panel-success';
        $textoBoton = 'Confirmar';
        $claseBoton = 'btn-success';
        $readOnly = '';
        break;

    case 2:
        $titulo = 'Eliminar Sanción';
        $panel = 'panel-danger';
        $textoBoton = 'Eliminar';
        $claseBoton = 'btn-danger';
        $readOnly = 'readonly=""';
        break;

    case 3:
        $titulo = 'Editar Sanción';
        $panel = 'panel-info';
        $claseBoton = 'btn-info';
        $textoBoton = 'Confimar';
        $readOnly = '';
        break;

    default:
        $titulo = 'Sanción - error de acceso';
        $panel = 'panel-default';
        $claseBoton = 'btn-default';
        $textoBoton = 'default';
        $readOnly = 'readonly=""';
        break;
}

?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Sanción</h4>
            </div>
            <div class="col-md-3 text-left">
                <?php 
                if (isset($idColegiado)) {
                    $action = 'colegiado_sanciones.php?idColegiado='.$idColegiado;
                } else {
                    $action = 'secretaria_sanciones.php';
                }
                ?>
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="<?php echo $action; ?>">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver a Sanciones</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
    if (isset($_POST['mensaje'])) {
    ?>
       <div class="ocultarMensaje"> 
           <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php
        $idColegiadoSancion = $_POST['idColegiadoSancion'];
        $apellidoNombre = $_POST['apellidoNombre'];
        $matricula = $_POST['matricula'];
        $ley = $_POST['ley'];
        $fechaDesde = $_POST['fechaDesde'];
        $fechaHasta = $_POST['fechaHasta'];
        $articulo = $_POST['articulo'];
        $codigo = $_POST['codigo'];
        $detalle = $_POST['detalle'];
        $distrito = $_POST['distrito'];
        $provincia = $_POST['provincia'];
        if (isset($_POST['idColegiado'])) {
            $idColegiado = $_POST['idColegiado'];
            $readOnlyColegiado = 'readonly=""';
        } else {
            $idColegiado = NULL;
            $readOnlyColegiado = '';
        }
    } else {
        if ($accion != 1) {
            $resSancion = $colegiadoSancionLogic->obtenerSancionPorId($idColegiadoSancion);
            if ($resSancion['estado']) {
                $sancion = $resSancion['datos'];
                $apellidoNombre = $sancion['apellidoNombre'];
                $matricula = $sancion['matricula'];
                $ley = $sancion['ley'];
                $fechaDesde = $sancion['fechaDesde'];
                $fechaHasta = $sancion['fechaHasta'];
                $articulo = $sancion['articulo'];
                $codigo = $sancion['codigo'];
                $detalle = $sancion['detalle'];
                $distrito = $sancion['distrito'];
                $provincia = $sancion['provincia'];
                if (isset($sancion['idColegiado'])) {
                    $idColegiado = $sancion['idColegiado'];
                    $readOnlyColegiado = 'readonly=""';
                } else {
                    $idColegiado = NULL;
                    if ($accion == 2) {
                        $readOnlyColegiado = 'readonly=""';
                    } else {
                        $readOnlyColegiado = '';
                    }
                }
            } else {
                $continua = FALSE;
            }
        } else {
            $ley = '';
            $fechaDesde = '';
            $fechaHasta = '';
            $articulo = '';
            $codigo = '';
            $detalle = '';
            $distrito = '';
            $provincia = '';
            if (isset($idColegiado)) {
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                if ($resColegiado['estado']) {
                    $colegiado = $resColegiado['datos'];
                    $apellidoNombre = trim($colegiado['apellido'].' '.  trim($colegiado['nombre']));
                    $matricula = $colegiado['matricula'];
                }
                $readOnlyColegiado = 'readonly=""';
            } else {
                $apellidoNombre = '';
                $matricula = '';
                $readOnlyColegiado = '';
            }
        }
    }
        if ($continua) {
        ?>
            <div class="row">
                <div class="col-md-12 text-center"><h4><b><?php echo $titulo; ?></b></h4></div>
            </div>
            <form id="datosSancion" autocomplete="off" name="datosSancion" method="POST" action="datosSanciones/abm_sanciones.php">
                <div class="row">
                    <div class="col-md-4">
                        <label>Apellido y Nombre: * </label>
                        <input class="form-control" autofocus autocomplete="OFF" type="text" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();"  id="apellidoNombre" name="apellidoNombre" value="<?php echo trim($apellidoNombre); ?>" placeholder="Ingrese Matrícula o Apellido del colegiao" required="" <?php echo $readOnlyColegiado; ?>/>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                    <div class="col-md-2">
                        <label>Matrícula Nº: </label>
                        <input class="form-control" type="number" id="matricula" name="matricula" value="<?php echo $matricula; ?>" <?php echo $readOnlyColegiado; ?> />
                    </div>
                    <div class="col-md-2">
                        <label>Ley: </label>
                        <input class="form-control" type="text" id="ley" name="ley" value="<?php echo trim($ley); ?>" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label>Artículo: </label>
                        <input class="form-control" type="text" id="articulo" name="articulo" value="<?php echo trim($articulo); ?>" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-2">
                        <label>Código: </label>
                        <input class="form-control" type="text" id="codigo" name="codigo" value="<?php echo trim($codigo); ?>" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-3">
                        <label>Fecha Desde:  *</label>
                        <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" value="<?php echo $fechaDesde;?>" required <?php echo $readOnly; ?>>
                    </div>
                    <div class="col-md-3">
                        <label>Fecha Hasta: </label>
                        <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" value="<?php echo $fechaHasta;?>" <?php echo $readOnly; ?>>
                    </div>
                    <div class="col-md-3">
                        <label>Distrito: </label>
                        <input class="form-control" type="text" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" id="distrito" name="distrito" value="<?php echo $distrito; ?>" <?php echo $readOnly; ?>/>
                    </div>
                    <div class="col-md-3">
                        <label>Provincia: </label>
                        <input class="form-control" type="text" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" id="provincia" name="provincia" value="<?php echo $provincia; ?>" <?php echo $readOnly; ?>/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <label>Observaciones </label>
                        <textarea class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" name="detalle" id="detalle" rows="5" <?php echo $readOnly; ?> ><?php echo $detalle; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" ><?php echo $textoBoton; ?> </button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        <input type="hidden" name="idColegiadoSancion" id="idColegiadoSancion" value="<?php echo $idColegiadoSancion; ?>" />
                    </div>
                </div>    
            </form>
        <?php
        } else {
        ?>
            <div class="col-md-12">
                <div class="<?php echo $resSancion['clase']; ?>" role="alert">
                    <span class="<?php echo $resSancion['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resSancion['mensaje']; ?></strong></span>
                </div>        
            </div>
        <?php 
        }
        ?>
    </div>    
</div>
<?php
require_once '../html/footer.php';
