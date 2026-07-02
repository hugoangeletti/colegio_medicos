<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/tipoTituloLogic.php');
$tipoTituloLogic = new tipoTituloLogic();
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
$tituloDigital = NULL;
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
            $apellido = $resColegiado['datos']['apellido'];
            $nombre = $resColegiado['datos']['nombre'];
        } else {
            $matricula = 999999;
        }
        
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idColegiadoTitulo = $_GET['id'];
            $resTitulo = $colegiadoLogic->obtenerTitulosPorIdColegiadoTitulo($idColegiadoTitulo);
            if ($resTitulo['estado']) {
                $titulo = $resTitulo['datos'];
            } else {
                $continua = FALSE;
                $mensaje = $resTitulo['mensaje'];
            }
        } else {
            $titulo = NULL;
            //$continua = FALSE;
            //$mensaje = 'MAL INGRESO ColegiadoTitulo';
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
        //$idColegiadoTitulo = $_GET['id'];
        $fechaTitulo = $_POST['fechaTitulo'];
        $idTipoTitulo = $_POST['idTipoTitulo'];
        $idUniversidad = $_POST['idUniversidad'];
        $universidad_buscar = $_POST['universidad'];
        $tituloDigital = $_POST['tituloDigital'];
        ?>
        <div class="ocultarMensaje">
            <div class="<?php echo $_POST['clase']; ?>" role="alert">
                <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
                <span><?php echo $_POST['mensaje'];?></span>
            </div>
        </div>

    <?php
    } else {
        if (isset($titulo)) {
            $idColegiadoTitulo = $titulo['idColegiadoTitulo'];
            $fechaTitulo = $titulo['fechaTitulo'];
            $idTipoTitulo = $titulo['idTipoTitulo'];
            $idUniversidad = $titulo['idUniversidad'];
            $universidad_buscar = $titulo['universidad'];
            $tituloDigital = $titulo['tituloDigital'];
        } else {
            $idColegiadoTitulo = NULL;
            $fechaTitulo = NULL;
            $idTipoTitulo = NULL;
            $idUniversidad = NULL;
            $universidad_buscar = NULL;
            $tituloDigital = NULL;
        }
    }

?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9 text-left">
                <h4>Actualización de datos del Título</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <form id="datosAlta" autocomplete="off" name="datosAlta" method="POST" onSubmit="" action="datosTitulo/actualiza.php?idColegiado=<?php echo $idColegiado; ?>">
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula</label>
                <input class="form-control" type="text" name="matricula" value="<?php echo $matricula; ?>" readonly=""/>
            </div>
            <div class="col-md-4">
                <label>Apellido *</label>
                <input class="form-control" type="text" name="apellido" value="<?php echo $apellido; ?>" readonly=""/>
            </div>
            <div class="col-md-4">
                <label>Nombre *</label>
                <input class="form-control" type="text" name="nombre" value="<?php echo $nombre; ?>" readonly=""/>
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
                <label>Título Digital *</label>
                <select class="form-control" id="tituloDigital" name="tituloDigital" required="">
                    <option value="" selected>Seleccione</option>
                    <option value="1" <?php if(isset($tituloDigital) && $tituloDigital == 1) { ?> selected <?php } ?>>SI</option>
                    <option value="0" <?php if(isset($tituloDigital) && $tituloDigital == 0) { ?> selected <?php } ?>>NO</option>
                </select>            
            </div>
            <div class="col-md-6">
                <label>Otorgado por *</label>
                <input class="form-control" type="text" name="universidad_buscar" id="universidad_buscar" value="<?php echo $universidad_buscar; ?>" placeholder="Ingrese universidad a buscar" required=""/>
                <input type="hidden" name="idUniversidad" id="idUniversidad" value="<?php echo $idUniversidad; ?>" required="" />
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
                        <input type="hidden" name="idColegiadoTitulo" id="idColegiadoTitulo" value="<?php echo $idColegiadoTitulo; ?>" />
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
    </div>
</div>
<?php 
    if (!isset($_POST['clave']) || $_POST['clave'] == "") {
    ?>
        <div id="claveModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header alert alert-success">
                        <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>-->
                        <h4 class="modal-title">Validar usuario</h4>
                    </div>              
                    <!-- dialog body -->
                    <div class="modal-body">
                        <form id="frm_clave" autocomplete="off" name="frm_clave" method="POST" action="titulo_actualizar.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoTitulo; ?>">
                            <label>Contraseña *</label>
                            <input type="password" class="form-control" autofocus="" name="clave" id="clave" required=""/>
                            <br>
                            <div style="text-align: center;"><button type="submit"  class="btn btn-success btn-lg" >Confirma</button></div>
                        </form>
                        <a href="colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>" >Volver</a>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
} else {
?>
<!--    <div class="<?php echo $resTitulo['clase']; ?>" role="alert">
        <span class="<?php echo $resTitulo['icono']; ?>" aria-hidden="true"></span>-->
        <span><strong><?php echo $mensaje; ?></strong></span>
    <!--</div>-->        
<?php
}
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
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