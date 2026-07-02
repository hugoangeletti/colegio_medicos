<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
require_once ('../dataAccess/patologiasLogic.php');
$patologiasLogic = new patologiasLogic();

$continuar = TRUE;
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $idColegiado = NULL;
    $continuar = FALSE;
}
if (isset($_GET['id'])) {
    $idColegiadoMovimiento = $_GET['id'];
} else {
    $idColegiadoMovimiento = NULL;
    $continuar = FALSE;
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Movimientos matriculares</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_movimientos.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Movimientos</button>
                </form>
            </div>
        </div>
    </div>

<?php
if ($continuar) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        //include 'menuColegiado.php';
    ?>
        <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
            </div>
            <div class="col-md-4 text-right"><b>Estado actual: <?php echo $colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']).$colegiado['movimientoCompleto']; ?></b></div>
        </div>
        <div class="row">&nbsp;</div>
        <?php
        $resMovimiento = $colegiadoMovimientoLogic->obtenerMovimientoPorId($idColegiadoMovimiento);
        if ($resMovimiento['estado']){
            $movimiento = $resMovimiento['datos'];
            if ($idColegiado == $movimiento['idColegiado']) {
                $fechaDesde = $movimiento['fechaDesde'];
                $fechaHasta = $movimiento['fechaHasta'];
                $distritoCambio = $movimiento['distritoCambio'];
                $distritoOrigen = $movimiento['distritoOrigen'];
                $idPatologia = $movimiento['idPatologia'];
                $detalleMovimiento = $movimiento['detalleMovimiento'];
                $resPatologia = $patologiasLogic->obtenerNombrePatologia($idPatologia);
                if ($resPatologia['estado']) {
                    $patologia_buscar = $resPatologia['datos']['nombre'];
                } else {
                    $patologia_buscar = NULL;
                }
                ?>
            <form id="formPatologia" name="formPatologia" method="POST" action="datosColegiadoMovimiento/patologia.php?idColegiado=<?php echo $idColegiado ?>&id=<?php echo $idColegiadoMovimiento; ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label>Movimiento matricular</label>
                        <input class="form-control" type="text" name="detalleMovimiento" value="<?php echo $detalleMovimiento; ?>" readonly=""/>
                    </div>
                    <div class="col-md-2">
                        <label>Fecha Desde</label>
                        <input class="form-control" type="text" name="fechaDesde" value="<?php echo cambiarFechaFormatoParaMostrar($fechaDesde); ?>" readonly=""/>
                    </div>
                    <div class="col-md-2">
                        <label>Fecha Desde</label>
                        <input class="form-control" type="text" name="fechaHasta" value="<?php echo cambiarFechaFormatoParaMostrar($fechaHasta); ?>" readonly=""/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-7">
                        <label>Patología *</label>
                        <input class="form-control" autocomplete="OFF" type="text" name="patologia_buscar" id="patologia_buscar" placeholder="Ingrese Código o Nombre de la patología" value="<?php echo $patologia_buscar; ?>" required=""/>
                        <input type="hidden" name="idPatologia" id="idPatologia" value="<?php echo $idPatologia; ?>" required="" />
                        <?php
                        /*
                        <select class="form-control" id="idPatologia" name="idPatologia" >
                            <option value="" selected>Sin Patología especificada</option>
                            <?php
                            $resPatologias = $patologiasLogic->obtenerPatologias();
                            if ($resPatologias['estado']) {
                                foreach ($resPatologias['datos'] as $fila) {
                                ?>
                                <option value="<?php echo $fila['idPatologia']; ?>" <?php if($idPatologia == $fila['idPatologia']) { ?> selected <?php } ?>><?php echo $fila['nombre']; ?></option>
                                <?php
                                }
                            }
                            ?>
                        </select>
                        */
                        ?>
                    </div>
                    <div class="col-md-5 text-center">
                        <br>
                        <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                    </div>
                </div>
            </form>
        </div>
        <?php
            } else {
            ?>
                <div class="alert alert-error" role="alert">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    <span><strong>INGRESO INCORRECTO</strong></span>
                </div>        
            <?php    
            }
        } else {
        ?>
            <div class="<?php echo $resMovimiento['clase']; ?>" role="alert">
                <span class="<?php echo $resMovimiento['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resMovimiento['mensaje']; ?></strong></span>
            </div>        
        <?php
        }
    } else {
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php        
    }
} else {
?>
    <div class="alert alert-error" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong>INGRESO INCORRECTO</strong></span>
    </div>        
<?php    
}
?>
    </div>
</div>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
?>
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#patologia_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'patologia.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idPatologia').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
    
</script>