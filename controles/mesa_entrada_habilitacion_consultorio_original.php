<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/remitenteLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaLogic.php');
require_once ('../dataAccess/zonaLogic.php');
$zonaLogic = new zonaLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
                    "iDisplayLength":7,
                    "order": [[ 0, "desc" ]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                        "bLengthChange": false,
                        "bFilter": false,
//                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3, 4, 5],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Cuotas adeudadas",
                                "sPdfOrientation": "portrait",
                                "sFileName": "ListadoDeCuotasAdeudadas.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
    });

    $(document).ready(function(){
        $('#idZona').on('change', function(){
            if($('#idZona').val() == ""){
                $('#idLocalidad').empty();
                $('<option value = "">Selecciona una Localidad</option>').appendTo('#idLocalidad');
                $('#idLocalidad').attr('disabled', 'disabled');
            }else{
                $('#idLocalidad').removeAttr('disabled', 'disabled');
                $('#idLocalidad').load('localidadPorZona.php?idZona=' + $('#idZona').val());
            }
        }
        );
        $('#idZona').trigger("change");
    });

</script>
<?php
$continua = TRUE;
$mensaje = "";
$readOnly = "";
$requerido = "";
$mesaEntradaLogic = new mesaEntradaLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    if ((isset($_POST['accion']) && $_POST['accion'] == "AGREGADA") || (isset($_GET['editar']))) {
        $accion = "CONTINUA_CARGA";
    } else {
        $accion = "CONSULTAR";
    }
    $readOnly = "readonly";

    $idMesaEntradaConsultorio = $_GET['id'];
    $idMesaEntrada = NULL;
    $resMesaEntradaConsultorio = $mesaEntradaLogic->obtenerMesaEntradaConsultorioPorId($idMesaEntradaConsultorio, $idMesaEntrada);
    if ($resMesaEntradaConsultorio['estado']) {
        $mesaEntradaConsultorio = $resMesaEntradaConsultorio['datos'];
        $idMesaEntrada = $mesaEntradaConsultorio['idMesaEntrada'];
        $calle = $mesaEntradaConsultorio['calle'];
        $lateral = $mesaEntradaConsultorio['lateral'];
        $numeroCasa = $mesaEntradaConsultorio['numeroCasa'];
        $piso = $mesaEntradaConsultorio['piso'];
        $departamento = $mesaEntradaConsultorio['departamento'];
        $consultorio_buscar = $mesaEntradaConsultorio['nombreConsultorio'];
        $nombreLocalidad = $mesaEntradaConsultorio['nombreLocalidad'];
        $especialidad_buscar = $mesaEntradaConsultorio['nombreEspecialidad'];
        $especialidadAlternativa_buscar = $mesaEntradaConsultorio['nombreEspecialidadAlternativa'];

        //busco si tiene mas medicos en el consultorio
        $resConsultorioOtrosMedicos = $mesaEntradaLogic->obtenerMesaEntradaConsultorioOtrosMedicos($idMesaEntradaConsultorio);
        if ($resConsultorioOtrosMedicos['estado']) {
            $consultorioOtrosMedicos = $resConsultorioOtrosMedicos['datos'];
        } else {
            $consultorioOtrosMedicos = array();
        }

        //obtenemos los datos de mesaentrada
        $resMesa = $mesaEntradaLogic->obtenerMesaEntradaPorId($idMesaEntrada);
        if ($resMesa['estado']) {
            $mesaEntrada = $resMesa['datos'];
            if (isset($mesaEntrada['idColegiado']) && $mesaEntrada['idColegiado'] <> "") {
                $idColegiado = $mesaEntrada['idColegiado'];
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                if ($resColegiado['estado']) {
                    $colegiado = $resColegiado['datos'];
                    $matricula = $colegiado['matricula'];
                    $numeroDocumento = $colegiado['numeroDocumento'];
                    
                    $colegiado_buscar = $matricula.' - '.trim($colegiado['apellido']).' '.trim($colegiado['nombre']).' (DNI '.$numeroDocumento.')';
                } else {
                    $continua = FALSE;
                    $mensaje .= $resColegiado['mensaje'];
                }
            } else {
                $continua = FALSE;
                $mensaje .= 'Mal ingresado, falta Colegiado o Remitente';
            }
            $observaciones = $mesaEntrada['observaciones'];
        } else {
            $continua = FALSE;
            $mensaje .= $resMesa['mensaje'];
            $clase = $resMesa['clase'];    
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resMesaEntradaConsultorio['mensaje'];
        $clase = $resMesaEntradaConsultorio['clase'];
    }
} else {
    $esColegiado = "S";
    $idColegiado = NULL;
    $idConsultorio = NULL;
    $idEspecialidad = NULL;
    $idEspecialidadAlternativa = NULL;
    $colegiado_buscar = NULL;
    $consultorio_buscar = NULL;
    $especialidad_buscar = NULL;
    $especialidadAlternativa_buscar = NULL;
    $accion = "AGREGAR";
    $idMesaEntrada = NULL;
    $observaciones = "";
}
$titulo = "Habilitación de Consultorios";

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
    <?php    
    if (isset($_POST['accion']) && $_POST['accion'] <> "AGREGADA") {
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiado - ";
        }
        if (isset($_POST['colegiado_buscar']) && $_POST['colegiado_buscar'] <> "") {
            $colegiado_buscar = $_POST['colegiado_buscar'];
        } else {
            $colegiado_buscar = NULL;
        }
        if (isset($_POST['idConsultorio']) && $_POST['idConsultorio'] <> "") {
            $idConsultorio = $_POST['idConsultorio'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idConsultorio - ";
        }
        if (isset($_POST['consultorio_buscar']) && $_POST['consultorio_buscar'] <> "") {
            $consultorio_buscar = $_POST['consultorio_buscar'];
        } else {
            $consultorio_buscar = NULL;
        }
        if (isset($_POST['idEspecialidad']) && $_POST['idEspecialidad'] <> "") {
            $idEspecialidad = $_POST['idEspecialidad'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idEspecialidad - ";
        }
        if (isset($_POST['especialidad_buscar']) && $_POST['especialidad_buscar'] <> "") {
            $especialidad_buscar = $_POST['especialidad_buscar'];
        } else {
            $especialidad_buscar = NULL;
        }
        if (isset($_POST['idEspecialidadAlternativa']) && $_POST['idEspecialidadAlternativa'] <> "") {
            $idEspecialidadAlternativa = $_POST['idEspecialidadAlternativa'];
        } else {
            $idEspecialidadAlternativa = NULL;
        }
        if (isset($_POST['especialidadAlternativa_buscar']) && $_POST['especialidadAlternativa_buscar'] <> "") {
            $especialidadAlternativa_buscar = $_POST['especialidadAlternativa_buscar'];
        } else {
            $especialidadAlternativa_buscar = NULL;
        }
    }
}   
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formNota" name="formNota" method="POST" onSubmit="" action="datosMesaEntrada\abm_habilitacion_consultorio.php<?php if ($accion == "AGREGAR") { echo '?agregar'; }?>">
                <div class="row">
                    <div class="col-md-6">
                        <label for="colegiado_buscar">Buscar colegiado *</label>
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" <?php echo $readOnly; ?> />
                        <input type="hidden" name="idColegiado" id="idColegiado" />
                    </div>
                    <div class="col-md-6">
                        <label for="consultorio_buscar">Buscar consultorio *</label>
                        <?php 
                        if ($readOnly <> "readonly") { 
                        ?>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#consultorioModal" >Agregar consultorio</button>
                        <?php
                        } 
                        ?>
                        <input class="form-control" autocomplete="OFF" type="text" name="consultorio_buscar" id="consultorio_buscar" placeholder="Ingrese domicilio o nombre del consultorio" value="<?php echo $consultorio_buscar; ?>" required <?php echo $readOnly; ?>/>
                        <input type="hidden" name="idConsultorio" id="idConsultorio" required />
                    </div>
                </div>  
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="especialidad_buscar">Buscar especialidad *</label> 
                        <input class="form-control" autocomplete="OFF" type="text" name="especialidad_buscar" id="especialidad_buscar" placeholder="Ingrese especialidad" value="<?php echo $especialidad_buscar; ?>" <?php echo $readOnly; ?> required/>
                        <input type="hidden" name="idEspecialidad" id="idEspecialidad" required />
                    </div>
                    <div class="col-md-6">
                        <label for="especialidadAlternativa_buscar">Buscar especialidad alternativa</label> 
                        <input class="form-control" autocomplete="OFF" type="text" name="especialidadAlternativa_buscar" id="especialidadAlternativa_buscar" placeholder="Ingrese especialidad" value="<?php echo $especialidadAlternativa_buscar; ?>" <?php echo $readOnly; ?>/>
                        <input type="hidden" name="idEspecialidadAlternativa" id="idEspecialidadAlternativa" />
                    </div>
                </div>  
                <?php 
                if ($accion == "AGREGAR") {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-8 text-center">
                            <button type="submit" class="btn btn-success" >Guardar</button>
                            <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                            <?php 
                            if (isset($idMesaEntradaConsultorio)) {
                            ?>
                                <input type="hidden" name="idMesaEntradaConsultorio" id="idMesaEntradaConsultorio" value="<?php echo $idMesaEntradaConsultorio; ?>">
                            <?php 
                            } 
                            ?>
                        </div>
                    </div>  
                <?php 
                } 
                ?>
            </form>   
            <?php
            //si ya existe mesaentradconsultorio, muestro la opcion de medicos autorizados
            if (isset($idMesaEntradaConsultorio) && $idMesaEntradaConsultorio <> "") {
            ?>
                <div class="row"><hr></div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="col-md-9 text-center">
                            <h4><b>Médicos autorizados</b></h4>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#autorizadoModal">Agregar autorizado</button>
                        </div>
                        <table id="tablaOrdenada" class="display">
                            <thead>
                                <tr>
                                    <th style="display: none;">Id</th>
                                    <th>Matrícula</th>
                                    <th>Apellido y Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($consultorioOtrosMedicos as $otrosMedicos) {
                                    $idMesaEntradaConsultorioAutorizado = $otrosMedicos['idMesaEntradaConsultorioAutorizado'];
                                    $idColegiadoOtro = $otrosMedicos['idColegiado'];
                                    $matriculaOtro = $otrosMedicos['matricula'];
                                    $apellidoNombreOtro = trim($otrosMedicos['apellido']).' '.trim($otrosMedicos['nombre']);
                                    ?>
                                    <tr>
                                        <td style="display: none;"><?php echo $idMesaEntradaConsultorioAutorizado;?></td>
                                        <td><?php echo $matriculaOtro;?></td>
                                        <td><?php echo $apellidoNombreOtro;?></td>
                                        <td><a href="datosMesaEntrada\abm_medico_autorizado.php?borrar&id=<?php echo $idMesaEntradaConsultorioAutorizado.'_'.$idMesaEntradaConsultorio; ?>" class="btn btn-primary btn-sm" onclick="return confirmaAnular()">Borrar</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php
            }
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="<?php echo $clase; ?>" role="alert">
                        <span><strong><?php echo $mensaje; ?></strong></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-1 text-right">
        <a href="mesa_entrada_listado.php" class="btn btn-info">Salir</a>
    </div>
</div>
<?php    
require_once '../html/footer.php';
?>
<div id="autorizadoModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar Médico Autorizado</h4>
      </div>
      <div class="modal-body">
        <div class="row">
            <form id="nuevoAutorizado" autocomplete="off" name="nuevoAutorizado" method="POST" action="datosMesaEntrada\abm_medico_autorizado.php?agregar">
                <div class="col-md-8">
                    <label for="autorizado_buscar">Buscar matricula: *</label>
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="autorizado_buscar" id="autorizado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" required />
                        <input type="hidden" name="idColegiadoAutorizado" id="idColegiadoAutorizado" />
                </div>
                <div class="col-md-4">
                    <br>
                    <button type="submit" class="btn btn-default" >Guardar</button>
                    <input type="hidden" name="idMesaEntradaConsultorio" id="idMesaEntradaConsultorio" value="<?php echo $idMesaEntradaConsultorio; ?>" />
                </div>
            </form>      
        </div>          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>        

<div id="consultorioModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar Consultorio</h4>
      </div>
      <div class="modal-body">
        <div class="row">
            <form id="nuevoConsultorio" autocomplete="off" name="nuevoConsultorio" method="POST" action="datosConsultorio\abm_consultorio.php">
                <div class="col-md-3">
                    <label for="tipoConsultorio">Tipo de Consultorio</label>
                    <select class="form-control" id="tipoConsultorio" name="tipoConsultorio" required="">
                        <option value="">Seleccione Tipo</option>
                        <option value="I">Institución</option>
                        <option value="P">Policonsultorio</option>
                        <option value="U">Único</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="nombreConsultorio">Nombre del Consultorio</label>
                    <input class="form-control" type="text" name="nombreConsultorio" id="nombreConsultorio" placeholder="Ingrese Nombre del Cosnultorio">
                </div>
                <div class="col-md-3">
                    <label for="cantidadConsultorios">Cantidad de Consultorios</label>
                    <input class="form-control" type="number" name="cantidadConsultorios" id="cantidadConsultorios">
                </div>
                <div class="row">&nbsp;</div>
                <div class="col-md-6">
                    <label for="calle">Calle: *</label>
                    <input class="form-control" type="text" name="calle" id="calle" placeholder="Ingrese calle" required />
                </div>
                <div class="col-md-6">
                    <label for="lateral">Laterales: *</label>
                    <input class="form-control" type="text" name="lateral" id="lateral" placeholder="Ej. e/9 y 10" />
                </div>
                <div class="row">&nbsp;</div>
                <div class="col-md-2">
                    <label for="numero">Número: *</label>
                    <input class="form-control" type="text" name="numero" id="numero" required />
                </div>
                <div class="col-md-2">
                    <label for="piso">Piso: </label>
                    <input class="form-control" type="text" name="piso" id="piso" />
                </div>
                <div class="col-md-2">
                    <label for="departamento">Departamento: *</label>
                    <input class="form-control" type="text" name="departamento" id="departamento" />
                </div>
                <div class="col-md-2">
                    <label for="telefono">Teléfono: *</label>
                    <input class="form-control" type="text" name="telefono" id="telefono" />
                </div>
                <div class="row">&nbsp;</div>
                <div class="col-md-5">
                    <label for="idZona">Partido: *</label>
                    <select class="form-control" id="idZona" name="idZona" required>
                        <option value="">Seleccione Partido</option>
                        <?php
                        $resZonas = $zonaLogic->obtenerZonas();
                        if ($resZonas['estado']) {
                            foreach ($resZonas['datos'] as $fila) {
                                $idZona = $fila['id'];
                                $nombreZona = $fila['nombre'];
                                ?>
                                <option value="<?php echo $idZona;?>"><?php echo $nombreZona;?></option>
                            <?php
                            }
                        }    
                        ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="idLocalidad">Localidad: *</label>
                    <select  id="idLocalidad" name="idLocalidad"  class="form-control" disabled="disabled" required="required"></select>
                    <!--<input class="form-control" type="text" name="localidad" id="localidad" />-->
                </div>
                <div class="col-md-2">
                    <label for="codigoPostal">Codigo Postal: *</label>
                    <input class="form-control" type="text" name="codigoPostal" id="codigoPostal" />
                </div>
                <div class="row">&nbsp;</div>
                <div class="col-md-12">
                    <label for="observaciones">Días y horarios: *</label>
                    <input class="form-control" type="text" name="observaciones" id="observaciones" />
                </div>
                <div class="row">&nbsp;</div>
                <div class="col-md-4">
                    <br>
                    <button type="submit" class="btn btn-default" >Guardar</button>
                    <input type="hidden" name="accion" id="accion" value="AGREGAR">
                </div>
            </form>      
        </div>          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>        

<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    //buscar idcolegiado solicitante
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php?activos=SI',
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
                $('#idColegiado').val(nameIdMap[item]);
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

    //buscar idcolegiado autorizado
    $(function(){
        var nameIdMap = {};
        $('#autorizado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php?activos=SI',
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
                $('#idColegiadoAutorizado').val(nameIdMap[item]);
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

    //buscar consultorio
    $(function(){
        var nameIdMap = {};
        $('#consultorio_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'consultorio.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 2,
            //maxItem:15,
            
            updater: function (item) {
                $('#idConsultorio').val(nameIdMap[item]);
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

    //buscar especialidad 
    $(function(){
        var nameIdMap = {};
        $('#especialidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'especialidad.php',
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
                $('#idEspecialidad').val(nameIdMap[item]);
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

    //buscar especialidad alternativa
    $(function(){
        var nameIdMap = {};
        $('#especialidadAlternativa_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'especialidad.php',
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
                $('#idEspecialidadAlternativa').val(nameIdMap[item]);
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

    function confirmaAnular()
    {
        if(confirm('¿Estas seguro de ANULAR este registro?'))
            return true;
        else
            return false;
    }

</script>
