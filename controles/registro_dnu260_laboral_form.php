<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();

$_SESSION['menuColegiado'] = "AltaRegistro";

$requerido = 'required=""';
$panel = "panel-danger";
$titulo = "Alta Lugar de trabajo";
$botonConfirma = "btn-success";
$matriculaReadOnly = 'readonly=""';
$fechaMatriculacionReadOnly = 'readonly=""';
$tipoIngreso = '';
$labelFechaMatriculacion = 'Fecha de Matriculación ';
$accion = $_POST['accion'];
$continua = TRUE;
if (isset($_GET['idRegistro']) && $_GET['idRegistro'] > 0) {
    $idRegistro = $_GET['idRegistro'];
    $resRegistro = $registroDNU260Logic->obtenerRegistroPorId($idRegistro);
    if ($resRegistro['estado']) {
        $registro = $resRegistro['datos'];
        $apellido = $registro['apellido'];
        $nombre = $registro['nombre'];
        $numero = $registro['numero'];
        $titulo = "Datoa laborales del Registro Número: ".$numero.' - '.trim($apellido).', '.trim($nombre); 
    } else {
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

if ($accion == 3 && isset($_GET['id']) && $_GET['id'] > 0) {
    $idRegistroLaboral = $_GET['id'];
    $resRegistro = $registroDNU260Logic->obtenerDatosLaboralesPorId($idRegistroLaboral);
    if ($resRegistro['estado']) {
        $registro = $resRegistro['datos'];
        $domicilioProfesional =  $registro['domicilioProfesional'];
        $localidadProfesional = $registro['localidadProfesional'];
        $codigoPostalProfesional = $registro['codigoPostalProfesional'];
        $entidad = $registro['entidad'];
        $telefonoProfesional = $registro['telefonoProfesional'];
    } else {
        $continua = FALSE;
    }
} else {
    if ($accion == 1) {
        $idRegistroLaboral = NULL;
    } else {
        $continua = FALSE;
    }
}

if ($continua) {
    if (isset($_POST['mensaje'])) {
        //var_dump($_POST);
        $domicilioProfesional = $_POST['domicilioProfesional'];
        $localidadProfesional = $_POST['localidadProfesional'];
        $codigoPostalProfesional = $_POST['codigoPostalProfesional'];
        $entidad = $_POST['entidad'];
        $telefonoProfesional = $_POST['telefonoProfesional'];
        ?>
        <!--<div class="ocultarMensaje">--> 
            <div class="<?php echo $_POST['clase']; ?>" role="alert">
                <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
                <span><?php echo $_POST['mensaje'];?></span>
            </div>
        <!--</div>-->
        
    <?php
    } else {
        if ($accion == 1) {
            $domicilioProfesional = NULL;
            $localidadProfesional = NULL;
            $codigoPostalProfesional = NULL;
            $entidad = NULL;
            $telefonoProfesional = "NO REGISTRA";
        }    
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
                <form  method="POST" action="registro_dnu260_laboral_lista.php?id=<?php echo $idRegistro; ?>">
                    <button type="submit" class="btn btn-danger" name='volver' id='name'>Volver al listado </button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <form id="datosAlta" autocomplete="off" name="datosAlta" method="POST" onSubmit="" action="datosRegistroDnu260/abm_laboral.php">
        <div class="row">
            <div class="col-md-3">
                <label>Entidad/Institución *</label>
                <input class="form-control text-uppercase" type="text" autofocus="" name="entidad" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?php echo $entidad; ?>" required=""/>
            </div>
            <div class="col-md-4">
                <label>Domicilio Profesional (Calle y Nº) *</label>
                <input class="form-control text-uppercase" type="text" name="domicilioProfesional" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?php echo $domicilioProfesional; ?>" required=""/>
            </div>
            <div class="col-md-3">
                <label>Localidad *</label>
                <input class="form-control" type="text" name="localidadProfesional" id="localidadProfesional" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?php echo $localidadProfesional; ?>" required=""/>
                <input type="hidden" name="idLocalidadProfesional" id="idLocalidadProfesional" />
            </div>
            <div class="col-md-2">
                <label>C.P. </label>
                <input class="form-control" type="text" name="codigoPostalProfesional" value="<?php echo $codigoPostalProfesional; ?>"/>
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <div class="row">
            <div class="col-md-2">
                <label>Tel&eacute;fono profesional *</label>
                <input class="form-control" type="text" name="telefonoProfesional" value="<?php echo $telefonoProfesional; ?>" <?php echo $requerido ?>/>
            </div>
            <div class="col-md-2 text-center">
                <br>
                <button type="submit"  class="btn <?php echo $botonConfirma; ?> btn-lg" >Confirma </button>
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                <input type="hidden" name="idRegistro" id="idRegistro" value="<?php echo $idRegistro; ?>" />
                <input type="hidden" name="id" id="id" value="<?php echo $idRegistroLaboral; ?>" />
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