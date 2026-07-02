<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoConsultorioLogic.php');
$colegiadoConsultorioLogic = new colegiadoConsultorioLogic();

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
} else {
    $accion = 1;
}
switch ($accion) {
    case 1:
        $titulo = 'Nuevo Consultorio';
        $panel = 'panel-success';
        $requerido = 'required=""';
        break;

    case 2:
        $titulo = 'Eliminar Consultorio';
        $panel = 'panel-danger';
        $requerido = '';
        break;

    case 3:
        $titulo = 'Editar Consultorio';
        $panel = 'panel-info';
        $requerido = 'required=""';
        break;

    default:
        $titulo = 'Consultorio';
        break;
}

if (isset($_GET['idColegiado']) || isset($_POST['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    if (isset($_GET['idColegiado'])) {
        $idColegiado = $_GET['idColegiado'];
    } else {
        $idColegiado = $_POST['idColegiado'];
    }
    if (isset($_GET['id'])) {
        $idConsultorio = $_GET['id'];
    } else {
        $idConsultorio = NULL;
    }
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $continua = TRUE;
    
        if (isset($_POST['mensaje'])) {
        ?>
           <div class="ocultarMensaje"> 
               <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
           </div>
         <?php
            $calle = $_POST['calle'];
            $numero = $_POST['numero'];
            $lateral = $_POST['lateral'];
            $piso = $_POST['piso'];
            $depto = $_POST['departamento'];
            $telefono = $_POST['telefono'];
            $idLocalidad = $_POST['idLocalidad'];
            $localidad = $_POST['localidad_buscar'];
            $codigoPostal = $_POST['codigoPostal'];
            $fechaHabilitacion = $_POST['fechaHabilitacion'];
            $ultimaInspeccion = $_POST['ultimaInspeccion'];
            $estado = $_POST['estado'];
            $observacion = $_POST['observacion'];
            $fechaBaja = $_POST['fechaBaja'];
            $resolucion = $_POST['resolucion'];
            $fechaCarga = $_POST['fechaCarga'];
            $idUsuario = $_POST['idUsuario'];
        } else {
            $calle = '';
            $numero = '';
            $lateral = '';
            $piso = '';
            $depto = '';
            $telefono = '';
            $idLocalidad = '';
            $localidad = '';
            $codigoPostal = '';
            $fechaHabilitacion = '';
            $ultimaInspeccion = '';
            $estado = '';
            $observacion = '';
            $fechaBaja = '';
            $resolucion = '';
            $fechaCarga = '';
            $idUsuario = '';
            if (isset($idConsultorio)) {
                $resConsultorio = $colegiadoConsultorioLogic->obtenerConsultorioPorId($idConsultorio);
                if ($resConsultorio['estado']) {
                    $consultorio = $resConsultorio['datos'];
                    $calle = $consultorio['calle'];
                    $numero = $consultorio['numero'];
                    $lateral = $consultorio['lateral'];
                    $piso = $consultorio['piso'];
                    $depto = $consultorio['departamento'];
                    $telefono = $consultorio['telefono'];
                    $idLocalidad = $consultorio['idLocalidad'];
                    $localidad = $consultorio['nombreLocalidad'];
                    $codigoPostal = $consultorio['codigoPostal'];
                    $fechaHabilitacion = $consultorio['fechaHabilitacion'];
                    $ultimaInspeccion = $consultorio['ultimaInspeccion'];
                    $estado = $consultorio['estado'];
                    $observacion = $consultorio['observacion'];
                    $fechaBaja = $consultorio['fechaBaja'];
                    $resolucion = $consultorio['resolucion'];
                }
            }
        }
    ?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Consultorio</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consultorios.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Consultorios del colegiado</button>
                </form>
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

    <?php
    if (isset($_POST['idReceta'])) {
        $idReceta = $_POST['idReceta'];
    } else {
        $idReceta = NULL;
    }
    ?>
        <form id="datosConsultorio" autocomplete="off" name="datosConsultorio" method="POST" onSubmit="" action="datosColegiadoConsultorio/abm_consultorio.php">
        <div class="row">
            <div class="col-md-6">
                <label>Calle *</label>
                <input class="form-control" type="text" id="calle" name="calle" value="<?php echo $calle; ?>" <?php echo $requerido; ?>/>
            </div>
            <div class="col-md-1">
                <label>Nº *</label>
                <input class="form-control" type="text" id="numero" name="numero" value="<?php echo $numero; ?>"  <?php //echo $requerido; ?>/>
            </div>
            <div class="col-md-1">
                <label>Piso</label>
                <input class="form-control" type="text" id="piso" name="piso" value="<?php echo $piso; ?>" />
            </div>
            <div class="col-md-1">
                <label>Dpto</label>
                <input class="form-control" type="text" id="departamento" name="departamento" value="<?php echo $depto; ?>" />
            </div>
            <div class="col-md-3">
                <label>Teléfono</label>
                <input class="form-control" type="text" id="telefono" name="telefono" value="<?php echo $telefono; ?>" />
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Lateral *</label>
                <input class="form-control" type="text" id="lateral" name="lateral" value="<?php echo $lateral; ?>"  <?php //echo $requerido; ?>/>
            </div>
            <div class="col-md-4">
                <label>Localidad *</label>
                <input class="form-control" autocomplete="OFF" type="text" name="localidad_buscar" id="localidad_buscar" placeholder="Ingrese Localidad" value="<?php echo $localidad; ?>"  <?php echo $requerido; ?>/>
                <input type="hidden" name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad; ?>"  <?php echo $requerido; ?> />
            </div>
            <div class="col-md-2">
                <label>C&oacute;digo Postal *</label>
                <input class="form-control" type="text" name="codigoPostal" id="codigoPostal" value="<?php echo $codigoPostal; ?>"  <?php echo $requerido; ?>/>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-3">
                <label>Fecha Habilitación *</label>
                <input type="date" class="form-control" id="fechaHabilitacion" name="fechaHabilitacion" value="<?php echo $fechaHabilitacion;?>"  <?php echo $requerido; ?>>
            </div>
            <div class="col-md-3">
                <label>Última Inspección </label>
                <input type="date" class="form-control" id="ultimaInspeccion" name="ultimaInspeccion" value="<?php echo $ultimaInspeccion;?>">
            </div>
            <div class="col-md-3">
                <label>Fecha de baja </label>
                <input type="date" class="form-control" id="fechaBaja" name="fechaBaja" value="<?php echo $fechaBaja;?>">
            </div>
            <div class="col-md-3">
                <label>Nº de resolución *</label>
                <input class="form-control" type="text" name="resolucion" id="resolucion" value="<?php echo $resolucion; ?>"  <?php echo $requerido; ?> />
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12">
                <label>Observaciones</label>
                <input class="form-control" type="text" name="observacion" id="observacion" value="<?php echo $observacion; ?>" />
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                <input type="hidden" name="idColegiadoConsultorio" id="idColegiadoConsultorio" value="<?php echo $idConsultorio; ?>" />
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
}
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
   
</script>