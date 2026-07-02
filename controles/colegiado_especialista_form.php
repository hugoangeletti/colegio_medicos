<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/especialidades_pdo.php');
?>
<script>
function confirmar()
{
    if(confirm('¿Estas seguro de elimiar el tipo de especialista?'))
        return true;
    else
        return false;
}
</script>
<?php
$continua = TRUE;
$mensaje = "";
$objEspecialidades = new especialidades_pdo();

if (isset($_GET['accion']) && $_GET['accion'] <> "") {
    $accion = $_GET['accion'];
    if ($accion == 1) {
        if (isset($_GET['id']) && $_GET['id'] <> ""){
            $idColegiado = $_GET['id'];
            $idColegiadoEspecialista = NULL;
        } else {
            $mensaje .= "Falta idColegiado";
            $continua = FALSE;
        }        
    } else {
        if (isset($_GET['id']) && $_GET['id'] <> ""){
            $idColegiadoEspecialista = $_GET['id'];
        } else {
            $mensaje .= "Ingreso incorrecto";
            $continua = FALSE;
        }        
    }
} else {
    $mensaje .= 'Falta accion - ';
    $continua = FALSE;
}
?>
<div class="container-fluid">
    <div class="panel panel-default">
    <div class="panel-heading"><h4><b>Editar datos del especialista</b></h4></div>  
    <?php      
    if ($continua){
    ?>
        <?php
        if (isset($_POST['mensaje'])) {
        ?>
            <div class="ocultarMensaje"> 
                <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
            </div>
            <?php    
            if (isset($_POST['incisoArticulo8']) && $_POST['incisoArticulo8'] <> "") {
                $incisoArticulo8 = $_POST['incisoArticulo8'];
            } else {
                $incisoArticulo8 = NULL;
            }
        } else {
            if ($accion == 1) {
                $fechaEspecialista = "";
                $fechaRecertificacion = "";
                $distritoOtorgante = "";
                $fechaVencimiento = "";
                $origen = "";
                $nombreEspecialidad = "";
                $idEspecialidad = "";
                $incisoArticulo8 = "";
                $idTipoEspecialista = "";
                $readOnly = "";
            } else {
                if (isset($idColegiadoEspecialista) && $idColegiadoEspecialista <> "") {
                    $resEspecialista = $colegiadoEspecialistaLogic->obtenerColegiadoEspecialistaPorId($idColegiadoEspecialista);
                    if ($resEspecialista['estado']){
                        $datos = $resEspecialista['datos'];
                        $fechaEspecialista = $datos['fechaEspecialista'];
                        $fechaRecertificacion = $datos['fechaRecertificacion'];
                        $distritoOtorgante = $datos['distritoOrigen'];
                        $fechaVencimiento = $datos['fechaVencimiento'];
                        $origen = $datos['tipoespecialista'];
                        $nombreEspecialidad = $datos['nombreEspecialidad'];
                        $idResolucionDetalle = $datos['idResolucionDetalle'];
                        $incisoArticulo8 = $datos['incisoArticulo8'];
                        $idTipoEspecialista = $datos['idTipoEspecialista'];
                        $idColegiado = $datos['idColegiado'];
                        $readOnly = "readonly";
                    } else {
                        //error al buscar expediente
                        $mensaje .= $resEspecialista['mensaje'];
                        $continua = FALSE;
                    }
                }
            }
            //busco los datos del colegiado
            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['datos']) {
                $colegiado = $resColegiado['datos'];
                $matricula = $colegiado['matricula'];
                $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
            } else { 
                $mensaje .= $resColegiado['mensaje'];
                $continua = FALSE;
            }        
        }
        ?>  
        <div class="panel-body"> 
            <form id="formEColegiadoEspecialista" name="formEColegiadoEspecialista" method="POST" onSubmit="" action="datosColegiadoEspecialista\abm_colegiadoEspecialista.php">
                <div class="row">
                    <div class="col-md-6">
                        <b>Apellido y Nombre</b>  
                        <input type="text" class="form-control" id="apellidoNombre" name="apellidoNombre" value="<?php echo $apellidoNombre; ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <b>Matrícula</b>  
                        <input type="text" class="form-control" id="matricula" name="matricula" value="<?php echo $matricula; ?>" readonly>
                    </div>
                </div>

                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-5">
                        <b>Especialidad</b>  
                        <?php 
                        if ($accion == 1) {
                            //$resEspecialidades = obtenerEspecialidadesParaExpedientes($idColegiado);
                            $resEspecialidades = $objEspecialidades->obtenerEspecialidades();
                            if ($resEspecialidades['estado']) {
                            ?>
                                <select class="form-control" id="idEspecialidad" name="idEspecialidad" >
                                    <option value="">Seleccione Especialidad</option>
                                <?php
                                foreach ($resEspecialidades['datos'] as $row) {
                                    $nombreEspecialidad = $row['nombreEspecialidad'];
                                    if (isset($row['especialidadPadre']) && $row['especialidadPadre'] <> "") {
                                        $nombreEspecialidad .= ' (<b>'.$row['especialidadPadre'].'</b>)';
                                    }
                                ?>
                                    <option value="<?php echo $row['idEspecialidad'] ?>" <?php if ($idEspecialidad == $row['idEspecialidad']) { ?> selected="" <?php } ?>><?php echo $nombreEspecialidad; ?></option>
                                <?php
                                }
                                ?>
                                </select>
                            <?php
                            } else {
                                echo "NO HAY ESPECIALIDADES";
                            }
                        } else {
                        ?>
                            <input type="text" class="form-control" id="especialidad" name="especialidad" value="<?php echo $nombreEspecialidad; ?>" readonly>
                        <?php 
                        }
                        ?>
                    </div>
                    <div class="col-md-1">
                        <b>Otorgado por*</b>  
                        <input type="number" class="form-control" id="distritoOtorgante" name="distritoOtorgante" value="<?php echo $distritoOtorgante; ?>" min="2" max="10" <?php echo $readOnly; ?>>
                    </div>
                    <div class="col-md-2">
                        <b>Fecha Especialista</b>  
                        <input type="date" class="form-control" id="fechaEspecialista" name="fechaEspecialista" value="<?php echo $fechaEspecialista; ?>" <?php echo $readOnly; ?>>
                    </div>
                    <div class="col-md-2">
                        <b>Fecha Recertificación</b>  
                        <input type="date" class="form-control" id="fechaRecertificacion" name="fechaRecertificacion" value="<?php echo $fechaRecertificacion; ?>" <?php //echo $readOnly; ?>>
                    </div>
                    <div class="col-md-2">
                        <b>Fecha Vencimiento</b>  
                        <input type="date" class="form-control" id="fechaVencimiento" name="fechaVencimiento" value="<?php echo $fechaVencimiento; ?>" <?php //echo $readOnly; ?>>
                    </div>
                </div>

                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6">
                        <b>Origen</b>  
                        <?php 
                        if ($accion == 1) {
                        ?>
                            <select class="form-control" id="idTipoEspecialista" name="idTipoEspecialista" required="">
                                <option value="">Seleccione el Tipo de Especialista</option>
                                <?php
                                $resTipoEspecialista = $resolucionesLogic->obtenerTiposEspecialista();
                                if ($resTipoEspecialista['estado']) {
                                    $noMostrar[0] = "J";
                                    $noMostrar[1] = "C";
                                    $noMostrar[2] = "R";
                                    foreach ($resTipoEspecialista['datos'] as $row) {
                                        if (!in_array($row['codigo'], $noMostrar)) {
                                        ?>
                                            <option value="<?php echo $row['id'] ?>" <?php if ($idTipoEspecialista == $row['id']) { ?> selected="" <?php } ?>><?php echo $row['nombre'] ?></option>
                                        <?php                                                
                                        }
                                    }
                                } else {
                                    echo $resTipoEspecialista['mensaje'];
                                }
                                ?>
                            </select>
                        <?php 
                    } else {
                        $resTipoEspecialista = $resolucionesLogic->obtenerTiposEspecialista();
                        if ($resTipoEspecialista['estado']) {
                        ?>
                            <select class="form-control" id="idTipoEspecialista" name="idTipoEspecialista" >
                                <option value="">Seleccione origen</option>
                                <?php 
                                foreach ($resTipoEspecialista['datos'] as $tipoEspecialista) {
                                    $idTipoEspecialistaSelect = $tipoEspecialista['id'];
                                    $tipoEspecialistaNombre = $tipoEspecialista['nombre'];
                                ?>
                                    <option value="<?php echo $idTipoEspecialistaSelect ?>" <?php if ($idTipoEspecialista == $idTipoEspecialistaSelect) { echo 'selected'; } ?> ><?php echo $tipoEspecialistaNombre; ?> </option>
                                <?php 
                                }
                                ?>
                            </select>                                            
                        <?php 
                        } else {
                            echo $resTipoEspecialista['mensaje'];
                        }
                    }
                    ?>
                    </div>
                    <div class="col-md-6">
                        <b>Inciso - Articulo 8 por Excepción</b>&nbsp;&nbsp;&nbsp;
                        <select class="form-control" id="inciso" name="inciso" >
                            <option value="">Seleccione el Inciso correspondiente</option>
                            <option value="a" <?php if ($incisoArticulo8 == "a") { ?> selected="" <?php } ?>>Inciso a - </option>
                            <option value="b" <?php if ($incisoArticulo8 == "b") { ?> selected="" <?php } ?>>Inciso b - Universitario</option>
                            <option value="c" <?php if ($incisoArticulo8 == "c") { ?> selected="" <?php } ?>>Inciso c - CONFEMECO</option>
                            <option value="d" <?php if ($incisoArticulo8 == "d") { ?> selected="" <?php } ?>>Inciso d - Por puntaje</option>
                            <option value="e" <?php if ($incisoArticulo8 == "e") { ?> selected="" <?php } ?>>Inciso e - Curso Superior</option>
                            <option value="f" <?php if ($incisoArticulo8 == "f") { ?> selected="" <?php } ?>>Inciso f - Residencia</option>
                        </select>                                            
                    </div>
                </div>

                <div class="row">&nbsp;</div>
                <div class="row">
                    <div style="text-align:center">
                        <button type="submit"  class="btn btn-success " >Confirma datos</button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                        <?php 
                        if (isset($idColegiadoEspecialista) && $idColegiadoEspecialista <> "") {
                        ?>
                            <input type="hidden" name="idColegiadoEspecialista" id="idColegiadoEspecialista" value="<?php echo $idColegiadoEspecialista; ?>">
                        <?php 
                        }
                        ?>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>">
                    </div>
                </div>  
            </form>   
            <?php 
            if ($accion <> 1) {
            ?>
                <div class="row"><hr style="border-color: lightblue;"></div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <h4><b>Jerarquizado</b></h4>
                            </div>
                            <?php 
                            $resJerarquizado = $colegiadoEspecialistaLogic->obtenerEspecialistaTipoPorIdColegiadoEspecialista($idColegiadoEspecialista, JERARQUIZADO);
                            if ($resJerarquizado['estado']) {
                                $jerarquizado = $resJerarquizado['datos'];
                                $idColegiadoEspecialistaTipo = $jerarquizado['idColegiadoEspecialistaTipo'];
                                $fechaJerarquizado = $jerarquizado['fecha'];
                                $distritoOtorgante = $jerarquizado['distritoOtorgante'];
                                if (is_numeric($distritoOtorgante)) {
                                    $otorgante = 'Distrito '.obtenerNumeroRomano($distritoOtorgante);
                                } else {
                                    $otorgante = 'Distrito '.$distritoOtorgante;
                                }
                                $numeroResolucion = $jerarquizado['numeroResolucion'];
                                ?>
                                <div class="col-md-3">
                                    <b>Fecha:</b>  
                                    <input type="text" class="form-control" id="fechaEspecialista" name="fechaEspecialista" value="<?php echo cambiarFechaFormatoParaMostrar($fechaJerarquizado); ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <b>Otorgado por:</b>  
                                    <input type="text" class="form-control" id="distritoOtorgante" name="distritoOtorgante" value="<?php echo $otorgante; ?>" readonly>
                                </div>
                                <?php 
                                if (isset($numeroResolucion) && $numeroResolucion <> "") {
                                ?>
                                    <div class="col-md-3">
                                        <b>Número de resolución:</b>  
                                        <input type="text" class="form-control" id="numeroResolucion" name="numeroResolucion" value="<?php echo $numeroResolucion; ?>" readonly>
                                    </div>
                                <?php 
                                }
                                ?>
                                <div class="col-md-2">
                                    <br>
                                    <a href="colegiado_especialista_tipo_form.php?id=<?php echo $idColegiadoEspecialista; ?>&tipo=<?php echo JERARQUIZADO; ?>&accion=3">Modificar</a>
                                </div>
                                <div class="col-md-2">
                                    <br>
                                    <a href="datosColegiadoEspecialista\abm_especialista_tipo.php?id=<?php echo $idColegiadoEspecialistaTipo; ?>&idColegiado=<?php echo $idColegiado; ?>&accion=2" onclick="return confirmar()">Borrar</a>
                                </div>
                            <?php
                            } else {
                            ?>  
                                <div class="row">&nbsp;</div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="<?php echo $resJerarquizado['clase']; ?>" role="alert">
                                            <span class="<?php echo $resJerarquizado['icono']; ?>" ></span>
                                            <span><strong><?php echo $resJerarquizado['mensaje']; ?></strong></span>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="col-md-12">
                                        <a href="colegiado_especialista_tipo_form.php?id=<?php echo $idColegiadoEspecialista; ?>&tipo=<?php echo JERARQUIZADO; ?>&accion=1">Agregar jerarquizado</a>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <h4><b>Consultor</b></h4>
                            </div>
                            <?php 
                            $resConsultor = $colegiadoEspecialistaLogic->obtenerEspecialistaTipoPorIdColegiadoEspecialista($idColegiadoEspecialista, CONSULTOR);
                            if ($resConsultor['estado']) {
                                $consultor = $resConsultor['datos'];
                                $idColegiadoEspecialistaTipo = $consultor['idColegiadoEspecialistaTipo'];
                                $fechaConsultor = $consultor['fecha'];
                                $distritoOtorgante = $consultor['distritoOtorgante'];
                                if (is_numeric($distritoOtorgante)) {
                                    $otorgante = 'Distrito '.obtenerNumeroRomano($distritoOtorgante);
                                } else {
                                    $otorgante = 'Distrito '.$distritoOtorgante;
                                }
                                $numeroResolucion = $consultor['numeroResolucion'];
                                ?>
                                <div class="col-md-3">
                                    <b>Fecha:</b>  
                                    <input type="text" class="form-control" id="fechaConsultor" name="fechaConsultor" value="<?php echo cambiarFechaFormatoParaMostrar($fechaConsultor); ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <b>Otorgado por:</b>  
                                    <input type="text" class="form-control" id="distritoOtorgante" name="distritoOtorgante" value="<?php echo $distritoOtorgante; ?>" readonly>
                                </div>
                                <?php 
                                if (isset($numeroResolucion) && $numeroResolucion <> "") {
                                ?>
                                    <div class="col-md-3">
                                        <b>Número de resolución:</b>  
                                        <input type="text" class="form-control" id="numeroResolucion" name="numeroResolucion" value="<?php echo $numeroResolucion; ?>" readonly>
                                    </div>
                                <?php 
                                }
                                ?>
                                <div class="col-md-2">
                                    <br>
                                    <a href="colegiado_especialista_tipo_form.php?id=<?php echo $idColegiadoEspecialista; ?>&tipo=<?php echo CONSULTOR; ?>&accion=3">Modificar</a>
                                </div>
                                <div class="col-md-2">
                                    <br>
                                    <a href="datosColegiadoEspecialista\abm_especialista_tipo.php?id=<?php echo $idColegiadoEspecialistaTipo; ?>&idColegiado=<?php echo $idColegiado; ?>&accion=2" onclick="return confirmar()">Borrar</a>
                                </div>
                            <?php
                            } else {
                            ?>  
                                <div class="row">&nbsp;</div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="<?php echo $resConsultor['clase']; ?>" role="alert">
                                            <span class="<?php echo $resConsultor['icono']; ?>" ></span>
                                            <span><strong><?php echo $resConsultor['mensaje']; ?></strong></span>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="col-md-12">
                                        <a href="colegiado_especialista_tipo_form.php?id=<?php echo $idColegiadoEspecialista; ?>&tipo=<?php echo CONSULTOR; ?>&accion=1">Agregar consultor</a>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php 
            }
            ?>
        </div>
    <?php
    }
    ?>
</div>
<!-- BOTON VOLVER -->    
<div class="col-md-12">
    <form  method="POST" action="colegiado_especialista.php?idColegiado=<?php echo $idColegiado; ?>">
        <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
    </form>
</div>  
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
