<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoRematriculacionLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
} else {
    $accion = 1;
}
$colegiadoRematriculacionLogic = new colegiadoRematriculacionLogic();
switch ($accion) {
    case 1:
        $titulo = 'Nuevo Consultorio Declarado';
        $requerido = 'required=""';
        if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
            $idColegiado = $_GET['idColegiado'];
            $idConsultorio = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiado - ";
        }    
        break;

    case 2:
        $titulo = 'Eliminar Consultorio Declarado';
        $requerido = '';
        break;

    case 3:
        $titulo = 'Editar Consultorio Declarado';
        $requerido = 'required=""';
        if (isset($_GET['id'])) {
            $idConsultorio = $_GET['id'];
        } else {
            if (isset($_POST['idConsultorio'])) {
                $idConsultorio = $_POST['idConsultorio'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta idConsultorio - ";
            }
        }
        if (isset($idConsultorio)) {
            $resConsultorio = $colegiadoRematriculacionLogic->obtenerConsultorioDeclaradoPorId($idConsultorio);
            if ($resConsultorio['estado']) {
                $consultorio = $resConsultorio['datos'];
                $idColegiado = $consultorio['idColegiado'];
                $entidad = $consultorio['entidad'];
                $calle = $consultorio['calle'];
                $lateral = $consultorio['lateral'];
                $numero = $consultorio['numero'];
                $piso = $consultorio['piso'];
                $departamento = $consultorio['departamento'];
                $codigoPostal = $consultorio['codigoPostal'];
                $telefono = $consultorio['telefono'];
                $idEntidad = $consultorio['idEntidad'];
                $idLocalidad = $consultorio['idLocalidad'];
                $nombreLocalidad = $consultorio['nombreLocalidad'];
                $nombreEntidad = $consultorio['nombreEntidad'];
            } else {
                $continua = FALSE;
                $mensaje .= $resConsultorio['mensaje']." - ";
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idConsultorio - ";
        }
        break;

    default:
        $titulo = 'Consultorio';
        break;
}
if ($continua) {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
    
        if (isset($_POST['mensaje'])) {
        ?>
            <div class="ocultarMensaje"> 
                <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
            </div>
            <?php
            if (isset($_POST['idConsultorio']) && $_POST['idConsultorio'] <> "") {
                $idConsultorio = $_POST['idConsultorio'];
            }
            $idColegiado = $_POST['idColegiado'];
            $entidad = $_POST['entidad'];
            $calle = $_POST['calle'];
            $lateral = $_POST['lateral'];
            $numero = $_POST['numero'];
            $piso = $_POST['piso'];
            $departamento = $_POST['departamento'];
            $codigoPostal = $_POST['codigoPostal'];
            $telefono = $_POST['telefono'];
            $idEntidad = $_POST['idEntidad'];
            $idLocalidad = $_POST['idLocalidad'];
            $nombreLocalidad = $_POST['nombreLocalidad'];
            $nombreEntidad = $_POST['nombreEntidad'];
        } else {
            if (!isset($idConsultorio)) {
                $entidad = NULL;
                $calle = '';
                $numero = '';
                $lateral = '';
                $piso = '';
                $departamento = '';
                $telefono = '';
                $idLocalidad = '';
                $nombreLocalidad = '';
                $codigoPostal = '';
                $idEntidad = NULL;
                $nombreEntidad = '';
            }
        }
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Consultorio Declarado</h4>
                </div>
                <div class="col-md-3 text-left">
                    <a href="colegiado_datos_profesionales.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-primary btn-sm">Volver a Datos Profesionales</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-5">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center"><h4><b><?php echo $titulo; ?></b></h4></div>
        </div>

        <form id="datosConsultorio" autocomplete="off" name="datosConsultorio" method="POST" onSubmit="" action="datosRematriculacion/abm_consultorio.php">
            <div class="row">
                <div class="col-md-4">
                    <label>Calle *</label>
                    <input class="form-control" type="text" id="calle" name="calle" value="<?php echo $calle; ?>" <?php echo $requerido; ?>/>
                </div>
                <div class="col-md-1">
                    <label>Nº *</label>
                    <input class="form-control" type="text" id="numero" name="numero" value="<?php echo $numero; ?>"  <?php echo $requerido; ?>/>
                </div>
                <div class="col-md-1">
                    <label>Piso</label>
                    <input class="form-control" type="text" id="piso" name="piso" value="<?php echo $piso; ?>" />
                </div>
                <div class="col-md-1">
                    <label>Dpto</label>
                    <input class="form-control" type="text" id="departamento" name="departamento" value="<?php echo $departamento; ?>" />
                </div>
                <div class="col-md-3">
                    <label>Lateral *</label>
                    <input class="form-control" type="text" id="lateral" name="lateral" value="<?php echo $lateral; ?>"  <?php echo $requerido; ?>/>
                </div>
                <div class="col-md-2">
                    <label>Teléfono</label>
                    <input class="form-control" type="text" id="telefono" name="telefono" value="<?php echo $telefono; ?>" />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-4">
                    <label>Localidad *</label>
                    <input class="form-control" autocomplete="OFF" type="text" name="nombreLocalidad" id="nombreLocalidad" placeholder="Ingrese Localidad" value="<?php echo $nombreLocalidad; ?>"  <?php echo $requerido; ?>/>
                    <input type="hidden" name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad; ?>"  <?php echo $requerido; ?> />
                </div>
                <div class="col-md-1">
                    <label>C&oacute;digo Postal *</label>
                    <input class="form-control" type="text" name="codigoPostal" id="codigoPostal" value="<?php echo $codigoPostal; ?>"  <?php echo $requerido; ?>/>
                </div>
                <div class="col-md-4">
                    <label>Entidad *</label>
                    <input class="form-control" autocomplete="OFF" type="text" name="nombreEntidad" id="nombreEntidad" placeholder="Ingrese Entidad" value="<?php echo $nombreEntidad; ?>"  <?php echo $requerido; ?>/>
                    <input type="hidden" name="idEntidad" id="idEntidad" value="<?php echo $idEntidad; ?>"  <?php echo $requerido; ?> />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    <?php if (isset($idConsultorio) && $idConsultorio <> "") { ?>
                            <input type="hidden" name="idConsultorio" id="idConsultorio" value="<?php echo $idConsultorio; ?>" />
                    <?php } ?>
                </div>
            </div>    
        </form>
    </div>    
    </div>
    <?php
    } else {
    ?>
        <div class="col-md-12">
            <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
                <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
            </div>        
        </div>
    <?php
    }
} else {
?>
    <div class="col-md-12">
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
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
        $('#nombreLocalidad').typeahead({ 
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
        $('#nombreEntidad').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'entidad_completo.php',
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
                $('#idEntidad').val(nameIdMap[item]);
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