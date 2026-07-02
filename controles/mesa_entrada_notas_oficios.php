<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/remitenteLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
$readOnly = "";
$requerido = "";
$mesaEntradaLogic = new mesaEntradaLogic();
if (isset($_GET['notas'])) {
    $ingreso_por = "NOTAS";
} else {
    $ingreso_por = "MESA_ENTRADAS";
}

if (isset($_GET['id']) && $_GET['id'] <> "") {
    if (isset($_GET['editar'])) {
        $accion = "EDITAR";
        $requerido = "required";
    } else {
        $accion = "CONSULTAR";
        $readOnly = "readonly";
    }
    $idMesaEntradaNota = $_GET['id'];
    $idMesaEntrada = NULL;
    $resNota = $mesaEntradaLogic->obtenerMesaEntradaNotaPorId($idMesaEntradaNota, $idMesaEntrada);
    if ($resNota['estado']) {
        $nota = $resNota['datos'];
        $idMesaEntrada = $nota['idMesaEntrada'];
        $tema = $nota['tema'];
        $incluyeListaMovimientos = $nota['incluyeListaMovimientos'];
        $resMesa = $mesaEntradaLogic->obtenerMesaEntradaPorId($idMesaEntrada);
        if ($resMesa['estado']) {
            $mesaEntrada = $resMesa['datos'];
            if (isset($mesaEntrada['idColegiado']) && $mesaEntrada['idColegiado'] <> "") {
                $idColegiado = $mesaEntrada['idColegiado'];
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                if ($resColegiado['estado']) {
                    $colegiado = $resColegiado['datos'];
                    $colegiado_buscar = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
                } else {
                    $continua = FALSE;
                    $mensaje .= $resColegiado['mensaje'];
                }
            } else {
                $idColegiado = NULL;
                if (isset($mesaEntrada['idRemitente']) && $mesaEntrada['idRemitente'] <> "") {
                    $idRemitente = $mesaEntrada['idRemitente'];
                    $remitenteLogic = new remitenteLogic();
                    $resRemitente = $remitenteLogic->obtenerRemitentePorId($idRemitente);
                    if ($resRemitente['estado']) {
                        $remitente = $resRemitente['datos'];
                        $remitente_buscar = $remitente['nombre'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resRemitente['mensaje'];
                    }
                } else {
                    $idRemitente = NULL;
                    $continua = FALSE;
                    $mensaje .= 'Mal ingresado, falta Colegiado o Remitente';
                }
            }
            $observaciones = $mesaEntrada['observaciones'];
        } else {
            $continua = FALSE;
            $mensaje .= $resMesa['mensaje'];
            $clase = $resMesa['clase'];    
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resNota['mensaje'];
        $clase = $resNota['clase'];
    }
} else {
    $esColegiado = "S";
    $idColegiado = NULL;
    $idRemitente = NULL;
    $colegiado_buscar = NULL;
    $remitente_buscar = NULL;
    $idMesaEntradaNota = NULL;
    $accion = "AGREGAR";
    $idMesaEntrada = NULL;
    $tema = "";
    $incluyeMovimiento = NULL;
    $observaciones = "";
    $estiloColegiado =  'style="display: block;"';
    $estiloRemitente =  'style="display: none;"';
}
$titulo = "NOTAS Y OFICIOS";

if (isset($_POST['mensaje'])) {
?>
    <div id="divMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
    <?php    
    /*
    if (isset($_POST['idMesaEntrada']) && $_POST['idMesaEntrada'] <> "") {
        $idMesaEntrada = $_POST['idMesaEntrada'];
    } else {
        $idMesaEntrada = NULL;
    }
    */
    if (isset($_POST['esColegiado']) && $_POST['esColegiado']) {
        $esColegiado = $_POST['esColegiado'];
        if ($esColegiado == 'S') {
            $estiloColegiado =  'style="display: block;"';
            $estiloRemitente =  'style="display: none;"';
        } else {
            if ($esColegiado == 'N') {
                $estiloColegiado =  'style="display: none;"';
                $estiloRemitente =  'style="display: block;"';
            } else {
                $estiloColegiado = '';
                $estiloRemitente = '';
            }
        }
    } else {
        $esColegiado = 'S';
    }
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
    }
    if (isset($_POST['colegiado_buscar']) && $_POST['colegiado_buscar'] <> "") {
        $colegiado_buscar = $_POST['colegiado_buscar'];
    } else {
        $colegiado_buscar = NULL;
    }
    if (isset($_POST['idRemitente']) && $_POST['idRemitente'] <> "") {
        $idRemitente = $_POST['idRemitente'];
    } else {
        $idRemitente = NULL;
    }
    if (isset($_POST['remitente_buscar']) && $_POST['remitente_buscar'] <> "") {
        $remitente_buscar = $_POST['remitente_buscar'];
    } else {
        $remitente_buscar = NULL;
    }
    $tema = $_POST['tema'];
    $incluyeMovimiento = $_POST['incluyeMovimiento'];
    $observaciones = $_POST['observaciones'];
}   
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formNota" name="formNota" method="POST" onSubmit="" action="datosMesaEntrada\abm_nota_oficio.php<?php if ($ingreso_por == "NOTAS") { echo '?notas'; } ?>">
                <div class="row">
                    <div class="col-md-2">
                        <b>Nota/Oficio presentada por: </b>
                        <br>
                        <label class="radio-inline"><input type="radio" name="esColegiado" id="esColegiado" value="S" <?php if ($esColegiado == 'S') { ?> checked="" <?php } ?>>Colegiado</label>
                        <label class="radio-inline"><input type="radio" name="esColegiado" id="esColegiado" value="N" <?php if ($esColegiado == 'N') { ?> checked="" <?php } ?>>Otro Remitente</label>
                    </div>
                    <div class="col-md-6" id="esUnColegiado" <?php echo $estiloColegiado; ?>>
                        <label for="colegiado_buscar">Buscar colegiado *</label>
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" />
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                    <?php 
                    /*
                    <div class="col-md-6" id="esUnDistrito" style="display: none;">
                        <label for="idRemitente">Distrito *</label>
                        <select class="form-control" id="idRemitente" name="idRemitente" required="">
                            <option value="">Seleccione Distrito</option>
                            <option value="1" <?php if($idRemitente == "1") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO II</option>
                            <option value="2" <?php if($idRemitente == "2") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO III</option>
                            <option value="3" <?php if($idRemitente == "3") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO IV</option>
                            <option value="4" <?php if($idRemitente == "4") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO V</option>
                            <option value="5" <?php if($idRemitente == "5") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO VI</option>
                            <option value="6" <?php if($idRemitente == "6") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO VII</option>
                            <option value="7" <?php if($idRemitente == "7") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO VIII</option>
                            <option value="10" <?php if($idRemitente == "10") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO IX</option>
                            <option value="11" <?php if($idRemitente == "11") { echo 'selected'; } ?>>COLEGIO DE MÉDICOS DISTRITO X</option>
                        </select>
                    </div>
                    */
                    ?>
                    <div class="col-md-6" id="noEsUnColegiado" <?php echo $estiloRemitente; ?>>
                        <label for="remitente_buscar">Buscar remitente *</label>
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="remitente_buscar" id="remitente_buscar" placeholder="Buscar Remitente" value="<?php echo $remitente_buscar; ?>" />
                        <input type="hidden" name="idRemitente" id="idRemitente" value="<?php echo $idRemitente; ?>" />
                    </div>
                    <div class="col-md-4" name="incluyeMovimientos" id="incluyeMovimientos" <?php echo $estiloRemitente; ?>>
                        <b>Incluye Movimientos </b>
                        <br>
                        <label class="radio-inline"><input type="radio" name="incluyeMovimiento" id="incluyeMovimientoSi" value="S" <?php if ($incluyeMovimiento == 'S') { ?> checked="" <?php } ?>>Si</label>
                        <label class="radio-inline"><input type="radio" name="incluyeMovimiento" id="incluyeMovimientoNo" value="N" <?php if ($incluyeMovimiento == 'N') { ?> checked="" <?php } ?>>No</label>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-8">
                        <label for="tema">Tema *</label>
                        <input class="form-control" autocomplete="OFF" type="text" name="tema" id="tema" placeholder="Ingrese Tema de Nota/Oficio" value="<?php echo $tema; ?>" required=""/>
                    </div>
                </div>  
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-8">
                        <label for="observaciones">Observaciones </label>
                        <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="5" <?php echo $readOnly; ?>><?php echo $observaciones; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-8 text-center">
                        <button type="submit" class="btn btn-success" >Guardar</button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                        <?php 
                        if (isset($idMesaEntradaNota)) {
                        ?>
                            <input type="hidden" name="idMesaEntradaNota" id="idMesaEntradaNota" value="<?php echo $idMesaEntradaNota; ?>">
                        <?php 
                        } 
                        ?>
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
<div class="row">
    <div class="col-md-1 text-right">
        <?php
        if ($ingreso_por == "MESA_ENTRADAS") {
        ?>
            <a href="mesa_entrada_listado.php" class="btn btn-info">Salir</a>
        <?php } else { ?>
            <a href="mesa_entrada_notas_listado.php" class="btn btn-info">Salir</a>
        <?php
        }
        ?>
    </div>
</div>
<?php    
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

    $(function(){
        var nameIdMap = {};
        $('#remitente_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'remitente.php',
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
                $('#idRemitente').val(nameIdMap[item]);
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

    
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="esColegiado"]:checked').val();
    });
    $(document).on('click', '[name="esColegiado"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            //if (x.style.display === "none") {
            var x = document.getElementById("esUnColegiado");
            var y = document.getElementById("noEsUnColegiado");
            var z = document.getElementById("incluyeMovimientos");
            if (lastSelected != 'S') {
                x.style.display = "block";
                y.style.display = "none";
                z.style.display = "none";
            } else {
                x.style.display = "none";
                y.style.display = "block";
                z.style.display = "block";
            }
        }
        lastSelected = $(this).val();
    });
    
</script>
