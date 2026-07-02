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

$periodoActual = $_SESSION['periodoActual'];

if (isset($_POST['mensaje'])) {
    //vino por error en la carga
    $tipoIngreso = $_POST['tipoIngreso'];
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
    $maxFolio = 500;
    
    $tipoIngreso = '';
    $tomo = NULL;
    $folio = NULL;
    $matricula = NULL;
    $apellido = NULL;
    $nombre = NULL;
    $fechaMatriculacion = date('Y-m-d');
    $fechaNacimiento = NULL;
    $tipoDocumento = 3;
    $idPaises = 54;
    $nacionalidad_buscar = "Argentina";
    $idTipoTitulo = 1;
    $fechaTitulo = NULL;
    $idUniversidad = 1;
    $universidad_buscar = "UNIVERSIDAD NACIONAL DE LA PLATA";
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
    $telefonoMovil = NULL;
    $estado = 1;
    $tomo = NULL;
    $folio = NULL;
    $colegiadoLogic = new colegiadoLogic();
    $resTomoFolio = $colegiadoLogic->obtenerNuevoTomoFolioOtroDistrito();
    if ($resTomoFolio['estado']) {
        $tomoFolio = $resTomoFolio['datos'];
        $ultimoTomo = $tomoFolio['tomo'];
        $ultimoFolio = $tomoFolio['folio'];
        if ($ultimoFolio == $maxFolio) {
            $folioDesde = 1;
        } else {
            $folioDesde = $ultimoFolio;
        }
        $folioHasta = $maxFolio;
        $tomoDesde = $ultimoTomo;
        $tomoHasta = $tomoDesde + 1;
    }
}
$dia = date('d');
$mes = date('m');
$anio = date('Y') - 23;
$fechaLimite = $anio.'-'.$mes.'-'.$dia;
?>
<div class="panel panel-warning">
    <div class="panel-heading">
        <h4>Alta de Matricula de Otro Distrito </h4>
    </div>
    <div class="panel-body">
    <form id="datosAlta" autocomplete="off" name="datosAlta" method="POST" onSubmit="" action="datosColegiado/alta_nuevaMatricula.php">
        <div class="row">
            <div class="col-md-4">
                <label>Tipo ingreso *</label><br>
                <label class="radio-inline"><input type="radio" name="tipoIngreso" id="tipoIngreso" value="I" <?php if ($tipoIngreso == 'I') { ?> checked="" <?php } ?>>Inscripto al Distrito I</label>
                <label class="radio-inline"><input type="radio" name="tipoIngreso" id="tipoIngreso" value="C" <?php if ($tipoIngreso == 'C') { ?> checked="" <?php } ?>>Colegiado del Distrito I</label>
            </div>
        </div>
        <div class="row">&nbsp;</div>
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
                <label>Matr&iacute;cula *</label>
                <input class="form-control" type="text" name="matricula" value="<?php echo $matricula; ?>" maxlength="6" required=""/>
            </div>
            <div class="col-md-2">
                <label>Fecha de Ingreso *</label>
                <input class="form-control" type="date" name="fechaMatriculacion" value="<?php echo $fechaMatriculacion; ?>" required=""/>
            </div>
            <div class="col-md-1">
                <label>Tomo</label>
                <input class="form-control" type="number" min="<?php echo $tomoDesde ?>" max="<?php echo $tomoHasta ?>" name="tomo" placeholder="<?php echo $ultimoTomo; ?>" value="<?php echo $tomo; ?>" required=""/>
            </div>
            <div class="col-md-1">
                <label>Folio</label>
                <input class="form-control" type="number" min="<?php echo $folioDesde ?>" max="<?php echo $folioHasta ?>" name="folio" placeholder="<?php echo $ultimoFolio; ?>" value="<?php echo $folio; ?>" required=""/>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-2">
                <label>N&ordm; de Documento *</label>
                <input class="form-control" type="number" name="numeroDocumento" value="<?php echo $numeroDocumento; ?>" required=""/>
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
                <input class="form-control" type="text" name="matriculaNacional" value="<?php $matriculaNacional; ?>" />
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
            <div class="col-md-3">
                <label>Fecha T&iacute;tulo * </label>
                <input class="form-control" type="date" name="fechaTitulo" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaTitulo; ?>" required=""/>
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
                <input class="form-control text-uppercase" type="text" name="calle" value="<?php echo $calle; ?>" required=""/>
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
                <label>Lateral *</label>
                <input class="form-control text-uppercase" type="text" name="lateral" value="<?php echo $lateral; ?>" required=""/>
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
                <input class="form-control" type="email" name="mail" value="<?php echo $mail; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>Tel&eacute;fono fijo *</label>
                <input class="form-control" type="text" name="telefonoFijo" value="<?php echo $telefonoFijo; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>Tel&eacute;fono M&oacute;vil *</label>
                <input class="form-control" type="text" name="telefonoMovil" value="<?php echo $telefonoMovil; ?>" required=""/>
            </div>
            <div class="col-md-2 text-right">
                <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                <input type="hidden" name="tipoDocumento" id="tipoDocumento" value="<?php echo $tipoDocumento; ?>" />
                <input type="hidden" name="estadoMatricular" id="estadoMatricular" value="<?php echo $estado; ?>" />
            </div>
        </div>
    </form>
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