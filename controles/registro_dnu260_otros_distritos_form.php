<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260OtrosDistritosLogic.php');
$registroDNU260OtrosDistritosLogic = new registroDNU260OtrosDistritosLogic();

//$_SESSION['menuColegiado'] = "AltaRegistro";

$accion = $_POST['accion'];

$panel = "panel-primary";
$titulo = " de Bajas al Registro de extranjeros de Otro Distrito";
$botonVolver = "btn-primary";
if ($accion == '1') {
    $titulo = "Carga".$titulo;
} else {
    $titulo = 'Modificaión'.$titulo;
}

$continua = TRUE;
if (isset($_POST['mensaje'])) {
    ?>
    <div class="ocultarMensaje">
        <div class="<?php echo $_POST['clase']; ?>" role="alert">
            <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
            <span><?php echo $_POST['mensaje'];?></span>
        </div>
    </div>
    <?php
    //var_dump($_POST);
    $apellido = $_POST['apellido'];
    $nombre = $_POST['nombre'];
    $fechaAlta = $_POST['fechaAlta'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $idTipoDocumento = $_POST['idTipoDocumento'];
    $idPais = $_POST['idPais'];
    $nacionalidad_buscar = $_POST['nacionalidad_buscar'];
    $fechaTitulo = $_POST['fechaTitulo'];
    $universidad = $_POST['universidad'];
    $especialidad = $_POST['especialidad'];
    $numeroDocumento = $_POST['numeroDocumento'];
    $numeroPasaporte = $_POST['numeroPasaporte'];
    $sexo = $_POST['sexo'];
    $fechaBaja = $_POST['fechaBaja'];
    $numero = $_POST['numeroRegistro'];
    $distrito = $_POST['distrito'];
    $observacion = $_POST['observacion'];
} else {
    if ($accion == 3) {
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idRegistro = $_GET['id'];
            $resRegistro = $registroDNU260OtrosDistritosLogic->obtenerRegistroOtrosDistritosPorId($idRegistro);
            if ($resRegistro['estado']) {
                $registro = $resRegistro['datos'];
                $fechaAlta = $registro['fechaAlta'];
                $apellido = $registro['apellido'];
                $nombre = $registro['nombre'];
                $fechaNacimiento = $registro['fechaNacimiento'];
                $idTipoDocumento = $registro['idTipoDocumento'];
                $idPais = $registro['idPais'];
                $nacionalidad_buscar = $registro['nacionalidad'];
                $fechaTitulo = $registro['fechaTitulo'];
                $universidad = $registro['universidad'];
                $numeroDocumento = $registro['numeroDocumento'];
                $numeroPasaporte = $registro['numeroPasaporte'];
                $sexo = $registro['sexo'];
                $especialidad = $registro['especialidad'];
                $numero = $registro['numero'];
                $titulo .= " Registro Número: ".$numero; 
                $fechaBaja = $registro['fechaBaja'];
                $distrito = $registro['distrito'];
                $observacion = $registro['observacion'];
            } else {
                $continua = FALSE;
            }
        } else {
            $continua = FALSE;
        }
    } else {
        $idRegistro = NULL;
        $fechaIngreso = NULL;
        $apellido = NULL;
        $nombre = NULL;
        $fechaNacimiento = NULL;
        $idTipoDocumento = NULL;
        $idPais = NULL;
        $nacionalidad_buscar = NULL;
        $fechaTitulo = NULL;
        $universidad = NULL;
        $numeroDocumento = NULL;
        $numeroPasaporte = NULL;
        $sexo = NULL;
        $especialidad = NULL;
        $fechaBaja = NULL;
        $numero = NULL;
        $distrito = NULL;
        $observacion = NULL;
    }    
}
if ($continua) {
?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><?php echo $titulo; ?></h4>
            </div>
            <div class="col-md-3 text-right">
                <form  method="POST" action="registro_dnu260_otros_distritos_lista.php">
                    <button type="submit" class="btn <?php echo $botonVolver; ?>" name='volver' id='name'>Volver al listado </button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <form id="datosAlta" autocomplete="off" name="datosAlta" method="POST" onSubmit="" action="datosRegistroDnu260/alta_registro_otros_distritos.php">
        <div class="row">
            <div class="col-md-3">
                <label>Número de Registro *</label>
                <input class="form-control" type="text" name="numeroRegistro" value="<?php echo $numero; ?>" required="" autofocus="" />
            </div>
            <div class="col-md-2">
                <label>Distrito de origen *</label>
                <input class="form-control" type="number" name="distrito" value="<?php echo $distrito; ?>" required="" min="2" max="10" />
            </div>
            <div class="col-md-2">
                <label>Fecha de Alta </label>
                <input class="form-control" type="date" name="fechaAlta" value="<?php echo $fechaAlta; ?>" />
            </div>
            <div class="col-md-2">
                <label>Fecha de Baja </label>
                <input class="form-control" type="date" name="fechaBaja" value="<?php echo $fechaBaja; ?>" />
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <div class="row">
            <div class="col-md-3">
                <label>Apellido *</label>
                <input class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" name="apellido" value="<?php echo $apellido; ?>" required="" <?php if ($distrito == '1') { echo 'autofocus=""'; } ?>/>
            </div>
            <div class="col-md-3">
                <label>Nombre *</label>
                <input class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" name="nombre" value="<?php echo $nombre; ?>" required=""/>
            </div>
            <div class="col-md-4">
                <label>Nacionalidad *</label>
                <input class="form-control" autocomplete="OFF" type="text" name="nacionalidad_buscar" id="nacionalidad_buscar" value="<?php echo $nacionalidad_buscar ?>" placeholder="Ingrese nacionalidad a buscar" required=""/>
                <input type="hidden" name="idPais" id="idPais" value="<?php echo $idPais; ?>" required="" />
            </div>
            <div class="col-md-2">
                <label>Sexo *</label>
                <select class="form-control" id="sexo" name="sexo" required="">
                    <option value="" selected>Seleccione sexo</option>
                    <option value="F" <?php if($sexo == 'F') { ?> selected <?php } ?>>Femenino</option>
                    <option value="M" <?php if($sexo == 'M') { ?> selected <?php } ?>>Masculino</option>
                </select>            
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-2">
                <label>Fecha de Nacimiento </label>
                <input class="form-control" type="date" name="fechaNacimiento" value="<?php echo $fechaNacimiento; ?>" />
            </div>            
            <div class="col-md-2">
                <label>Tipo Documento </label>
                <select class="form-control" id="idTipoDocumento" name="idTipoDocumento" >
                    <option value="" selected>Seleccione</option>
                    <option value="3" <?php if($idTipoDocumento == '3') { ?> selected <?php } ?>>DNI</option>
                    <option value="4" <?php if($idTipoDocumento == '4') { ?> selected <?php } ?>>CI</option>
                    <option value="5" <?php if($idTipoDocumento == '5') { ?> selected <?php } ?>>Otro</option>
                </select>            
            </div>
            <div class="col-md-2">
                <label>N&ordm; de Documento </label>
                <input class="form-control" type="text" name="numeroDocumento" value="<?php echo $numeroDocumento; ?>"/>
            </div>
            <div class="col-md-2">
                <label>N&ordm; de Pasaporte </label>
                <input class="form-control" type="text" name="numeroPasaporte" value="<?php echo $numeroPasaporte; ?>"/>
            </div>            
        </div>
        <div class="row">&nbsp;</div>

        <div class="row">
            <div class="col-md-6">
                <label>Universidad </label>
                <input class="form-control" type="text" name="universidad" id="universidad" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?php echo $universidad; ?>" placeholder="Nombre de la Universidad" />
                <input type="hidden" name="idUniversidad" id="idUniversidad" />
            </div>
            <div class="col-md-3">
                <label>Fecha T&iacute;tulo </label>
                <input class="form-control" type="date" name="fechaTitulo" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaTitulo; ?>" />
            </div>
            <div class="col-md-3">
                <label>Especialidad </label>
                <input class="form-control" type="text" name="especialidad" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?php echo $especialidad; ?>" placeholder="Nombre de la Especialidad" />
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <div class="row">
            <div class="col-md-12">
                <label>Observaciones: </label>
                <textarea class="form-control" name="observacion" id="observacion" rows="4" ><?php echo $observacion; ?> </textarea>
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <div class="row">
            <div class="col-md-2 text-center">
                <br>
                <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                <input type="hidden" name="id" id="id" value="<?php echo $idRegistro; ?>" />
            </div>
        </div>
    </form>
    </div>
</div>
<?php
} else {
?>
    <div class="row">
        <div class="col-md-12 alert alert-danger">MAL INGRESO</div>
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
                $('#idPais').val(nameIdMap[item]);
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
        $('#universidad').typeahead({ 
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

    $(function(){
        var nameIdMap = {};
        $('#localidadParticular').typeahead({ 
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
                $('#idLocalidadParticular').val(nameIdMap[item]);
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
        $('#localidadProfesional').typeahead({ 
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
                $('#idLocalidadProfesional').val(nameIdMap[item]);
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