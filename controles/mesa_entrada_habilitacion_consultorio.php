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
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
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
</script>
<?php
$continua = TRUE;
$mensaje = "";
$readOnly = "";
$requerido = "";
$esColegiado = "S";
$idColegiado = NULL;
//$idConsultorio = NULL;
$idEspecialidad = NULL;
$idEspecialidadAlternativa = NULL;
$colegiado_buscar = NULL;
//$consultorio_buscar = NULL;
$especialidad_buscar = NULL;
$especialidadAlternativa_buscar = NULL;
$accion = "AGREGAR";
$idMesaEntrada = NULL;
$observaciones = NULL;
$tipoConsultorio = NULL;
$nombreConsultorio = NULL;
$cantidadConsultorios = 1;
$calle = NULL;
$lateral = NULL;
$numero = NULL;
$piso = NULL;
$departamento = NULL;
$telefono = NULL;
$localidad_buscar = NULL;
$idLocalidad = NULL;
$codigoPostal = NULL;

//verifica por donde para volver con el mismo filtro
if (isset($_GET['ingreso']) && ($_GET['ingreso'] == "FECHA" || $_GET['ingreso'] == "FECHA_TIPO" || $_GET['ingreso'] == "COLEGIADO" || $_GET['ingreso'] == "OTRO")) {
    $accedePor = $_GET['ingreso'];
} else {
    $accedePor = NULL;
}
//fin accedePor

$mesaEntradaLogic = new mesaEntradaLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    if ((isset($_POST['accion']) && $_POST['accion'] == "AGREGADA") || (isset($_GET['editar'])) || (isset($_GET['ver']))) {
        $accion = "CONTINUA_CARGA";
    } else {
        $accion = "CONSULTAR";
    }
    $readOnly = "readonly";
    if (isset($_GET['editar'])) {
        $accion = "EDITAR";
        $readOnly = "";
    }

    $idMesaEntradaConsultorio = $_GET['id'];
    $resMesaEntradaConsultorio = $mesaEntradaLogic->obtenerMesaEntradaConsultorioPorId($idMesaEntradaConsultorio, $idMesaEntrada);
    if ($resMesaEntradaConsultorio['estado']) {
        $mesaEntradaConsultorio = $resMesaEntradaConsultorio['datos'];
        $idMesaEntrada = $mesaEntradaConsultorio['idMesaEntrada'];
        $calle = $mesaEntradaConsultorio['calle'];
        $lateral = $mesaEntradaConsultorio['lateral'];
        $numero = $mesaEntradaConsultorio['numeroCasa'];
        $piso = $mesaEntradaConsultorio['piso'];
        $departamento = $mesaEntradaConsultorio['departamento'];
        $idLocalidad = $mesaEntradaConsultorio['idLocalidad'];
        $codigoPostal = $mesaEntradaConsultorio['codigoPostal'];
        $observaciones = $mesaEntradaConsultorio['observaciones'];
        //$consultorio_buscar = $mesaEntradaConsultorio['nombreConsultorio'];
        //$nombreLocalidad = $mesaEntradaConsultorio['nombreLocalidad'];
        $localidad_buscar = $mesaEntradaConsultorio['nombreLocalidad'];
        $especialidad_buscar = $mesaEntradaConsultorio['nombreEspecialidad'];
        $idEspecialidad = $mesaEntradaConsultorio['idEspecialidad'];
        $especialidadAlternativa_buscar = $mesaEntradaConsultorio['nombreEspecialidadAlternativa'];
        $idEspecialidadAlternativa = $mesaEntradaConsultorio['idEspecialidadAlternativa'];
        $tipoConsultorio = $mesaEntradaConsultorio['tipoConsultorio'];
        $nombreConsultorio = $mesaEntradaConsultorio['nombreConsultorio'];
        $telefono = $mesaEntradaConsultorio['telefono'];
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
                $fechaIngreso = $mesaEntrada['fechaIngreso'];
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
    $idColegiado = $_POST['idColegiado'];
    $colegiado_buscar = $_POST['colegiado_buscar'];
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
        if (isset($_POST['idEspecialidad']) && $_POST['idEspecialidad'] <> "") {
            $idEspecialidad = $_POST['idEspecialidad'];
        }
        if (isset($_POST['especialidad_buscar']) && $_POST['especialidad_buscar'] <> "") {
            $especialidad_buscar = $_POST['especialidad_buscar'];
        }
        if (isset($_POST['idEspecialidadAlternativa']) && $_POST['idEspecialidadAlternativa'] <> "") {
            $idEspecialidadAlternativa = $_POST['idEspecialidadAlternativa'];
        }
        if (isset($_POST['especialidadAlternativa_buscar']) && $_POST['especialidadAlternativa_buscar'] <> "") {
            $especialidadAlternativa_buscar = $_POST['especialidadAlternativa_buscar'];
        }
        if (isset($_POST['tipoConsultorio']) && $_POST['tipoConsultorio'] <> "") {
            $tipoConsultorio = $_POST['tipoConsultorio'];
        }
        if (isset($_POST['nombreConsultorio']) && $_POST['nombreConsultorio'] <> "") {
            $nombreConsultorio = $_POST['nombreConsultorio'];
        } else {
            $nombreConsultorio = NULL;
        }
        if (isset($_POST['cantidadConsultorios']) && $_POST['cantidadConsultorios'] <> "") {
            $cantidadConsultorios = $_POST['cantidadConsultorios'];
        } else {
            $cantidadConsultorios = 1;
        }
        if (isset($_POST['calle']) && $_POST['calle'] <> "") {
            $calle = $_POST['calle'];
        }
        if (isset($_POST['lateral']) && $_POST['lateral'] <> "") {
            $lateral = $_POST['lateral'];
        }
        if (isset($_POST['piso']) && $_POST['piso'] <> "") {
            $piso = $_POST['piso'];
        }
        if (isset($_POST['departamento']) && $_POST['departamento'] <> "") {
            $departamento = $_POST['departamento'];
        }
        if (isset($_POST['telefono']) && $_POST['telefono'] <> "") {
            $telefono = $_POST['telefono'];
        }
        if (isset($_POST['localidad_buscar']) && $_POST['localidad_buscar'] <> "") {
            $localidad_buscar = $_POST['localidad_buscar'];
        }
        if (isset($_POST['idLocalidad']) && $_POST['idLocalidad'] <> "") {
            $idLocalidad = $_POST['idLocalidad'];
        }
        if (isset($_POST['codigoPostal']) && $_POST['codigoPostal'] <> "") {
            $codigoPostal = $_POST['codigoPostal'];
        }
        if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
            $observaciones = $_POST['observaciones'];
        }
    }
}   

