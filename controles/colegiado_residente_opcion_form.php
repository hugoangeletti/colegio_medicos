<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoResidenteLogic.php');
$colegiadoResidenteLogic = new colegiadoResidenteLogic();

$continua = TRUE;
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = $colegiado['apellido'].' '.trim($colegiado['nombre']);

        //si viene por actualizar, busco por idColegiadoResid
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idColegiadoResidente = $_GET['id'];
            $resColegiadoResidente = $colegiadoResidenteLogic->obtenerColegiadoResidentePorId($idColegiadoResidente);
            //var_dump($resColegiadoResidente);
            if ($resColegiadoResidente['estado']) {
                $colegiadoResidente = $resColegiadoResidente['datos'];
                $fechaInicio = $colegiadoResidente['fechaInicio'];
                $fechaFin = $colegiadoResidente['fechaFin'];
                $opcion = $colegiadoResidente['opcion'];
                $anioResidencia = $colegiadoResidente['anio'];
                $idEntidad = $colegiadoResidente['idEntidad'];
                $nombreEntidad = $colegiadoResidente['nombreEntidad'];
                $adjunto = $colegiadoResidente['adjunto'];
                $accion = 3; //moficicar
            } else {
                $continua = FALSE;
            }
        } else {
            $calle = NULL;
            $numero = NULL;
            $piso = NULL;
            $depto = NULL;
            $lateral = NULL;
            $idLocalidad = NULL;
            $localidad_buscar = NULL;
            $codigoPostal = NULL;
            $telefonoFijo = null;
            $telefonoMovil = NULL;
            $mail = NULL;

            $resColegiadoResidente = $colegiadoResidenteLogic->obtenerColegiadoResidentePorIdColegiado($idColegiado);
            //var_dump($resColegiadoResidente);
            if ($resColegiadoResidente['estado']) {
                if (sizeof($resColegiadoResidente['datos']) > 0) {
                    $colegiadoResidente = $resColegiadoResidente['datos'];
                    $idColegiadoResidente = $colegiadoResidente['idColegiadoResidente'];
                    $fechaInicio = $colegiadoResidente['fechaInicio'];
                    $fechaFin = $colegiadoResidente['fechaFin'];
                    $opcion = $colegiadoResidente['opcion'];
                    $anioResidencia = $colegiadoResidente['anio'];
                    $idEntidad = $colegiadoResidente['idEntidad'];
                    $nombreEntidad = $colegiadoResidente['nombreEntidad'];
                    $adjunto = $colegiadoResidente['adjunto'];
                    $accion = 4; //visualizar
                } else {
                    $periodoActual = $_SESSION['periodoActual'];
                    $idColegiadoResidente = NULL;
                    $fechaInicio = date('Y-m-d');
                    $fechaFin = ($periodoActual+1).'-06-30';
                    $opcion = NULL;
                    $fechaBaja = NULL;
                    $anioResidencia = NULL;
                    $idEntidad = NULL;
                    $nombreEntidad = NULL;
                    $adjunto = NULL;
                    $accion = 1; //alta
                }
            } else {
                $continua = FALSE;
            }
        }
    } else {
        $continua = FALSE;
    }
} else {
    $resColegiadoResidente['clase'] = "alert alert-warning";
    $resColegiadoResidente['icono'] = "glyphicon glyphicon-exclamation-sign";
    $resColegiadoResidente['mensaje'] = "Datos mal ingresados";
    $continua = FALSE;
}

if (isset($idColegiadoResidente)) {
    $panel = 'panel-info';
    $claseBoton = 'btn-info';
    $textoBoton = 'Confimar';
    $readOnly = 'readonly';
    $requerido = '';
} else {
    $panel = 'panel-success';
    $textoBoton = 'Confirmar';
    $claseBoton = 'btn-success';
    $readOnly = '';
    $requerido = 'required';
}

