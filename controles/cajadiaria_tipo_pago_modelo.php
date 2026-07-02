<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
require_once ('../dataAccess/usuarioLogic.php');
require_once ('../dataAccess/tipoPagoLogic.php');
$tipoPagoLogic = new tipoPagoLogic();

$idUsuario = $_SESSION['user_id'];
if (isset($_GET['idAsistente'])) {
    $idAsistente = $_GET['idAsistente'];
} else {
    if (isset($_POST['idAsistente'])) {
        $idAsistente = $_POST['idAsistente'];
    } else {
        $idAsistente = NULL;
    }
}
$continua = TRUE;
$cajaDiariaLogic = new cajaDiariaLogic();
$resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
if ($resCajaDiaria['estado']) {
    $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
} else {
    $continua = FALSE;
}

if ($continua) {
?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Caja Diaria - Cobranza por concepto</h4>
                </div>
                <div class="col-md-3 text-left">
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
                        <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            //debe seleccionar al colegiado    
            if (isset($_POST['persona']) && ($_POST['persona'] == "C" || $_POST['persona'] == "O")) {
                $persona = $_POST['persona'];
            } else {
                $persona = "C";
            }
            $colegiado_buscar = NULL;
            $idColegiado = NULL;
            $localidad_buscar = NULL;
            $idLocalidad = NULL;
            ?>
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosCajaDiaria/generar_recibo.php">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4>Generar recibo por concepto</h4>
                    </div>
                    <div id="grupoPersona" class="col-md-2 text-center">
                        <label>Responsable del recibo</label><br>
                        <label class="radio-inline"><input type="radio" name="persona" id="persona" value="C" <?php if ($persona == 'C') { ?> checked="" <?php } ?>>Colegiado</label>
                        <label class="radio-inline"><input type="radio" name="persona" id="persona" value="O" <?php if ($persona == 'O') { ?> checked="" <?php } ?>>Otro responsable</label>
                    </div>
                    <?php 
                    if ($persona == "C") {
                        //como es a todo efecto se inicializa como que no va con firma, entonces no muestar el radio de envia por mail
                        $styleGrupoColegiado = 'style="display: block"';
                        $styleGrupoOtro = 'style="display: none"';
                    } else {
                        $styleGrupoColegiado = 'style="display: none"';
                        $styleGrupoOtro = 'style="display: block"';
                    }
                    ?>
                    <div id="grupoColegiado" class="col-md-10" <?php echo $styleGrupoColegiado; ?>>
                            <div class="row">
                                <div class="col-md-3" style="text-align: right;">
                                    <label>Matr&iacute;cula o Apellido y Nombre *</label>
                                </div>
                                <div class="col-md-7">
                                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" required=""/>
                                    <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                                </div>
                            </div>
                    </div>
                    <div id="grupoOtro" class="col-md-10" <?php echo $styleGrupoOtro ?>>
                        <div class="row">
                            <div class="col-md-3">
                                <label>Apellido y Nombre *</label>
                                <input class="form-control" autofocus autocomplete="OFF" type="text" name="responsable" id="responsable" placeholder="Responsable del recibo" />
                            </div>
                            <div class="col-md-3">
                                <label>CUIT/CUIL *</label>
                                <input class="form-control" autocomplete="OFF" type="text" name="responsableCuit" id="responsableCuit" placeholder="CUIT/CUIL del Responsable del recibo" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <label>Domicilio *</label>
                                <input class="form-control" autocomplete="OFF" type="text" name="responsableDomicilio" id="responsableDomicilio" placeholder="Ingrese domicilio del responsable" />
                            </div>
                            <div class="col-md-3">
                                <label>Localidad *</label>
                                <input class="form-control" type="text" name="localidad_buscar" id="localidad_buscar" value="<?php echo $localidad_buscar; ?>" placeholder="Ingrese universidad a buscar" />
                                <input type="hidden" name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad; ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <h4 class="text-center">Concepto a abonar</h4>
                <div class="row">
                    <?php
                    $resTipos = $tipoPagoLogic->ontenerTiposPagoParaRecibo();
                    if ($resTipos['estado']) {
                        $totalRecibo = 0;
                        ?>
                        <div class="col-md-6">
                            <?php
                            foreach ($resTipos['datos'] as $row) {                                        
                                $importe = $row['importe'];
                                $idTipoPago = $row['id'];
                                $nombre = $row['nombre'];
                                if ($importe == 0) continue;
                                ?>
                                <div class="row">
                                    <div class="col-md-2">&nbsp;</div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" name="generarRecibo[]" type="checkbox" value="<?php echo $idTipoPago; ?>" 
                                                   id="<?php echo $idTipoPago ?>" onclick="cambiaTotalRecibo(<?php echo $importe ?>, <?php echo $totalRecibo; ?>, <?php echo $idTipoPago; ?>)">
                                            <label class="form-check-label" for="<?php echo $idTipoPago ?>">
                                              <?php echo $nombre; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="importe_<?php echo $idTipoPago; ?>" id="importe_<?php echo $idTipoPago; ?>" value="<?php echo $importe; ?>" readonly onChange="cambiaTotalRecibo(<?php echo $importe ?>, <?php echo $totalRecibo; ?>, <?php echo $idTipoPago; ?>)">
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php 
                            $i = 0;
                            foreach ($resTipos['datos'] as $row) {                                        
                                $importe = $row['importe'];
                                $idTipoPago = $row['id'];
                                $nombre = $row['nombre'];
                                if ($importe <> 0) continue;
                                $i++;
                                ?>
                                <div class="row">
                                    <div class="col-md-2">&nbsp;</div>
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input" name="generarRecibo[]" type="checkbox" value="<?php echo $idTipoPago; ?>" 
                                                   id="<?php echo $idTipoPago ?>" >
                                            <label class="form-check-label" for="<?php echo $idTipoPago ?>">
                                              <?php echo $nombre; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="importe_<?php echo $idTipoPago; ?>" id="importe_<?php echo $idTipoPago; ?>" data-id="importe_<?php echo $idTipoPago; ?>" value="<?php echo $importe; ?>" >
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="detalle_<?php echo $idTipoPago; ?>" value="">
                                    </div>
                                    <div class="col-md-1">                                        
                                        <input type="button" class="btn-importe" style="display: block" id="boton<?php echo $idTipoPago; ?>" data-id="<?php echo $idTipoPago; ?>" name="boton<?php echo $i; ?>" value="Confirma item">
                                        <input type="button" class="btn-importe-resta" style="display: none" id="boton-resta<?php echo $idTipoPago; ?>" data-id="<?php echo $idTipoPago; ?>" name="boton<?php echo $i; ?>" value="Borra item">
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    <?php 
                    }
                    ?>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Total a abonar:</h4> 
                        <input type="text" name="totalRecibo" id="totalRecibo" value="<?php echo $totalRecibo; ?>" readonly>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-default">Confirma recibo</button>
                    </div>
                </div>
            </form>
            <div class="row">&nbsp;</div>
        </div>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="alert alert-warning">NO HAY CAJA ABIERTA, DEBE IR A CAJAS DIARIAS Y ABRIR PRIMERO UNA CAJA DEL DIA</div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row text-center">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
            <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
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
    
    $(function(){
        var nameIdMap = {};
        $('#localidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'localidad.php',
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
                $('#idLocalidad').val(nameIdMap[item]);
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
        lastSelected = $('[name="persona"]:checked').val();
    });
    $(document).on('click', '[name="persona"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            var x = document.getElementById("grupoColegiado");
            var y = document.getElementById("grupoOtro");

            if (lastSelected == 'O') {
                x.style.display = "block";
                y.style.display = "none";
            } else {
                x.style.display = "none";
                y.style.display = "block";
            }
        }
        lastSelected = $(this).val();
    });

    $('.btn-importe').on('click', function (event) {
        var totalRecibo = parseInt(document.getElementById('totalRecibo').value);
        var idTipoPago = $(this).data('id');
        var importe = parseInt(document.getElementById('importe_'+idTipoPago).value);
        var valor = totalRecibo + importe;

        document.getElementById('totalRecibo').value = valor;
        var x = document.getElementById('boton'+idTipoPago);
        var y = document.getElementById('boton-resta'+idTipoPago);

        x.style.display = "none";
        y.style.display = "block";
        var check_id = document.getElementById(idTipoPago);
        check_id.checked = true;
    });
    
    $('.btn-importe-resta').on('click', function (event) {
        var totalRecibo = parseInt(document.getElementById('totalRecibo').value);
        var idTipoPago = $(this).data('id');
        var importe = parseInt(document.getElementById('importe_'+idTipoPago).value);

        if (totalRecibo >= importe) {
            var valor = totalRecibo - importe;
        }
        document.getElementById('totalRecibo').value = valor;
        var x = document.getElementById('boton'+idTipoPago);
        var y = document.getElementById('boton-resta'+idTipoPago);

        x.style.display = "block";
        var importe = document.getElementById('importe_'+idTipoPago);
        importe.value = '0.00';
        y.style.display = "none";
        var check_id = document.getElementById(idTipoPago);
        check_id.checked = false;
    });
    
    function cambiaTotalRecibo(importe, totalRecibo, idTipoPago){
        var totalRecibo = parseInt(document.getElementById('totalRecibo').value);
        var valor = parseInt(totalRecibo);
        var importe = parseInt(importe);

        if (document.getElementById(idTipoPago).checked)
        {
            var valor = totalRecibo + importe;
        } else {
            var valor = totalRecibo - importe;
        }

        document.getElementById('totalRecibo').value = valor;
        
        return valor;
    }

</script>