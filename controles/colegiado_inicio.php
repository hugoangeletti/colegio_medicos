<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
?>
<div class="container-fluid">
    <h4><b>Consulta de colegiados</b></h4>
    <ul class="nav nav-tabs">
        <li class="active"><a href="colegiado_inicio.php">Inicio</a></li>
        <li><a href="colegiado_especialista">Especialidades</a></li>
        <li><a href="#">Tesorer&iacute;a</a></li>
    </ul>
    <div class="row">&nbsp;</div>
    <?php
    if (!isset($_POST['idColegiado'])) {
    ?>
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_inicio.php">
            <div class="row">
                <div class="col-md-3" style="text-align: right;">
                    <label>Matr&iacute;cula o Apellido y Nombre *</label>
                </div>
                <div class="col-md-7">
                    <input class="form-control" autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                    <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                </div>
                <div class="col-md-2">
                    <button type="submit"  class="btn btn-success " >Confirma colegiado</button>
                </div>
            </div>
        </form>
    <?php
    } else {
        $idColegiado = $_POST['idColegiado'];
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
        ?>
            <div class="row">
                <div class="col-md-5">
                    <label>Apellido y Nombres</label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>" readonly=""/>
                </div>
                <div class="col-md-1">
                    <label>Matr&iacute;cula</label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['matricula']; ?>" readonly=""/>
                </div>
                <div class="col-md-2">
                    <label>Tipo y N&ordm; de Documento</label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['tipoDocumento'].' - '.$colegiado['numeroDocumento']; ?>" readonly=""/>
                </div>
                <div class="col-md-2">
                    <label>Fecha de Nacimiento</label>
                    <input class="form-control" type="text" value="<?php echo cambiarFechaFormatoParaMostrar($colegiado['fechaNacimiento']).'  -  '.calcular_edad($colegiado['fechaNacimiento']); ?>" readonly=""/>
                </div>
                <div class="col-md-2">
                    <label>Nacionalidad</label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['nacionalidad']; ?>" readonly=""/>
                </div>
            </div>

            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-3">
                    <label>Matriculado el</label>
                    <input class="form-control" type="text" value="<?php echo cambiarFechaFormatoParaMostrar($colegiado['fechaMatriculacion']).'  -  '.calcular_edad($colegiado['fechaMatriculacion']); ?>" readonly=""/>
                </div>
                <div class="col-md-1">
                    <label>Tomo</label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['tomo']; ?>" readonly=""/>
                </div>
                <div class="col-md-1">
                    <label>Folio</label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['folio']; ?>" readonly=""/>
                </div>
                <div class="col-md-3">
                    <label>Matr&iacute;cula Nacional</label>
                    <input class="form-control" type="text" value="<?php if ($colegiado['matriculaNacional'] != "") { echo $colegiado['matriculaNacional']; } else { echo 'No registra'; } ?>" readonly=""/>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                    <?php
                    $resColegiadoTitulo = $colegiadoLogic->obtenerTitulosPorColegiado($idColegiado);
                    if ($resColegiadoTitulo['estado'] && $resColegiadoTitulo['datos']){
                        $dato = $resColegiadoTitulo['datos'];
//                        foreach ($resColegiadoTitulo['datos'] as $dato) {
//                            echo 'Pasos';
                        ?>
                            <div class="col-md-3">
                                <label>T&iacute;tulo</label>
                                <input class="form-control" type="text" value="<?php echo $dato['tipoTitulo']; ?>" readonly=""/>
                            </div>
                            <div class="col-md-6">
                                <label>Universidad</label>
                                <input class="form-control" type="text" value="<?php echo $dato['universidad']; ?>" readonly=""/>
                            </div>
                            <div class="col-md-3">
                                <label>Fecha T&iacute;tulo</label>
                                <input class="form-control" type="text" value="<?php echo cambiarFechaFormatoParaMostrar($dato['fechaTitulo']).'  -  '.calcular_edad($dato['fechaTitulo']); ?>" readonly=""/>
                            </div>
                        <?php
//                        }
                    }
                    ?>
            </div>
        <?php
        } else {
        ?>
            <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
                <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
            </div>        
        <?php
        }
    ?>
        <div class="row">&nbsp;</div>
        <div class="col-md-12" style="text-align:right;">
            <form  method="POST" action="colegiado_inicio.php">
                <button type="submit" class="btn btn-info" name='volver' id='name'>Buscar Otro colegiado </button>
            </form>
        </div>  
    <?php
    }
    ?>
    </div>
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
        $('#sumarianteTitular').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'sumariante.php',
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
                $('#idSumarianteTitular').val(nameIdMap[item]);
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
        $('#sumarianteSuplente').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'sumariante.php',
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
                $('#idSumarianteSuplente').val(nameIdMap[item]);
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
        $('#secretarioadhoc').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'secretarioadhoc.php',
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
                $('#idSecretarioadhoc').val(nameIdMap[item]);
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
    
    $(document).ready(function() {
    $("input[type=radio]").click(function(event){
        var valor = $(event.target).val();
        if(valor =="S"){
            $("#sumariantes").show();
        } else if (valor == "A") {
            $("#sumariantes").hide();
        } else { 
            // Otra cosa
        }
    });
});
</script>