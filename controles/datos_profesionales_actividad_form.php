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
        $titulo = 'Nueva Actividad Asistencial';
        $requerido = 'required=""';
        if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
            $idColegiado = $_GET['idColegiado'];
            $idActividadAsistencial = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiado - ";
        }    
        break;

    case 2:
        $titulo = 'Eliminar Actividad Asistencial';
        $requerido = '';
        break;

    case 3:
        $titulo = 'Editar Actividad Asistencial';
        $requerido = 'required=""';
        if (isset($_GET['id'])) {
            $idActividadAsistencial = $_GET['id'];
        } else {
            if (isset($_POST['idActividadAsistencial'])) {
                $idActividadAsistencial = $_POST['idActividadAsistencial'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta idActividadAsistencial - ";
            }
        }
        if (isset($idActividadAsistencial)) {
            $resActAsistencial = $colegiadoRematriculacionLogic->obtenerActividadAsistencialPorId($idActividadAsistencial);
            if ($resActAsistencial['estado']) {
                $actividadAsistencial = $resActAsistencial['datos'];
                $idColegiado = $actividadAsistencial['idColegiado'];
                $idEntidad = $actividadAsistencial['idEntidad'];
                $tipoInstitucion = $actividadAsistencial['tipoInstitucion'];
                $tipoInstitucionDetalle = $actividadAsistencial['tipoInstitucionDetalle'];
                $cargo = $actividadAsistencial['cargo'];
                $servicio = $actividadAsistencial['servicio'];
                $fechaDesdeHasta = $actividadAsistencial['fechaDesdeHasta'];
                $nombreInstitucion = $actividadAsistencial['nombreInstitucion'];
                $tipoEntidad = $actividadAsistencial['tipoEntidad'];
                $nombreEntidad = $actividadAsistencial['nombreEntidad'];
            } else {
                $continua = FALSE;
                $mensaje .= $resActAsistencial['mensaje']." - ";
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idActividadAsistencial - ";
        }
        break;

    default:
        $titulo = 'Actividad Asistencial';
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
            if (isset($_POST['idActividadAsistencial']) && $_POST['idActividadAsistencial'] <> "") {
                $idActividadAsistencial = $_POST['idActividadAsistencial'];
            }
            $idColegiado = $_POST['idColegiado'];
            $idEntidad = $_POST['idEntidad'];
            $tipoInstitucion = $_POST['tipoInstitucion'];
            $tipoInstitucionDetalle = $_POST['tipoInstitucionDetalle'];
            $cargo = $_POST['cargo'];
            $servicio = $_POST['servicio'];
            $fechaDesdeHasta = $_POST['fechaDesdeHasta'];
            $nombreInstitucion = $_POST['nombreInstitucion'];
            $tipoEntidad = $_POST['tipoEntidad'];
            $nombreEntidad = $_POST['nombreEntidad'];
        } else {
            if (!isset($idActividadAsistencial)) {
                $tipoInstitucion = NULL;
                $tipoInstitucionDetalle = '';
                $cargo = '';
                $servicio = '';
                $fechaDesdeHasta = '';
                $nombreInstitucion = '';
                $tipoEntidad = '';
                $idEntidad = NULL;
                $nombreEntidad = '';
            }
        }
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Actividad Asistencial</h4>
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

        <form id="datosActividad" autocomplete="off" name="datosActividad" method="POST" onSubmit="" action="datosRematriculacion/abm_actividad_asistencial.php">
            <div class="row">
                <div class="col-md-2">
                    <label>Tipo Entidad *</label>  
                    <select class="form-control" id="tipoInstitucion" name="tipoInstitucion" required>
                        <option value="">Seleccione Tipo</option>
                        <option value="1" <?php if ($tipoInstitucion == '1') { echo 'selected'; } ?>>Pública</option>
                        <option value="2" <?php if ($tipoInstitucion == '2') { echo 'selected'; } ?>>Privada</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Entidad *</label>
                    <input class="form-control" autocomplete="OFF" type="text" name="nombreEntidad" id="nombreEntidad" placeholder="Ingrese Entidad" value="<?php echo $nombreEntidad; ?>" required/>
                    <input type="hidden" name="idEntidad" id="idEntidad" value="<?php echo $idEntidad; ?>" required />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-3">
                    <label>Cargo *</label>
                    <input class="form-control" type="text" id="cargo" name="cargo" value="<?php echo $cargo; ?>" required/>
                </div>
                <div class="col-md-3">
                    <label>Servicio *</label>
                    <input class="form-control" type="text" id="servicio" name="servicio" value="<?php echo $servicio; ?>" required />
                </div>
                <div class="col-md-2">
                    <label>Fecha desde/hasta *</label>
                    <input class="form-control" type="text" id="fechaDesdeHasta" name="fechaDesdeHasta" value="<?php echo $fechaDesdeHasta; ?>" required />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-3">
                    <label>Nombre Institución </label>
                    <input class="form-control" type="text" id="nombreInstitucion" name="nombreInstitucion" value="<?php echo $nombreInstitucion; ?>" readonly />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    <?php if (isset($idActividadAsistencial) && $idActividadAsistencial <> "") { ?>
                            <input type="hidden" name="idActividadAsistencial" id="idActividadAsistencial" value="<?php echo $idActividadAsistencial; ?>" />
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