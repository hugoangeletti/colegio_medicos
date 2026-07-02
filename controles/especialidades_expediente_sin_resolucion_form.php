<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();

$continua = TRUE;
$mensaje = "";
$readOnly = "";
$requerido = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    if (isset($_GET['borrar'])) {
        $accion = "BORRAR";
        $requerido = "required";
        $titulo = " - Anular expediente";
    } else {
        $accion = "CONSULTAR";
        $readOnly = "readonly";
        $titulo = " - Consulta";
    }
    $idMesaEntrada = $_GET['id'];
    $resMesaEntrada = $mesaEntradaEspecialistaLogic->obtenerMesaEntradaEspecialistaPorIdMesaEntrada($idMesaEntrada);
    if ($resMesaEntrada['estado']) {
        $mesaEntradaEspecialista = $resMesaEntrada['datos'];
        $idMesaEntrada = $mesaEntradaEspecialista['idMesaEntrada'];
        $matricula = $mesaEntradaEspecialista['matricula'];
        $apellidoNombre = $mesaEntradaEspecialista['apellidoNombre'];
        $nombreEspecialidad = $mesaEntradaEspecialista['nombreEspecialidad'];
        $nombreTipoEspecialista = $mesaEntradaEspecialista['nombreTipoEspecialista'];
    } else {
        $continua = FALSE;
        $mensaje .= $resMesaEntrada['mensaje'];
        $clase = $resMesaEntrada['clase'];    
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta id - ';
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b>Mesa Entrada Especialista <?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
                <a href="especialidades_expediente_sin_resolucion.php" class="btn btn-info">Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formAnularDeuda" name="formAnularDeuda" method="POST" onSubmit="" action="datosMesaEntrada\anular_expediente_sin_resolucion.php">
                <div class="row">
                    <div class="col-md-2">
                        <label for="matricula">Matrícula: </label>
                        <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly />
                    </div>
                    <div class="col-md-4">
                        <label for="apellidoNombre">Colegiado: </label>
                        <input class="form-control" type="text" name="apellidoNombre" id="apellidoNombre" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $apellidoNombre; ?>" readonly/>
                    </div>
                    <div class="col-md-4">
                        <label for="especialidad">Especialidad: </label>
                        <input class="form-control" name="especialidad" id="especialidad" type="text" value="<?php echo $nombreEspecialidad; ?>" readonly=""/>
                    </div>
                    <div class="col-md-2">
                        <label for="tipoEspecialista">Tipo Especialista: </label>
                        <input class="form-control" name="tipoEspecialista" id="tipoEspecialista" type="text" value="<?php echo $nombreTipoEspecialista; ?>" readonly=""/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="observacion">Motivo de la baja * </label>
                        <textarea class="form-control" type="text" name="observacion" id="observacion" rows="3" required></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success" >Confirma anulación</button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                        <input type="hidden" name="idMesaEntrada" id="idMesaEntrada" value="<?php echo $idMesaEntrada; ?>">
                    </div>
                </div>  
            </form>   
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="<?php echo $clase; ?>" role="alert">
                        <span><strong><?php echo $mensaje; ?></strong></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';
?>