if (isset($_GET['ingreso']) && ($_GET['ingreso'] == "FECHA" || $_GET['ingreso'] == "FECHA_TIPO" || $_GET['ingreso'] == "COLEGIADO" || $_GET['ingreso'] == "OTRO")) {
    $accedePor = $_GET['ingreso'];
} else {
    $accedePor = NULL;
}

if (isset($idColegiado) && $idColegiado <> "") {
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $colegiado_buscar = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        $idEstadoMatricular = $colegiado['idEstadoMatricular'];
        $estadoMatricular = $colegiado['estado'];
        $tipoEstadoMatricular = $colegiado['tipoEstado'];
        $movimientoCompleto = $colegiado['movimientoCompleto'];
        if ($tipoEstadoMatricular == 'A' || $tipoEstadoMatricular == 'I'){
            $estiloColegiado = ' style="color: green; font-size: large;"';
        } else {
            $estiloColegiado = ' style="color: red;"';
        }        
    } else {
        $continua = FALSE;
        $mensaje .= $resColegiado['mensaje'];
    }

    //obtengo el estado actual con tesoreria, solo si no es ni fallecido ni jubilado
    $aJubFal = array('J', 'F');
    if (!in_array($colegiado['tipoEstado'], $aJubFal)){
        $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, PERIODO_ACTUAL);
        if ($resEstadoTeso['estado']) {
            $codigoDeudor = $resEstadoTeso['codigoDeudor'];
            $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudor);
            if ($resEstadoTesoreria['estado']) {
                $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
            } else {
                $estadoTesoreria = $resEstadoTesoreria['mensaje'];
            }
        } else {
            $estadoTesoreria = $resEstadoTeso['mensaje'];
        }

        if ($codigoDeudor == 0) {
            $estiloTesoreria = ' style="color: green; font-size: large;"';
        } else {
            $estiloTesoreria = ' style="color: red;"';
        }
        $mostrarTesoreria = TRUE;

        //verificamos si esta al dia para poder realizar el pedido, solo si tiene permiso le genera el pedido en caso de ser deudor
        if ($codigoDeudor == 0 || $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 103)) {
            $generaMovimiento = TRUE;
        } else {
            $generaMovimiento = FALSE;
            $mensaje .= "No está en condiciones de solicitar la habilitación de consultorio!";
        }
    } else {
        $mostrarTesoreria = FALSE;
        $generaMovimiento = TRUE;
    }
    //fin tesoreria
}

