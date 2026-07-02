<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');

$continua = TRUE;
$mensaje = "";
$titulo = "";
$botonConfirma = "Confirma ";
$fapLogic = new fapLogic();
$accion = 'agregar';
$idSapConsejoDetalle = NULL;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSapConsejo = $_GET['id'];
    $resReunion = $fapLogic->obtenerSapReunionPorId($idSapConsejo);
    if ($resReunion['estado']) {
        $reunion = $resReunion['datos'];
        $fechaReunion = $reunion['fechaReunion'];
    } else {
        $continua = FALSE;
        $mensaje .= $resReunion['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idSapConsejo - ';
}
if ($continua) {
    if (isset($_POST['mensaje'])) {
    ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
    <?php    
    }   

    $resFap = $fapLogic->obtenerFapPenditesDeReunion();
    if ($resFap['estado']) {
    ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-8">
                        <h4>ALTA DE FAP EN REUNION DE CONSEJO DE FECHA <?php echo cambiarFechaFormatoParaMostrar($fechaReunion); ?></h4>
                    </div>
                    <div class="col-md-2">
                        <a href="fap_reuniones_detalle.php?id=<?php echo $idSapConsejo; ?>" class="btn btn-info">Volver al detalle</a>
                    </div>
                    <div class="col-md-2">
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <form id="formReunion" name="formReunion" method="POST" onSubmit="" action="datosFap/abm_reuniones_detalle.php?id=<?php echo $idSapConsejo; ?>">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Selección</th>
                                    <th>Id</th>
                                    <th>Fecha de ingreso</th>
                                    <th>Matrícula</th>
                                    <th>Apellido y Nombre</th>
                                    <th>Nombre de la causa</th>
                                    <th>Tipo causa</th>
                                    <th>Tipo trámite</th>
                                    <th>Estado</th>
                                    <th>Condición</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            foreach ($resFap['datos'] as $fapCaratula) {
                                $idSapCaratula = $fapCaratula['idSapCaratula'];
                                $fechaIngreso = $fapCaratula['fechaIngreso'];
                                $nombreCausa = $fapCaratula['nombreCausa'];
                                $nombreTipoCausa = $fapCaratula['nombreTipoCausa'];
                                $nombreSapEstado = $fapCaratula['nombreSapEstado'];
                                $nombreSapTipoTramite = $fapCaratula['nombreSapTipoTramite'];
                                $nombreSapCondicion = $fapCaratula['nombreSapCondicion'];
                                $matricula = $fapCaratula['matricula'];
                                $apellidoNombre = trim($fapCaratula['apellido']).' '.trim($fapCaratula['nombre']);
                                ?>
                                <tr>
                                    <td style="text-align: center;"><input type="checkbox" name="fap_seleccionado[]" id="fap_seleccionado[]" value="<?php echo $idSapCaratula ?>">&nbsp;</td>
                                    <td><?php echo $idSapCaratula; ?></td>
                                    <td><?php echo cambiarFechaFormatoParaMostrar($fechaIngreso); ?></td>
                                    <td><?php echo $matricula; ?></td>
                                    <td><?php echo $apellidoNombre; ?></td>
                                    <td><?php echo $nombreCausa; ?></td>
                                    <td><?php echo $nombreTipoCausa; ?></td>
                                    <td><?php echo $nombreSapTipoTramite; ?></td>
                                    <td><?php echo $nombreSapEstado; ?></td>
                                    <td><?php echo $nombreSapCondicion; ?></td>
                                </tr>
                            <?php 
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-success">Confirma causas</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 alert alert-danger" role="alert">
                <span><strong><?php echo $resFap['mensaje']; ?></strong></span>
            </div>
        </div>
    <?php
    }
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12 alert alert-danger" role="alert">
            <span><strong><?php echo $mensaje; ?></strong></span>
        </div>
    </div>
<?php    
}
require_once '../html/footer.php';
