<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/paisLogic.php');
$paisLogic = new paisLogic();
require_once ('../dataAccess/personaLogic.php');
$personaLogic = new personaLogic();
?>
<script>
      $(document).ready(function()
      {
         $("#claveModal").modal("show");
      });
</script>
<?php
$_SESSION['menuColegiado'] = "Modifica";
$periodoActual = $_SESSION['periodoActual'];
$continua = TRUE;
$idColegiado = NULL;
$persona = NULL;
if (isset($_GET['idColegiado']) || isset($_POST['idColegiado'])) {
    if (isset($_GET['idColegiado'])) {
        $idColegiado = $_GET['idColegiado'];
    } 
    if (isset($_POST['idColegiado'])) {
        $idColegiado = $_POST['idColegiado'];
    } 
    if (isset($idColegiado)) {
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado']) {
            $matricula = $resColegiado['datos']['matricula'];
        } else {
            $matricula = 999999;
        }
        $resPersona = $personaLogic->obtenerPersonaPorIdColegiado($idColegiado);
        if ($resPersona['estado']) {
            $persona = $resPersona['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resPersona['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje = 'MAL INGRESO';
    }
} else {
    $continua = FALSE;
    $mensaje = 'MAL INGRESO. SIN DATOS.';
}

if ($continua) {
    if (isset($_POST['mensaje'])) {
        $idPersona = $_POST['idPersona'];
        $apellido = $_POST['apellido'];
        $nombre = $_POST['nombre'];
        $fechaNacimiento = $_POST['fechaNacimiento'];
        $tipoDocumento = $_POST['tipoDocumento'];
        $idPaises = $_POST['idPaises'];
        $nacionalidad_buscar = $_POST['nacionalidad_buscar'];
        $numeroDocumento = $_POST['numeroDocumento'];
        $sexo = $_POST['sexo'];
        ?>
        <div class="ocultarMensaje">
            <div class="<?php echo $_POST['clase']; ?>" role="alert">
                <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
                <span><?php echo $_POST['mensaje'];?></span>
            </div>
        </div>

    <?php
    } else {
        $idPersona = $persona['idPersona'];
        $apellido = $persona['apellido'];
        $nombre = $persona['nombre'];
        $fechaNacimiento = $persona['fechaNacimiento'];
        $tipoDocumento = $persona['tipoDocumento'];
        $idPaises = $persona['idNacionalidad'];
        $resNacionalidad = $paisLogic->obtenerPaisPorId($idPaises);
        if ($resNacionalidad['estado']) {
            $nacionalidad_buscar = $resNacionalidad['datos']['nombre'];
        } else {
            $nacionalidad_buscar = '';
        }
        $numeroDocumento = $persona['numeroDocumento'];
        $sexo = $persona['sexo'];
    }
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9 text-left">
                <h4>Actualización de datos de la Persona</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <form id="datosAlta" autocomplete="off" name="datosAlta" method="POST" onSubmit="" action="datosPersona/actualiza.php?idColegiado=<?php echo $idColegiado; ?>">
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula</label>
                <input class="form-control" type="text" name="matricula" value="<?php echo $matricula; ?>" readonly=""/>
            </div>
            <div class="col-md-4">
                <label>Apellido *</label>
                <input class="form-control" type="text" name="apellido" value="<?php echo $apellido; ?>" required=""/>
            </div>
            <div class="col-md-4">
                <label>Nombre *</label>
                <input class="form-control" type="text" name="nombre" value="<?php echo $nombre; ?>" required=""/>
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
            <div class="col-md-4">
                <label>Nacionalidad *</label>
                <input class="form-control" autocomplete="OFF" type="text" name="nacionalidad_buscar" id="nacionalidad_buscar" value="<?php echo $nacionalidad_buscar ?>" placeholder="Ingrese nacionalidad a buscar" required=""/>
                <input type="hidden" name="idPaises" id="idPaises" value="<?php echo $idPaises; ?>" required="" />
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <?php
        $usuarioOK = FALSE;
        if (isset($_POST['clave']) && $_POST['clave'] <> "") {
            $resUsuario = $usuarioLogic->validarUsuario($_SESSION['user_entidad']['nombreUsuario'], $_POST['clave']);
            if ($resUsuario['estado'] && $resUsuario['datos']){
                $usuarioOK = TRUE;
                ?>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <br>
                        <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                        <input type="hidden" name="idPersona" id="idPersona" value="<?php echo $idPersona; ?>" />
                    </div>
                </div>
                <?php
            } else {
            ?>
                <div class="row">
                    <div class="col-md-12 text-center alert alert-danger">
                        CLAVE NO CORRESPONDE DEBE VOLVER A INTENTAR
                    </div>
                </div>
            <?php
            }
        }
        ?>
    </form>
        <div class="row">&nbsp;</div>
        <?php
        if (!$usuarioOK) {
            if (isset($_POST['clave'])) {
        ?>
        <div class="row">
            <div class="col-md-12 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="persona_actualizar.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a intentar</button>
                </form>
            </div>
        </div>
        <?php
            }
        }
        ?>
    </div>
</div>

<?php 
    if (!isset($_POST['clave']) || $_POST['clave'] == "") {
    ?>
        <div id="claveModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header alert alert-success">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Validar usuario</h4>
                    </div>              
                    <!-- dialog body -->
                    <div class="modal-body">
                        <form id="clave" autocomplete="off" name="clave" method="POST" action="persona_actualizar.php?idColegiado=<?php echo $idColegiado; ?>">
                            <label>Contraseña *</label>
                            <input type="password" class="form-control" autofocus="" name="clave" id="clave" required=""/>
                            <br>
                            <div style="text-align: center;"><button type="submit"  class="btn btn-success btn-lg" >Confirma</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
} else {
?>
    <div class="<?php echo $resPersona['clase']; ?>" role="alert">
        <span class="<?php echo $resPersona['icono']; ?>" aria-hidden="true"></span>
        <span><strong><?php echo $resPersona['mensaje']; ?></strong></span>
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

      
</script>