?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-2 text-right">
            </div>
            <div class="col-md-1 text-right">
                <?php 
                include('mesa_entrada_volver_listado.php');
                ?>
            </div>            
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
                <form id="formNota" name="formNota" method="POST" onSubmit="" action="datosMesaEntrada\abm_habilitacion_consultorio.php<?php if ($accion == "AGREGAR") { echo '?agregar'; } else { if ($accion == "EDITAR") { echo '?editar'; }}?>">
                    <div class="row">
                        <div class="col-md-1">
                            <label for="colegiado_buscar">Matrícula: </label>
                            <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly />
                        </div>
                        <div class="col-md-4">
                            <label for="colegiado_buscar">Colegiado: </label>
                            <input class="form-control" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" readonly/>
                            <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        </div>
                        <div class="col-md-4">
                            <label for="elEstado">Estado Matricular: </label>
                            <input class="form-control" name="elEstado" id="elEstado" type="text" <?php echo $estiloColegiado; ?> 
                                value="<?php 
                                    //se agrega esta condicion el 7/3/2024 a pedido de secretaria
                                    if ($colegiado['idEstadoMatricular'] <> 9) {
                                        $elEstado = trim($colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']));
                                        if (isset($elEstado) && $elEstado <> "") {
                                            $elEstado .= ' - ';
                                        }
                                    } else {
                                        $elEstado = "";
                                    }
                                    echo $elEstado.$movimientoCompleto; ?>" 
                                readonly=""/>
                            <input type="hidden" name="estadoMatricular" id="estadoMatricular" value="<?php echo $estadoMatricular; ?>" />
                        </div>
                        <?php 
                        if ($mostrarTesoreria) {
                        ?>
                            <div class="col-md-3">
                                <label for="estadoTesoreria">Estado con Tesorería: </label>
                                <input class="form-control" type="text" <?php echo $estiloTesoreria; ?> name="estadoTesoreria" id="estadoTesoreria" value="<?php echo $estadoTesoreria  ?>" readonly=""/>
                                <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="<?php echo $codigoDeudor; ?>" />
                            </div>
                        <?php 
                        }
                        ?>                    
                    </div>
                    <?php 
                    if ($generaMovimiento) {
                    ?>
                        <div class="row"><hr></div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="especialidad_buscar">Especialidad *</label> 
                                <input class="form-control" autocomplete="OFF" type="text" name="especialidad_buscar" id="especialidad_buscar" placeholder="Ingrese especialidad" value="<?php echo $especialidad_buscar; ?>" <?php echo $readOnly; ?> required/>
                                <input type="hidden" name="idEspecialidad" id="idEspecialidad" value="<?php echo $idEspecialidad; ?>" required />
                            </div>
                            <div class="col-md-4">
                                <label for="especialidadAlternativa_buscar">Especialidad alternativa</label> 
                                <input class="form-control" autocomplete="OFF" type="text" name="especialidadAlternativa_buscar" id="especialidadAlternativa_buscar" placeholder="Ingrese especialidad" value="<?php echo $especialidadAlternativa_buscar; ?>" <?php echo $readOnly; ?>/>
                                <input type="hidden" name="idEspecialidadAlternativa" id="idEspecialidadAlternativa" value="<?php echo $idEspecialidadAlternativa; ?>" />
                            </div>
                        </div>  
                        <div class="row">&nbsp;</div>
                        <div class="row text-center"><h4>Datos del consultorio</h4></div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="tipoConsultorio">Tipo de Consultorio</label>
                                <select class="form-control" id="tipoConsultorio" name="tipoConsultorio" required="" <?php echo $readOnly; ?>>
                                    <option value="">Seleccione Tipo</option>
                                    <option value="I" <?php if ($tipoConsultorio == "I") { echo 'selected'; } ?>>Institución</option>
                                    <option value="P" <?php if ($tipoConsultorio == "P") { echo 'selected'; } ?>>Policonsultorio</option>
                                    <option value="U" <?php if ($tipoConsultorio == "U") { echo 'selected'; } ?>>Único</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="nombreConsultorio">Nombre del Consultorio</label>
                                <input class="form-control" type="text" name="nombreConsultorio" id="nombreConsultorio" placeholder="Ingrese Nombre del Cosnultorio" value="<?php echo $nombreConsultorio; ?>" <?php echo $readOnly; ?>>
                            </div>
                            <div class="col-md-3">
                                <label for="cantidadConsultorios">Cantidad de Consultorios</label>
                                <input class="form-control" type="number" name="cantidadConsultorios" id="cantidadConsultorios" value="<?php echo $cantidadConsultorios; ?>" <?php echo $readOnly; ?>>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="calle">Calle: *</label>
                                <input class="form-control" type="text" name="calle" id="calle" placeholder="Ingrese calle" value="<?php echo $calle; ?>" required <?php echo $readOnly; ?>/>
                            </div>
                            <div class="col-md-3">
                                <label for="lateral">Laterales: *</label>
                                <input class="form-control" type="text" name="lateral" id="lateral" placeholder="Ej. e/9 y 10" value="<?php echo $lateral; ?>" <?php echo $readOnly; ?>/>
                            </div>
                            <div class="col-md-1">
                                <label for="numero">Número: *</label>
                                <input class="form-control" type="text" name="numero" id="numero" value="<?php echo $numero; ?>" required <?php echo $readOnly; ?>/>
                            </div>
                            <div class="col-md-1">
                                <label for="piso">Piso: </label>
                                <input class="form-control" type="text" name="piso" id="piso" value="<?php echo $piso; ?>" <?php echo $readOnly; ?>/>
                            </div>
                            <div class="col-md-1">
                                <label for="departamento">Dpto: </label>
                                <input class="form-control" type="text" name="departamento" id="departamento" value="<?php echo $departamento; ?>" <?php echo $readOnly; ?>/>
                            </div>
                            <div class="col-md-2">
                                <label for="telefono">Teléfono: *</label>
                                <input class="form-control" type="text" name="telefono" id="telefono" value="<?php echo $telefono; ?>" required <?php echo $readOnly; ?>/>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="idLocalidad">Localidad: *</label>
                                <input class="form-control" autocomplete="OFF" type="text" name="localidad_buscar" id="localidad_buscar" placeholder="Ingrese Localidad" value="<?php echo $localidad_buscar; ?>"  <?php echo $requerido; ?> <?php echo $readOnly; ?>/>
                                <input type="hidden" name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad; ?>"  <?php echo $requerido; ?> />
                            </div>
                            <div class="col-md-2">
                                <label for="codigoPostal">Codigo Postal: *</label>
                                <input class="form-control" type="text" name="codigoPostal" id="codigoPostal" value="<?php echo $codigoPostal; ?>" <?php echo $readOnly; ?>/>
                            </div>
                            <div class="col-md-7">
                                <label for="observaciones">Días y horarios: *</label>
                                <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="2" <?php echo $readOnly; ?>><?php echo $observaciones; ?></textarea>
                            </div>
                        </div>
                        <?php 
                        if ($accion == "AGREGAR" || $accion == "EDITAR") {
                        ?>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-12 text-center">
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
                    } else {
                        //tiene deuda por lo tanto no puede solicitar habilitacion de consultorio
                        ?>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <h4 class="alert alert-warning"><?php echo $mensaje; ?></h4>
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
                            <div class="col-md-6 text-center">
                                <h4><b>Médicos autorizados</b></h4>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#autorizadoModal">Agregar autorizado</button>
                            </div>
                            <div class="col-md-3">
                                <a href="mesa_entrada_imprimir.php?id=<?php echo $idMesaEntrada; ?>" class="btn btn-primary">Imprimir</a> 
                            </div>
                            <?php 
                            if (sizeof($consultorioOtrosMedicos) > 0) {
                            ?>
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
                            <?php 
                            } else {
                            ?>
                                <div class="col-md-9 text-center">
                                    <h4><b>NO REGISTRA</b></h4>
                                </div>
                            <?php
                            }
                            ?>
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
        <form method="POST" action="mesa_entrada_listado.php">
            <button type="submit"  class="btn btn-info " >Volver</button>
            <?php 
            switch ($accedePor) {
                case 'FECHA':
                    ?>
                    <input type="hidden" name="fechaIngreso" id="fechaIngreso" value="<?php echo $fechaIngreso ?>">
                    <?php
                    break;
                
                case 'FECHA_TIPO':
                    ?>
                    <input type="hidden" name="fechaIngreso" id="fechaIngreso" value="<?php echo $fechaIngreso ?>">
                    <input type="hidden" name="idTipoMesaEntradaSeleccionada" id="idTipoMesaEntradaSeleccionada" value="<?php echo $idTipoMesaEntrada ?>">
                    <?php
                    break;
                
                case 'COLEGIADO':
                    ?>
                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado ?>">
                    <?php
                    break;
                
                case 'OTRO':
                    ?>
                    <input type="hidden" name="idRemitente" id="idRemitente" value="<?php echo $idRemitente ?>">
                    <?php
                    break;
                
                default:
                    // code...
                    break;
            }
            ?>
        </form>
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

    function confirmaAnular()
    {
        if(confirm('¿Estas seguro de ANULAR este registro?'))
            return true;
        else
            return false;
    }

</script>
