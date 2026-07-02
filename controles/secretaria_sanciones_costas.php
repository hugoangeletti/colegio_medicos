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

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    if (isset($_GET['idColegiadoSancion']) || isset($_POST['idCostas'])) {
        $idCostas = $_GET['idCostas'];
    } else {
        $idCostas = $_POST['idCostas'];
    }
} else {
    $accion = 1;
    $idCostas = NULL;
}
$continua = TRUE;
if (isset($_GET['idColegiadoSancion']) || isset($_POST['idColegiadoSancion'])) {
    if (isset($_GET['idColegiadoSancion'])) {
        $idColegiadoSancion = $_GET['idColegiadoSancion'];
    } else {
        $idColegiadoSancion = $_POST['idColegiadoSancion'];
    }
    $resSancion = $colegiadoSancionLogic->obtenerSancionPorId($idColegiadoSancion);
    if ($resSancion['estado']) {
        $sancion = $resSancion['datos'];
        $apellidoNombre = $sancion['apellidoNombre'];
        $matricula = $sancion['matricula'];
        $fechaDesde = $sancion['fechaDesde'];
        $fechaHasta = $sancion['fechaHasta'];
        $detalle = $sancion['detalle'];
    } else {
        $continua = FALSE;
    }
} else {
    $resFalsosMedicos['clase'] = "alert alert-warning";
    $resFalsosMedicos['icono'] = "glyphicon glyphicon-exclamation-sign";
    $resFalsosMedicos['mensaje'] = "Datos mal ingresados";
    $continua = FALSE;
}

switch ($accion) {
    case 1:
        $titulo = 'Agregar Costas a la Sanción';
        $panel = 'panel-success';
        $textoBoton = 'Confirmar';
        $claseBoton = 'btn-success';
        $readOnly = '';
        break;

    case 2:
        $titulo = 'Eliminar Costas a la Sanción';
        $panel = 'panel-danger';
        $textoBoton = 'Eliminar';
        $claseBoton = 'btn-danger';
        $readOnly = 'readonly=""';
        break;

    case 3:
        $titulo = 'Editar Costas a la Sanción';
        $panel = 'panel-info';
        $claseBoton = 'btn-info';
        $textoBoton = 'Confimar';
        $readOnly = '';
        break;

    default:
        $titulo = 'Costas a la Sanción - error de acceso';
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
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="secretaria_sanciones.php">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver a Sanciones</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if ($continua) {
            if (isset($_POST['mensaje'])) {
            ?>
               <div class="ocultarMensaje"> 
                   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
               </div>
             <?php
                $cantidadGalenos = $_POST['cantidadGalenos'];
                $fechaVencimiento = $_POST['fechaVencimiento'];
                $estado = $_POST['estado'];
            } else {
                if ($accion != 1) {
                    $resSancionCostas = $colegiadoSancionLogic->obtenerCostasPorId($idCostas);
                    if ($resSancionCostas['estado']) {
                        $sancionCostas = $resSancionCostas['datos'];
                        $cantidadGalenos = $sancionCostas['cantidadGalenos'];
                        $fechaVencimiento = $sancionCostas['fechaVencimiento'];
                        $estado = $sancionCostas['estado'];
                    } else {
                        $cantidadGalenos = '';
                        $fechaVencimiento = '';
                        $estado = 'A';
                    }
                } else {
                    $cantidadGalenos = '';
                    $fechaVencimiento = '';
                    $estado = 'A';
                }
            }
            ?>
            <div class="row">
                <div class="col-md-12 text-center"><h4><b><?php echo $titulo; ?></b></h4></div>
            </div>
            <form id="datosSancion" autocomplete="off" name="datosSancion" method="POST" action="datosSanciones/abm_costas.php">
                <div class="row">
                    <div class="col-md-4">
                        <label>Cantidad de Galenos: *</label>
                        <input class="form-control" autofocus="" type="number" id="cantidadGalenos" name="cantidadGalenos" value="<?php echo $cantidadGalenos; ?>" <?php echo $readOnly; ?> />
                    </div>
                    <div class="col-md-3">
                        <label>Fecha de Vencimiento:  *</label>
                        <input type="date" class="form-control" id="fechaVencimiento" name="fechaVencimiento" value="<?php echo $fechaVencimiento;?>" required <?php echo $readOnly; ?>>
                    </div>
                    <div class="col-md-3">
                        <label>Estado: *</label>
                        <select class="form-control" id="estado" name="estado" required="" <?php echo $readOnly; ?>>
                            <option value="A" <?php if($estado == 'A') { ?> selected <?php } ?>>A pagar</option>
                            <option value="B" <?php if($estado == 'B') { ?> selected <?php } ?>>Anulada</option>
                        </select>            
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" ><?php echo $textoBoton; ?> </button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        <input type="hidden" name="idColegiadoSancion" id="idColegiadoSancion" value="<?php echo $idColegiadoSancion; ?>" />
                        <input type="hidden" name="idCostas" id="idCostas" value="<?php echo $idCostas; ?>" />
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
