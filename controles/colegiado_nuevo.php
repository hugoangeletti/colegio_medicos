<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/paisLogic.php');
require_once ('../dataAccess/universidadLogic.php');
require_once ('../dataAccess/tipoTituloLogic.php');
$tipoTituloLogic = new tipoTituloLogic();
require_once ('../dataAccess/localidadLogic.php');

$_SESSION['menuColegiado'] = "Alta";

$periodoActual = $_SESSION['periodoActual'];
$requerido = 'required=""';
if (isset($_GET['tipo']) || isset($_POST['tipo'])){
    if (isset($_GET['tipo'])) {
        $tipoIngreso = $_GET['tipo'];
    } else {
        $tipoIngreso = $_POST['tipo'];
    }
    $porTipo = TRUE;
    $matriculaReadOnly = '';
    if ($tipoIngreso == 'baja'){
        $esOtroDistrito = FALSE;
        $porTipo = TRUE;
        $panel = "panel-primary";
        $titulo = "Alta de Matricula dada de baja";
        $botonConfirma = "btn-primary";
        $labelFechaMatriculacion = 'Fecha de Matriculación ';
        $fechaMatriculacionReadOnly = 'required=""';
        $requerido = '';
    } else {   
        $esOtroDistrito = TRUE;
        $panel = "panel-warning";
        $titulo = "Alta de Matricula de Otro Distrito";
        $botonConfirma = "btn-warning";
        $labelFechaMatriculacion = 'Fecha de Ingreso ';
        $fechaMatriculacionReadOnly = 'readonly=""';
    }
    $tipoIngreso = '?tipo='.$tipoIngreso;
} else {
    $esOtroDistrito = FALSE;
    $porTipo = FALSE;
    $panel = "panel-success";
    $titulo = "Alta de matriculado del Distrito I";
    $botonConfirma = "btn-success";
    $matriculaReadOnly = 'readonly=""';
    $fechaMatriculacionReadOnly = 'readonly=""';
    $tipoIngreso = '';
    $labelFechaMatriculacion = 'Fecha de Matriculación ';
}
if (isset($_POST['mensaje'])) {
    //vino por error en la carga
    if (isset($_POST['tipoMovimiento'])) {
        $tipoMovimiento = $_POST['tipoMovimiento'];
    } else {
        $tipoMovimiento = '';
    }
    if (isset($_POST['distritoOrigen'])) {
        $distritoOrigen = $_POST['distritoOrigen'];
    } else {
        $distritoOrigen = '';
    }
    if (isset($_POST['fechaOtroDistrito'])) {
        $fechaOtroDistrito = $_POST['fechaOtroDistrito'];
    } else {
        $fechaOtroDistrito = '';
    }
    $tomo = $_POST['tomo'];
    $folio = $_POST['folio'];
    $matricula = $_POST['matricula'];
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    $fechaMatriculacion = $_POST['fechaMatriculacion'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $tipoDocumento = $_POST['tipoDocumento'];
    $idPaises = $_POST['idPaises'];
    $nacionalidad_buscar = $_POST['nacionalidad_buscar'];
    $idTipoTitulo = $_POST['idTipoTitulo'];
    $fechaTitulo = $_POST['fechaTitulo'];
    $tituloDigital = $_POST['tituloDigital'];
    $idUniversidad = $_POST['idUniversidad'];
    $universidad_buscar = $_POST['universidad_buscar'];
    $numeroDocumento = $_POST['numeroDocumento'];
    $matriculaNacional = $_POST['matriculaNacional'];
    $sexo = $_POST['sexo'];
    $calle = $_POST['calle'];
    $numero = $_POST['numero'];
    $lateral = $_POST['lateral'];
    $idLocalidad = $_POST['idLocalidad'];
    $localidad_buscar = $_POST['localidad_buscar'];
    $mail = $_POST['mail'];
    $telefonoFijo = $_POST['telefonoFijo'];
    /*
    $telefonoFijoPrefijo = $_POST['telefonoFijoPrefijo'];
    $telefonoFijo1 = $_POST['telefonoFijo1'];
    $telefonoFijo2 = $_POST['telefonoFijo2'];
     * 
     */
    $telefonoMovil = $_POST['telefonoMovil'];
    $estado = $_POST['estadoMatricular'];
    $codigoPostal = $_POST['codigoPostal'];
    if (isset($_POST['piso'])) {
        $piso = $_POST['piso'];
    } else {
        $piso = NULL;
    }
    if (isset($_POST['depto'])) {
        $depto = $_POST['depto'];
    } else {
        $depto = NULL;
    }
    
    ?>
    <!--<div class="ocultarMensaje">--> 
        <div class="<?php echo $_POST['clase']; ?>" role="alert">
            <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
            <span><?php echo $_POST['mensaje'];?></span>
        </div>
    <!--</div>-->
    
<?php
} else {
    $fechaOtroDistrito = '';
    $tipoMovimiento = '';
    $distritoOrigen = '';
    $maxFolio = 1000;
    $tomo = NULL;
    $folio = NULL;
    $matricula = NULL;
    $apellido = NULL;
    $nombre = NULL;
    if ($tipoIngreso == '?tipo=baja') {
        $fechaMatriculacion = NULL;
    } else {
        $fechaMatriculacion = date('Y-m-d');
    }
    $fechaNacimiento = NULL;
    $tipoDocumento = 3;
    $idPaises = 54;
    $nacionalidad_buscar = "";
    $idTipoTitulo = 1;
    $fechaTitulo = NULL;
    if ($esOtroDistrito) {
        $tituloDigital = NULL;
        $idUniversidad = NULL;
        $universidad_buscar = NULL;
    } else {
        $tituloDigital = 1;
        $idUniversidad = 1;
        $universidad_buscar = "UNIVERSIDAD NACIONAL DE LA PLATA";
    }
    $numeroDocumento = NULL;
    $matriculaNacional = NULL;
    $sexo = NULL;
    $calle = NULL;
    $numero = NULL;
    $piso = NULL;
    $depto = NULL;
    $lateral = NULL;
    $idLocalidad = 1172;
    $localidad_buscar = "LA PLATA";
    $codigoPostal = NULL;
    $mail = NULL;
    $telefonoFijo = NULL;
    /*
    $telefonoFijoPrefijo = NULL;
    $telefonoFijo1 = NULL;
    $telefonoFijo2 = NULL;
     * 
     */
    $telefonoMovil = NULL;
    $tomo = NULL;
    $folio = NULL;
    
    if ($porTipo) {
        $tomo = NULL;
        $tomoFolio = NULL;
        $ultimoTomo = NULL;
        $folio = NULL;
        $tomoDesde = 1;
        $folioDesde = 1;
        if ($tipoIngreso == '?tipo=otro') {
            $colegiadoLogic = new colegiadoLogic();
            $resTomoFolio = $colegiadoLogic->obtenerNuevoTomoFolioOtroDistrito();
            if ($resTomoFolio['estado']) {

                $tomoFolio = $resTomoFolio['datos'];
                $ultimoTomo = $tomoFolio['tomo'];
                $ultimoFolio = $tomoFolio['folio'];
                if ($ultimoFolio == $maxFolio - 501) {
                    $folioDesde = 1;
                } else {
                    $folioDesde = $ultimoFolio;
                }
                $tomo = $ultimoTomo;
                $tomoDesde = $ultimoTomo;
            }
            $tomoHasta = $tomoDesde + 1;
            $folioHasta = $maxFolio;
        } else {
            $tomoHasta = 999;
            $folioHasta = 1000;
        }
    } else {
        $resTomoFolio = $colegiadoLogic->obtenerNuevoTomoFolioMatricula();
        if ($resTomoFolio['estado']) {
            $tomoFolio = $resTomoFolio['datos'];
            $ultimoTomo = $tomoFolio['tomo'];
            $ultimoFolio = $tomoFolio['folio'];
            if ($ultimoFolio == $maxFolio) {
                $folioDesde = 1;
            } else {
                $folioDesde = $ultimoFolio;
            }
            $tomo = $ultimoTomo;
            $folioHasta = $maxFolio;
            $tomoDesde = $ultimoTomo;
            $tomoHasta = $tomoDesde + 1;
            $matricula = $tomoFolio['matricula'];            
        }
    }
}
$fechaLimite = sumarRestarSobreFecha(date('Y-m-d'), 23, 'year', '-');
?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-3"><h4><?php echo $titulo; ?></h4></div>
            <div class="col-md-9"><h4><a href="https://registrograduados.siu.edu.ar/" target="_BLANK">VERFICAR AL NUEVO MATRICULADO EN EL REGISTRO PÚBLICO DE GRADUADOS UNIVERSITARIOS, INGRESANDO AQUI</a>
                <br>Antes de iniciar la carga del nuevo matriculado, debe verificar en el registro que exista como graduado</h4></div>
        </div>
    </div>
    <div class="panel-body">
    <form id="datosAlta" autocomplete="off" name="datosAlta" method="POST" onSubmit="" action="datosColegiado/alta_nuevaMatricula.php<?php echo $tipoIngreso; ?>">
        <div class="row">
            <div class="col-md-3">
                <label>Apellido *</label>
                <input class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" autofocus type="text" name="apellido" value="<?php echo $apellido; ?>" required=""/>
            </div>
            <div class="col-md-3">
                <label>Nombre *</label>
                <input class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" name="nombre" value="<?php echo $nombre; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>Matrícula *</label>
                <input class="form-control" type="text" name="matricula" value="<?php echo $matricula; ?>" <?php echo $matriculaReadOnly; ?> maxlength="6" required/>
            </div>
            <div class="col-md-2">
                <label><?php echo $labelFechaMatriculacion ?></label>
                <input class="form-control" type="date" name="fechaMatriculacion" value="<?php echo $fechaMatriculacion; ?>" <?php echo $fechaMatriculacionReadOnly; ?>/>
            </div>
            <div class="col-md-1">
                <label>Tomo *</label>
                <input class="form-control" type="number" min="<?php echo $tomoDesde ?>" max="<?php echo $tomoHasta ?>" name="tomo" placeholder="<?php echo $ultimoTomo; ?>" value="<?php echo $tomo; ?>" required=""/>
            </div>
            <div class="col-md-1">
                <label>Folio *</label>
                <!--<input class="form-control" type="number" name="folio" placeholder="<?php //echo $ultimoFolio; ?>" value="<?php echo $folio; ?>" required=""/>-->
                <input class="form-control" type="number" min="<?php echo $folioDesde ?>" max="<?php echo $folioHasta ?>" name="folio" placeholder="<?php //echo $ultimoFolio; ?>" value="<?php echo $folio; ?>" required=""/>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-2">
                <label>N&ordm; de Documento *</label>
                <input class="form-control" type="number" name="numeroDocumento" value="<?php echo $numeroDocumento; ?>" min="1000000" maxlength="8" required=""/>
            </div>
            <div class="col-md-2">
                <label>Fecha de Nacimiento *</label>
                <input class="form-control" type="date" name="fechaNacimiento" max="<?php echo $fechaLimite; ?>" value="<?php echo $fechaNacimiento; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>Sexo *</label>
                <select class="form-control" id="sexo" name="sexo" required="">
                    <option value="" selected>Seleccione sexo</option>
                    <option value="F" <?php if($sexo == 'F') { ?> selected <?php } ?>>Femenino</option>
                    <option value="M" <?php if($sexo == 'M') { ?> selected <?php } ?>>Masculino</option>
                </select>            
            </div>
            <div class="col-md-3">
                <label>Matr&iacute;cula Nacional </label>
                <input class="form-control" type="text" name="matriculaNacional" value="<?php $matriculaNacional; ?>" maxlength="6" />
            </div>
            <div class="col-md-3">
                <label>Nacionalidad *</label>
                <input class="form-control" autocomplete="OFF" type="text" name="nacionalidad_buscar" id="nacionalidad_buscar" value="<?php echo $nacionalidad_buscar ?>" placeholder="Ingrese nacionalidad a buscar" required=""/>
                <input type="hidden" name="idPaises" id="idPaises" value="<?php echo $idPaises; ?>" required="" />
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <div class="row">
            <div class="col-md-3">
                <label>T&iacute;tulo *</label>
                <select class="form-control" id="idTipoTitulo" name="idTipoTitulo" required="">
                    <?php
                    $resTipoTitulo = $tipoTituloLogic->obtenerTiposTitulo();
                    if ($resTipoTitulo['estado']) {
                        foreach ($resTipoTitulo['datos'] as $row) {
                        ?>
                            <option value="<?php echo $row['id'] ?>" <?php if($idTipoTitulo == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                        <?php
                        }
                    } else {
                        echo $resTipoTitulo['mensaje'];
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Fecha T&iacute;tulo * </label>
                <input class="form-control" type="date" name="fechaTitulo" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaTitulo; ?>" required=""/>
            </div>
            <div class="col-md-1">
                <?php 
                //if (!$porTipo || $esOtroDistrito) {
                if (!$porTipo) {
                ?>
                    <label>Título Digital *</label>
                    <select class="form-control" id="tituloDigital" name="tituloDigital" required="">
                        <?php
                        if ($porTipo) {
                        ?>
                            <option value="" selected>Seleccione</option>
                        <?php
                        }
                        ?>
                        <option value="1" <?php if(isset($tituloDigital) && $tituloDigital == 1) { ?> selected <?php } ?>>SI</option>
                        <option value="0" <?php if(isset($tituloDigital) && $tituloDigital == 0) { ?> selected <?php } ?>>NO</option>
                    </select>            
                <?php 
                } else {
                ?>
                    <input type="hidden" name="tituloDigital" id="tituloDigital" value="0" />
                <?php 
                }
                ?>
            </div>
            <div class="col-md-6">
                <label>Otorgado por *</label>
                <input class="form-control" type="text" name="universidad_buscar" id="universidad_buscar" value="<?php echo $universidad_buscar; ?>" placeholder="Ingrese universidad a buscar" required=""/>
                <input type="hidden" name="idUniversidad" id="idUniversidad" value="<?php echo $idUniversidad; ?>" required="" />
            </div>
        </div>
        <div class="row">&nbsp;</div>
        
        <div class="row">
            <div class="col-md-3">
                <label>Calle *</label>
                <input class="form-control" style="text-transform:uppercase;" type="text" name="calle" value="<?php echo $calle; ?>" required=""/>
            </div>
            <div class="col-md-1">
                <label>Nº *</label>
                <input class="form-control" type="text" name="numero" value="<?php echo $numero; ?>" required=""/>
            </div>
            <div class="col-md-1">
                <label>Piso </label>
                <input class="form-control" type="text" name="piso" value="<?php echo $piso; ?>"/>
            </div>
            <div class="col-md-1">
                <label>Depto. </label>
                <input class="form-control" type="text" name="depto" value="<?php echo $depto; ?>"/>
            </div>
            <div class="col-md-3">
                <label>Lateral </label>
                <input class="form-control" style="text-transform:uppercase;" type="text" name="lateral" value="<?php echo $lateral; ?>" />
            </div>
            <div class="col-md-3">
                <label>Localidad *</label>
                <input class="form-control" type="text" name="localidad_buscar" id="localidad_buscar" value="<?php echo $localidad_buscar; ?>" placeholder="Ingrese universidad a buscar" required=""/>
                <input type="hidden" name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad; ?>" required="" />
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-2">
                <label>C&oacute;digo Postal *</label>
                <input class="form-control" type="text" name="codigoPostal" value="<?php echo $codigoPostal; ?>" required=""/>
            </div>
            <div class="col-md-4">
                <label>Email *</label>
                <input class="form-control" type="email" name="mail" value="<?php echo $mail; ?>" <?php echo $requerido ?>/>
            </div>
            <div class="col-md-2">
                <label>Tel&eacute;fono fijo *</label>
                <input class="form-control" type="text" name="telefonoFijo" value="<?php echo $telefonoFijo; ?>" <?php echo $requerido ?>/>
            </div>
            <div class="col-md-2">
                <label>Tel&eacute;fono M&oacute;vil *</label>
                <input class="form-control" type="tel" name="telefonoMovil" value="<?php echo $telefonoMovil; ?>" <?php echo $requerido ?>/>
            </div>
            <div class="col-md-2 text-center">
                <br>
                <button type="submit"  class="btn <?php echo $botonConfirma; ?> btn-lg" >Siguiente </button>
            </div>
        </div>
    </form>
    <br>
    <a href="http://consejosuperior.com.ar/signin" target="_BLANK">VERFICAR AL NUEVO MATRICULADO EN EL SISTEMA UNIFICADO</a>
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
    
    $(function(){
        var nameIdMap = {};
        $('#nacionalidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'nacionalidad.php',
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
                $('#idPaises').val(nameIdMap[item]);
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
        $('#universidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'universidad.php',
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
                $('#idUniversidad').val(nameIdMap[item]);
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