<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
require_once ('../dataAccess/condonacionLogic.php');
$condonacionLogic = new condonacionLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":50,
            "order": [[ 0, "asc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": false,
            "bFilter": false,
            //dom: 'T<"clear">lfrtip',
        });
    });              
</script>

<?php

$continua = TRUE;
$accion = $_POST['accion'];
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $idColegiado = NULL;
    $continua = FALSE;
}

if (isset($_GET['idCondonacion'])) {
    $idCondonacion = $_GET['idCondonacion'];
} else {
    $idCondonacion = NULL;
    $continua = FALSE;
}

if ($continua) {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
    }
    
    if (isset($_POST['mensaje'])) {
    ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
     <?php
    }
    //obtengo la condonacion
    $resCondonacion = $condonacionLogic->obtenerCondonacionPorId($idCondonacion);    
    if ($resCondonacion['estado']) {
        $realizo = $resCondonacion['datos']['realizo'];
        $responsable = $resCondonacion['datos']['responsable'];
        $observaciones = $resCondonacion['datos']['observacion'];
        $motivo = $resCondonacion['datos']['motivo'];
        $fechaCreacion = $resCondonacion['datos']['fechaSolicitud'];
        $estadoCondonacion = $resCondonacion['datos']['estado'];
        
        //busco las cuotas
        $resCuotas = $condonacionLogic->obtenerCondonacionDetalle($idCondonacion);
        if ($resCuotas['estado']) {
            $condonacionCuotas = $resCuotas['datos'];
        } else {
            $condonacionCuotas = array();
        }
    } else {
        $totalDeuda = 0;
        $cuotas = 0;
        $fechaCreacion = "";
    }
    ?>

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><?php if ($accion == 2) { echo 'Anular Condonación'; } else { echo 'Ver Cuotas Condonadas'; } ?></h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_condonacion.php">
                    <button type="submit"  class="btn btn-info" >Volver a Condonaciones</button>
                    <input type="hidden" name="estadoCondonacion" id="estadoCondonacion" value="<?php echo $estadoCondonacion; ?>" />
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <div class="row">
        <div class="col-md-2">
            <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
        </div>
        <div class="col-md-4">
            <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
        </div>
        <div class="col-md-6">&nbsp;</div>
    </div>
    <div class="row">&nbsp;</div>
        <form id="datosCondonacion" autocomplete="off" name="datosCondonacion" method="POST" action="datosColegiadoCondonacion/anular_condonacion.php">
            <div class="row">
                <div class="col-md-2">
                    <label>Condonación Nº: &nbsp;</label>
                    <input type="text" class="form-control" name="idCondonacion" id="idCondonacion" value="<?php echo $idCondonacion; ?>" readonly="" />
                </div>
                <div class="col-md-3">
                    <label>Motivo de la condonación: &nbsp;</label>
                    <input type="text" class="form-control" name="motivo" id="motivo" value="<?php echo $motivo; ?>" readonly="" />
                </div>
                <div class="col-md-2">
                    <label>Autorizada pot: &nbsp;</label>
                    <input type="text" class="form-control" name="responsable" id="responsable" value="<?php echo $responsable; ?>" readonly="" />
                </div>
                <div class="col-md-3">
                    <label>Realizada por: &nbsp;</label>
                    <input type="text" class="form-control" name="realizo" id="realizo" value="<?php echo $realizo; ?>" readonly="" />
                </div>
                <div class="col-md-2">
                    <label>Fecha de creación: &nbsp;</label>
                    <input type="text" class="form-control" name="fecha" id="fecha" value="<?php echo cambiarFechaFormatoParaMostrar($fechaCreacion); ?>" readonly="" />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 table-responsive">
                    <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Cuota</th>
                            <th>Importe</th>
                            <th>Vencimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $botonConfirma = TRUE;
                        foreach ($condonacionCuotas as $dato) 
                        {
                          $cuota = $dato['queCondona'].': '.$dato['laCuota'];
                          $importe = $dato['importe'];
                          $vencimiento = $dato['vencimiento'];
                          ?>
                        <tr>
                            <td><?php echo $cuota;?></td>
                            <td><?php echo $importe;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($vencimiento);?></td>
                       </tr>
                      <?php
                      }
                  ?>
                    </tbody>
                    </table>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <?php
                    if ($accion == 2) {
                    ?>
                        <button type="submit" name='confirma' id='confirma' class="btn btn-success">Confirma anulación</button>
                    <?php
                    } 
                    ?>
                </div>
            </div>
        </form>
    <?php  } else { ?>
        <div class="col-md-12 text-center">
            <h4>No se encontró la condonación, vuelva a intentar</h4>
        </div>
    <?php } ?>
    </div>
</div>
<?php
require_once '../html/footer.php';
