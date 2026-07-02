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
if (isset($_GET['agregar'])) {
    $accion = 'agregar';
    $titulo = 'ALTA DE FAP EN REUNION DE CONSEJO';
    $idSapConsejoDetalle = NULL;
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idSapConsejo = $_GET['id'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idSapConsejo - ';
    }
} else {
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idSapConsejoDetalle = $_GET['id'];
        $resDetalle = $fapLogic->obtenerSapReunionDetallePorId($idSapConsejoDetalle);
        if ($resDetalle['estado']) {
            $fapReunionDetalle = $resDetalle['datos'];
            $idSapConsejo = $fapReunionDetalle['idSapConsejo'];
            $idSapCaratula = $fapReunionDetalle['idSapCaratula'];
            $observacion = $fapReunionDetalle['observacion'];
            $estado = $fapReunionDetalle['estado'];
            $fechaAprobacion = $fapReunionDetalle['fechaAprobacion'];
            
            if (isset($_GET['editar'])) {
                $accion = 'editar';
                $titulo = 'EDITAR DETALLE DE LA REUNIÓN ';
                $botonConfirma .= 'cambios';
            } else {
                $accion = 'consulta';
                $titulo = 'CONSULTA DETALLE DE LA REUNIÓN ';
                $botonConfirma = 'Volver';
            }
        } else {
            $continua = FALSE;
            $mensaje .= $resDetalle['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idSapConsejo - ';
    }
}
if ($continua) {
    if (isset($_POST['mensaje'])) {
        //vino por error en la carga
        ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
        <?php
        if (isset($_POST['fechaReunion'])) {
            $fechaReunion = $_POST['fechaReunion'];
        } else {
            $fechaReunion = NULL;
        }
        if (isset($_POST['observaciones'])) {
            $observaciones = $_POST['observaciones'];
        } else {
            $observaciones = NULL;
        }
        if (isset($_POST['resolucion'])) {
            $resolucion = $_POST['resolucion'];
        } else {
            $resolucion = NULL;
        }
        if (isset($_POST['estadoReunion'])) {
            $estadoReunion = $_POST['estadoReunion'];
        } else {
            $estadoReunion = NULL;
        }
    } else {
        if (!isset($idSapConsejoDetalle)) {
            //si entra por alta inicializa todos los campos en null
            if (isset($_POST['idSapCaratula'])) {
                $idSapCaratula = $_POST['idSapCaratula'];
            } else {
                $idSapCaratula = NULL;
            }
            $observacion = NULL;
            $estado = NULL;
            $fechaAprobacion = NULL;
        }
    }
    if ($continua) {
    ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo $titulo; ?></h4>
                    </div>
                    <div class="col-md-2">
                        <a href="fap_reuniones_detalle.php?id=<?php echo $idSapConsejo; ?>" class="btn btn-info">Volver al detalle</a>
                    </div>
                    <div class="col-md-2">
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?php 
                if ((isset($_POST['idSapCaratula']) && $_POST['idSapCaratula'] <> "") || isset($idSapCaratula)) {
                    //obtenemos los datos del fap
                    $resFap = $fapLogic->obtenerSapCaratulaPorId($idSapCaratula);
                    if ($resFap['estado']) {
                        $fapCaratula = $resFap['datos'];
                        $idColegiado = $fapCaratula['idColegiado'];
                        $idSapTipoTramite = $fapCaratula['idSapTipoTramite'];
                        $fechaIngreso = $fapCaratula['fechaIngreso'];
                        $nombreCausa = $fapCaratula['nombreCausa'];
                        $nombreTipoCausa = $fapCaratula['nombreTipoCausa'];
                        $nombreSapEstado = $fapCaratula['nombreSapEstado'];
                        $nombreSapTipoTramite = $fapCaratula['nombreSapTipoTramite'];

                        $colegiadoLogic = new colegiadoLogic();
                        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                        if ($resColegiado['estado']) {
                            $colegiado = $resColegiado['datos'];
                            $matricula = $colegiado['matricula'];
                            $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
                        } else {
                            $continua = FALSE;
                            $mensaje .= $resColegiado['mensaje'];
                        }
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resFap['mensaje'];
                    }

                    //si es agregar en id va el idSapConsejo, si es editar o borrar en id va idSapConsejoDetalle
                    if ($accion == 'agregar') {
                        $id = $idSapConsejo;
                    } else {
                        $id = $idSapConsejoDetalle;
                    }
                    ?>
                    <form id="formReunion" name="formReunion" method="POST" onSubmit="" action="datosFap/abm_reuniones_detalle_individual.php?<?php echo $accion; ?>&id=<?php echo $id; ?>">
                        <div class="row">
                            <div class="col-md-1">
                                <label for="idSapCaratula">Id FAP *: </label>
                                <input class="form-control" type="text" name="idSapCaratula" id="idSapCaratula" value="<?php echo $idSapCaratula; ?>" readonly >
                            </div>
                            <div class="col-md-1">
                                <label for="matricula">Matrícula: </label>
                                <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly >
                            </div>
                            <div class="col-md-4">
                                <label for="apellidoNombre">Apellido y nombre: </label>
                                <input class="form-control" type="text" name="apellidoNombre" id="apellidoNombre" value="<?php echo $apellidoNombre; ?>" readonly >
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nombreCausa">Causa: </label>
                                <input class="form-control" type="text" name="nombreCausa" id="nombreCausa" value="<?php echo $nombreCausa; ?>" readonly>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-2">
                                <label for="nombreSapTipoTramite">Tipo trámite: </label>
                                <input class="form-control" type="text" name="nombreSapTipoTramite" id="nombreSapTipoTramite" value="<?php echo $nombreSapTipoTramite; ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="nombreTipoCausa">Tipo causa: </label>
                                <input class="form-control" type="text" name="nombreTipoCausa" id="nombreTipoCausa" value="<?php echo $nombreTipoCausa; ?>" readonly >
                            </div>
                            <div class="col-md-2">
                                <label for="nombreSapEstado">Estado: </label>
                                <input class="form-control" type="text" name="nombreSapEstado" id="nombreSapEstado" value="<?php echo $nombreSapEstado; ?>" readonly>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-2">
                                <label for="fechaAprobacion">Fecha de aprobación: </label>
                                <input class="form-control" type="date" name="fechaAprobacion" id="fechaAprobacion" value="<?php echo $fechaAprobacion; ?>" >
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">Estado: *</label>
                                <br>
                                <label class="radio-inline"><input type="radio" name="estado" value="P" <?php if ($estado == 'P') { ?> checked="" <?php } ?>>Pendiente</label>
                                <label class="radio-inline"><input type="radio" name="estado" value="A" <?php if ($estado == 'A') { ?> checked="" <?php } ?>>Aprobado</label>
                                <label class="radio-inline"><input type="radio" name="estado" value="D" <?php if ($estado == 'D') { ?> checked="" <?php } ?>>Desaprobado</label>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="observacion">Observaciones: </label>
                                <textarea class="form-control" name="observacion" id="observacion" rows="4" ><?php echo $observacion; ?></textarea>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <button type="submit" class="btn btn-success"><?php echo $botonConfirma; ?></button>
                            </div>
                        </div>
                    </form>
                <?php 
                } else {
                ?>
                    <div class="row">
                        <form id="formFap" name="formFap" method="POST" onSubmit="" action="fap_reuniones_detalle_individual_form.php?agregar&id=<?php echo $idSapConsejo; ?>">
                            <div class="row">
                                <div class="col-md-3" style="text-align: right;">
                                    <label>Buscar por FAP N° o Matrícula o Apellido y Nombre *</label>
                                </div>
                                <div class="col-md-7">
                                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="fap_buscar" id="fap_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" required=""/>
                                    <input type="hidden" name="idSapCaratula" id="idSapCaratula" required="" />
                                </div>
                                <div class="col-md-2">
                                    <button type="submit"  class="btn btn-success">Confirma FAP</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
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
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#fap_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'fap_pendiente_de_reunion.php',
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
                $('#idSapCaratula').val(nameIdMap[item]);
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