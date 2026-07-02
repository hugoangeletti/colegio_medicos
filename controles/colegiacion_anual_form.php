<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiacionAnualLogic.php');
$colegiacionAnualLogic = new colegiacionAnualLogic();

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
} else {
    $accion = 1;
}

$continua = TRUE;
if ($accion <> 1) {
    if (isset($_POST['idColegiacionAnual']) && $_POST['idColegiacionAnual']) {
        $idColegiacionAnual = $_POST['idColegiacionAnual'];
    } else {
        $resFalsosMedicos['clase'] = "alert alert-warning";
        $resFalsosMedicos['icono'] = "glyphicon glyphicon-exclamation-sign";
        $resFalsosMedicos['mensaje'] = "Datos mal ingresados";
        $continua = FALSE;
    }
} else {
    $idColegiacionAnual = NULL;
}

switch ($accion) {
    case 1:
        $titulo = 'Nuevo Valor y Vencimiento';
        $panel = 'panel-info';
        $textoBoton = 'Confirmar';
        $claseBoton = 'btn-info';
        $readOnly = '';
        break;

    case 2:
        $titulo = 'Eliminar Valor y Vencimiento';
        $panel = 'panel-danger';
        $textoBoton = 'Eliminar';
        $claseBoton = 'btn-danger';
        $readOnly = 'readonly=""';
        break;

    case 3:
        $titulo = 'Editar Valor y Vencimiento';
        $panel = 'panel-info';
        $claseBoton = 'btn-info';
        $textoBoton = 'Confimar';
        $readOnly = '';
        break;

    default:
        $titulo = 'Valor y Vencimiento - error de acceso';
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
                <h4>Valores y Vencimientos de Colegiación</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiacion_anual_lista.php">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver al listado</button>
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
            $idColegiacionAnual = $_POST['idColegiacionAnual'];
            $periodo = $_POST['periodo'];
            $cuotas = $_POST['cuotas'];
            $antiguedad = $_POST['antiguedad'];
            $importe = $_POST['importe'];
            $vencimientoCuotaUno = $_POST['vencimientoCuotaUno'];
            $pagoTotal = $_POST['pagoTotal'];
            $vencimientoPagoTotal = $_POST['vencimientoPagoTotal'];
        } else {
            if ($accion != 1) {
                $resColegiacion = $colegiacionAnualLogic->obtenerColegiacionAnualPorId($idColegiacionAnual);
                if ($resColegiacion['estado']) {
                    $datos = $resColegiacion['datos'];
                    $idColegiacionAnual = $datos['idColegiacionAnual'];
                    $periodo = $datos['periodo'];
                    $cuotas = $datos['cuotas'];
                    $antiguedad = $datos['antiguedad'];
                    $importe = $datos['importe'];
                    $vencimientoCuotaUno = $datos['vencimientoCuotaUno'];
                    $pagoTotal = $datos['pagoTotal'];
                    $vencimientoPagoTotal = $datos['vencimientoPagoTotal'];
                } else {
                    $continua = FALSE;
                }
            } else {
                $periodo = $_SESSION['periodoActual'];
                $cuotas = NULL;
                $antiguedad = NULL;
                $importe = NULL;
                $vencimientoCuotaUno = NULL;
                $pagoTotal = NULL;
                $vencimientoPagoTotal = NULL;
            }
        }
        if ($continua) {
        ?>
            <div class="row">
                <div class="col-md-12 text-center"><h4><b><?php echo $titulo; ?></b></h4></div>
            </div>
            <form id="datosColegiacion" autocomplete="off" name="datosColegiacion" method="POST" action="datosColegiacion/abm_colegiacion_anual.php">
                <div class="row">
                    <div class="col-md-4">
                        <label>Período: * </label>
                        <input class="form-control" autofocus autocomplete="OFF" type="number" id="periodo" name="periodo" value="<?php echo $periodo; ?>" placeholder="Ingrese Periodo" required="" />
                    </div>
                    <div class="col-md-2">
                        <label>Cuotas: </label>
                        <input class="form-control" type="number" id="cuotas" name="cuotas" value="<?php echo $cuotas; ?>" required="" min="1" max="10" />
                    </div>
                    <div class="col-md-5">
                        <label>Antigüedad: </label>
                        <br>
                        <label class="radio-inline">
                            <input type="radio" name="antiguedad" id="antiguedad" value="1" <?php if ($antiguedad == "1") { echo 'checked=""'; } ?>>menos de 5 años
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="antiguedad" id="antiguedad" value="2" <?php if ($antiguedad == "2") { echo 'checked=""'; } ?>>5 o más años
                        </label>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        <label>Importe Anual: *</label>
                        <input class="form-control" type="number" id="importe" name="importe" value="<?php echo $importe; ?>" required="" />
                    </div>
                    <div class="col-md-3">
                        <label>Vencimiento Cuota 1:  *</label>
                        <input type="date" class="form-control" id="vencimientoCuotaUno" name="vencimientoCuotaUno" value="<?php echo $vencimientoCuotaUno;?>" required="" >
                    </div>
                    <div class="col-md-2">
                        <label>Importe Pago Total: *</label>
                        <input class="form-control" type="number" id="pagoTotal" name="pagoTotal" value="<?php echo $pagoTotal; ?>" required="" />
                    </div>
                    <div class="col-md-3">
                        <label>Vencimiento Pago Total Hasta: </label>
                        <input type="date" class="form-control" id="vencimientoPagoTotal" name="vencimientoPagoTotal" value="<?php echo $vencimientoPagoTotal;?>" required="">
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" ><?php echo $textoBoton; ?> </button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        <input type="hidden" name="idColegiacionAnual" id="idColegiacionAnual" value="<?php echo $idColegiacionAnual; ?>" />
                    </div>
                </div>    
            </form>
        <?php
        } else {
        ?>
            <div class="col-md-12">
                <div class="<?php echo $resColegiacion['clase']; ?>" role="alert">
                    <span class="<?php echo $resColegiacion['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resColegiacion['mensaje']; ?></strong></span>
                </div>        
            </div>
        <?php 
        }
        ?>
    </div>    
</div>
<?php
require_once '../html/footer.php';