?>
<div class="panel <?php echo $panel; ?>">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4> Opción de residente</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_residente_opcion.php?idColegiado=<?php echo $idColegiado; ?>">
                    <button type="submit"  class="btn <?php echo $claseBoton ?>" >Volver</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
    if (isset($_POST['mensaje'])) {
    ?>
       <div class="ocultarMensaje"> 
           <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php
    }
    ?>
        <div class="row">
            <div class="col-md-4">
                <label for="apellidoNombre">Apellido y nombre</label>
                <input class="form-control" type="text" name="apellidoNombre" value="<?php echo $apellidoNombre; ?>" readonly/>
            </div>
            <div class="col-md-1">
                <label for="matricula">Matrícula</label>
                <input class="form-control" type="number" name="matricula" value="<?php echo $matricula; ?>" readonly/>
            </div>
            <div class="col-md-2">
                <label for="fechaInicio">Fecha de solcitud:</label>
                <input class="form-control" type="date" name="fechaInicio" value="<?php echo $fechaInicio; ?>" readonly/>
            </div>
            <div class="col-md-2">
                <label for="fechaFin">Fecha de caducidad:</label>
                <input class="form-control" type="date" name="fechaFin" value="<?php echo $fechaFin; ?>" readonly/>
            </div>
        </div>
        <div class="row">&nbsp;</div>

            <form id="datosResidente" autocomplete="off" name="datosResidente" method="POST" action="datosResidente/abm_residente.php" target="_BLANK">
                <div class="row">
                    <div class="col-md-3">
                        <label>Opta por *</label>
                        <select class="form-control" id="opcion" name="opcion" <?php if ($accion == 1) { echo 'required'; } else { echo 'readonly'; } ?>>
                            <option value="">Selección opción</option>
                            <option value="EXENCION" <?php if($opcion == "EXENCION") { ?> selected <?php } ?>>EXENCION</option>
                            <option value="PAGO_CUOTA" <?php if($opcion == "PAGO_CUOTA") { ?> selected <?php } ?>>PAGO_CUOTA</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Año de residencia *</label>
                        <select class="form-control" id="anio" name="anio" <?php if ($accion == 1) { echo 'required'; } else { echo 'readonly'; } ?>>
                            <option value="">Selección opción</option>
                            <option value="1" <?php if($anioResidencia == "1") { ?> selected <?php } ?>>Residencia 1° nivel - 1er Año</option>
                            <option value="2" <?php if($anioResidencia == "2") { ?> selected <?php } ?>>Residencia 1° nivel - 2d0 Año</option>
                            <option value="3" <?php if($anioResidencia == "3") { ?> selected <?php } ?>>Residencia 1° nivel - 3er Año</option>
                            <option value="4" <?php if($anioResidencia == "4") { ?> selected <?php } ?>>Residencia 1° nivel - 4to Año</option>
                            <option value="5" <?php if($anioResidencia == "5") { ?> selected <?php } ?>>Residencia 1° nivel - Jefatura</option>
                            <option value="6" <?php if($anioResidencia == "6") { ?> selected <?php } ?>>Residencia 2° nivel - 1er Año</option>
                            <option value="7" <?php if($anioResidencia == "7") { ?> selected <?php } ?>>Residencia 2° nivel - 2do Año</option>
                            <option value="8" <?php if($anioResidencia == "8") { ?> selected <?php } ?>>Residencia 2° nivel - 3do Año</option>
                            <option value="9" <?php if($anioResidencia == "9") { ?> selected <?php } ?>>Residencia 2° nivel - Jefatura</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Hospital de residencia *</label>
                        <input class="form-control" type="text" name="nombreEntidad" id="nombreEntidad" value="<?php echo $nombreEntidad; ?>" placeholder="Ingrese hospital a buscar" 
                            <?php if ($accion <> 4) { echo 'required'; } else { echo 'readonly'; } ?>/>
                        <input type="hidden" name="idEntidad" id="idEntidad" value="<?php echo $idEntidad; ?>" required="" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>

                <!--<div class="row">
                    <div class="col-md-12">
                        <label>Adjunta *</label>
                        <textarea class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" name="adjunto" id="adjunto" rows="5" 
                                <?php if ($accion <> 4) { echo 'required'; } else { echo 'readonly'; } ?>><?php echo $adjunto; ?></textarea>
                    </div>
                </div>-->
                <?php 
                if (!isset($idColegiadoResidente) || $idColegiadoResidente == "") {
                    //como es nuevo pido los datos del domicilio                    
                    ?>
                    <hr>

                    <div class="row">
                        <div class="col-md-12"><h4>Domicilio personal y datos de contacto</h4></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Calle *</label>
                            <input class="form-control text-uppercase" type="text" name="calle" value="<?php echo $calle; ?>" <?php echo $readOnly.' '.$requerido ?>/>
                        </div>
                        <div class="col-md-1">
                            <label>Nº *</label>
                            <input class="form-control" type="text" name="numero" value="<?php echo $numero; ?>" <?php echo $readOnly.' '.$requerido ?>/>
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
                            <input class="form-control text-uppercase" type="text" name="lateral" value="<?php echo $lateral; ?>" <?php echo $readOnly.' '.$requerido ?>/>
                        </div>
                        <div class="col-md-3">
                            <label>Localidad *</label>
                            <input class="form-control" type="text" name="localidad_buscar" id="localidad_buscar" value="<?php echo $localidad_buscar; ?>" placeholder="Ingrese universidad a buscar" <?php echo $readOnly.' '.$requerido ?>/>
                            <input type="hidden" name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad; ?>" <?php echo $readOnly.' '.$requerido ?>/>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-2">
                            <label>C&oacute;digo Postal *</label>
                            <input class="form-control" type="text" name="codigoPostal" value="<?php echo $codigoPostal; ?>" <?php echo $readOnly.' '.$requerido ?>/>
                        </div>
                        <div class="col-md-4">
                            <label>Email *</label>
                            <input class="form-control" type="email" name="mail" value="<?php echo $mail; ?>" <?php echo $readOnly.' '.$requerido ?>/>
                        </div>
                        <div class="col-md-2">
                            <label>Tel&eacute;fono fijo *</label>
                            <input class="form-control" type="text" name="telefonoFijo" value="<?php echo $telefonoFijo; ?>" <?php echo $readOnly.' '.$requerido ?>/>
                        </div>
                        <div class="col-md-2">
                            <label>Tel&eacute;fono M&oacute;vil *</label>
                            <input class="form-control" type="tel" name="telefonoMovil" value="<?php echo $telefonoMovil; ?>" <?php echo $readOnly.' '.$requerido ?>/>
                        </div>
                    </div>
                <?php 
                }
                if (!isset($idColegiadoResidente) || $accion == 3) { 
                ?> 
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 text-center" id="confirmar">
                            <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" onclick="show('confirmar', 'cerrar')"><?php echo $textoBoton; ?> </button>
                            <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                            <input type="hidden" name="idColegiadoResidente" id="idColegiadoResidente" value="<?php echo $idColegiadoResidente; ?>" />
                            <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                            <input type="hidden" name="adjunto" id="adjunto" value="CERTIFICADO DE RESIDENCIA" />
                        </div>
                    </div>    
                <?php 
                }
                ?>
            </form>
        
            <div class="row">&nbsp;</div>
            <div class="row" id="cerrar" style="display: <?php if ($accion == 4) { ?> blok <?php } else { ?> none; <?php } ?>">
                <div class="col-md-2 text-center">
                    <a href="colegiado_residente_imprimir.php?id=<?php echo $idColegiadoResidente; ?>" class="btn btn-default btn-lg" target="_BLANK">Imprimir Planilla</a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="colegiado_residente_opcion.php?id=<?php echo $idColegiadoResidente; ?>&idColegiado=<?php echo $idColegiado; ?>" class="btn btn-default btn-lg">Actualizar datos</a>
                </div>
                <div class="col-md-2 text-center">
                    <form id="datosResidente" autocomplete="off" name="datosResidente" method="POST" action="datosResidente/abm_residente.php">
                        <button type="submit"  class="btn btn-default btn-lg" onclick="return confirmar()">Anular opción </button>
                        <input type="hidden" name="accion" id="accion" value="2" />
                        <input type="hidden" name="idColegiadoResidente" id="idColegiadoResidente" value="<?php echo $idColegiadoResidente; ?>" />
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </form>
                </div>
                <div class="col-md-2 text-center">
                    <a href="colegiado_residente_opcion.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-info btn-lg">Salir</a>
                </div>
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
        $('#nombreEntidad').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'entidad.php',
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

    function show(confirmar, cerrar) {
        obj1 = document.getElementById(confirmar);
        obj1.style.display = 'none';
        obj2 = document.getElementById(cerrar);
        obj2.style.display = 'block';
        //obj.style.display = (obj.style.display=='none') ? 'block' : 'none';
    }

    function confirmar()
    {
        if(confirm('¿Estas seguro de elimiar esta opción de residente?'))
            return true;
        else
            return false;
    }

</script>