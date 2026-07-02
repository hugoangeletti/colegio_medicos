<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/condonacionLogic.php');
$condonacionLogic = new condonacionLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    if (isset($_POST['idColegiado'])) {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
    }
}

if (isset($idColegiado)) {
    $periodoActual = $_SESSION['periodoActual'];
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
    } else {
        $idResponsable = NULL;
        $idTipoCondonacion = NULL;
        $observaciones = NULL;
        //obtengo la deuda, inicializo los campos a mostrar
        $totalDeuda = 0;
        $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualAPagar($idColegiado);
        if ($resDeuda['estado']) {
            //inicializo los totales
            foreach ($resDeuda['datos'] as $row) {
                $totalDeuda += $row['importeUno'];
            }
        }
        
        $totalDeudaPP = 0;
        $resDeudaPP = $colegiadoPlanPagoLogic->obtenerDeudaPlanPagosPorIdColegiado($idColegiado);
        if ($resDeudaPP['estado']) {
            foreach ($resDeudaPP['datos'] as $row) {
                $totalDeudaPP += $row['importe'];
            }
        }
    }
    ?>

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Condonación de deuda</h4>
            </div>
            <div class="col-md-3 text-left">
                <?php 
                if (isset($_POST['origen']) && $_POST['origen'] == 'nuevo') {
                    //accede desde la lista de planes de pagos, entonces debe volver al listado
                ?>
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_condonacion.php">
                        <button type="submit"  class="btn btn-info" >Volver a Condonaciones</button>
                    </form>
                <?php
                } else {
                    //sino, fue llamado desde el colegiado, debe volver a los datos de tesoreria
                ?>
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_tesoreria.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-info" >Volver a Datos de Tesorería</button>
                    </form>
                <?php
                }
                ?>
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
    <div class="row">
        <div class="col-md-12 text-center"><h4><b>Nueva Condonación</b></h4></div>
    </div>
    <div class="row">&nbsp;</div>
    <form id="datosPlanPagos" autocomplete="off" name="datosCondonacion" method="POST" action="datosColegiadoCondonacion\generar_condonacion.php">
        <?php
        if ($totalDeuda+$totalDeudaPP > 0) {
        ?>
            <div class="row">
                <div class="col-md-3">
                    <label>Responsable: * </label>  
                    <select class="form-control" id="idResponsable" name="idResponsable" required="">
                        <?php
                        $resResponsables = $condonacionLogic->obtenerResponsables();
                        if ($resResponsables['estado']) {
                            foreach ($resResponsables['datos'] as $row) {
                            ?>
                                <option value="<?php echo $row['id'] ?>" <?php if($idResponsable == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                            <?php
                            }
                        } else {
                            echo $resResponsables['mensaje'];
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Motivo: * </label>  
                    <select class="form-control" id="idTipoCondonacion" name="idTipoCondonacion" required="">
                        <option value="">Seleccione un motivo de condonación</option>
                        <?php
                        $resTipoCondonacion = $condonacionLogic->obtenerTipoCondonacion();
                        if ($resTipoCondonacion['estado']) {
                            foreach ($resTipoCondonacion['datos'] as $row) {
                            ?>
                                <option value="<?php echo $row['id'] ?>" <?php if($idTipoCondonacion == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                            <?php
                            }
                        } else {
                            echo $resTipoCondonacion['mensaje'];
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Observaciones: * </label>  
                    <textarea class="form-control" name="observaciones" id="observaciones" rows="2" ><?php echo $observaciones; ?></textarea>
                </div>
<!--                    <div class="col-md">
                    <label>Condona toda la deuda? * </label>  
                    <select class="form-control" id="todas" name="todas" required="">
                        <option value="N" selected="">NO</option>
                        <option value="S">SI</option>
                    </select>
                </div>-->
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="form-check">
                    <?php
                    if ($totalDeuda > 0) {
                    ?>
                        <h4><b class="text-center">Deuda de Colegiación a condonar &nbsp;</b></h4>
                        <?php
                        foreach ($resDeuda['datos'] as $row) {
                            $idColegiadoDeudaAnualCuota = $row['idColegiadoDeudaAnualCuota'];
                            $periodo = $row['periodo'];
                            $cuota = $row['cuota'];
                            $importe = $row['importeUno'];
                        ?>
                        <div class="col-md-2">
                            <input class="form-check-input" name="lasCuotas[]" type="checkbox" checked="checked" value="<?php echo $idColegiadoDeudaAnualCuota ?>" 
                                   id="<?php echo $idColegiadoDeudaAnualCuota ?>">
                            <label class="form-check-label" for="<?php echo $idColegiadoDeudaAnualCuota ?>">
                              <?php echo $periodo.'-'.$cuota.': $'.$importe ?>
                            </label>
                        </div>
                        <?php
                        }
                    }

                    if ($totalDeudaPP > 0) {
                    ?>
                        <div class="row">&nbsp;</div>
                        <h4><b class="text-center">Deuda de Plan de Pagos a condonar &nbsp;</b></h4>
                        <?php
                        foreach ($resDeudaPP['datos'] as $row) {
                            $idPlanPagosCuotas = $row['idPlanPagosCuotas'];
                            $idPlanPagos = $row['idPlanPagos'];
                            $cuota = $row['cuota'];
                            $importe = $row['importe'];
                        ?>
                        <div class="col-md-2">
                            <input class="form-check-input" name="lasCuotasPP[]" type="checkbox" checked="checked" value="<?php echo $idPlanPagosCuotas ?>" 
                                   id="<?php echo $idPlanPagosCuotas ?>">
                            <label class="form-check-label" for="<?php echo $idPlanPagosCuotas ?>">
                              <?php echo $idPlanPagos.'-'.$cuota.': $'.$importe ?>
                            </label>
                        </div>
                        <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php 
            }
            ?>
        </div>
        
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?php
                if ($totalDeuda > 0 || $totalDeudaPP > 0) {
                ?>
                <h4 id="informe" style="display: none; color: #F8BB00; ">La Condonación se generó con éxito.</h4>
                <button type="submit" name='confirma' id='confirma' class="btn btn-success" onclick="show('confirma', 'informe')">Confirma Condonación</button>
                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                <?php 
                } else {
                ?>
                    <h4 class="alert alert-warning">No registra deuda para generar Condonación.</h4>
                <?php
                }
                ?>
            </div>
        </div>    
    </form>
    </div>
</div>
<?php
} else {
    //debe seleccionar al colegiado    
?>
    <div class="row">
        <div class="col-md-12 text-center">
            <h4>Nueva Condonación</h4>
            <h5>Seleccione al colegiado/a</h5>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_condonacion_nueva.php">
            <div class="row">
                <div class="col-md-3" style="text-align: right;">
                    <label>Matr&iacute;cula o Apellido y Nombre *</label>
                </div>
                <div class="col-md-7">
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                    <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                </div>
                <div class="col-md-2">
                    <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                    <input type="hidden" name="origen" id="origen" value="nuevo" />
                </div>
            </div>
        </form>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row text-center">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_condonacion.php">
            <button type="submit"  class="btn btn-info" >Volver a Condonaciones</button>
        </form>
    </div>
<?php
}
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php',
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
                $('#idColegiado').val(nameIdMap[item]);
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