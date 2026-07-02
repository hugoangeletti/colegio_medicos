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

$continua = TRUE;
$mensaje = "";
$readOnly = "";
$requerido = "";
$mesaEntradaLogic = new mesaEntradaLogic();
?>
<div class="panel panel-default">
    <?php
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idMesaEntrada = $_GET['id'];
        if (isset($_GET['ingreso']) && ($_GET['ingreso'] == "FECHA" || $_GET['ingreso'] == "FECHA_TIPO" || $_GET['ingreso'] == "COLEGIADO" || $_GET['ingreso'] == "OTRO")) {
            $accedePor = $_GET['ingreso'];
        } else {
            $accedePor = NULL;
        }
        $resMesaEntrada = $mesaEntradaLogic->obtenerMesaEntradaPorId($idMesaEntrada);
        if ($resMesaEntrada['estado']) {
            $mesaEntrada = $resMesaEntrada['datos'];
            $idTipoMesaEntrada = $mesaEntrada['idTipoMesaEntrada'];
            $idColegiado = $mesaEntrada['idColegiado'];
            $idRemitente = $mesaEntrada['idRemitente'];
            $fechaIngreso = $mesaEntrada['fechaIngreso'];
            $observaciones = $mesaEntrada['observaciones'];

            switch ($idTipoMesaEntrada) {
                case '1':
                    // Movimientos Matriculares
                    $titulo = "MOVIMIENTOS MATRICULARES";
                    $resMesaEntradaMovimiento = $mesaEntradaLogic->obtenerMesaEntradaMovimientoPorId($idMesaEntrada);
                    if ($resMesaEntradaMovimiento['estado']) {
                        $mesaEntradaMovimiento = $resMesaEntradaMovimiento['datos'];
                        $nombreTipoMovimiento = $mesaEntradaMovimiento['nombreTipoMovimiento'];
                        $nombreTipoMovimientoCompleto = $mesaEntradaMovimiento['nombreTipoMovimientoCompleto'];
                        $nombreMotivoCancelacion = $mesaEntradaMovimiento['nombreMotivoCancelacion'];
                        $distrito = $mesaEntradaMovimiento['distrito'];
                        $nombrePatologia = $mesaEntradaMovimiento['nombrePatologia'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $mesaEntradaMovimiento['mensaje'];
                        $clase = $mesaEntradaMovimiento['clase'];
                    }
                    break;
                
                case '2':
                    // Especialidades
                    $titulo = "ESPECIALIDADES";
                    $resMesaEntradaEspecialista = $mesaEntradaLogic->obtenerMesaEntradaEspecialidadPorId($idMesaEntrada);
                    if ($resMesaEntradaEspecialista['estado']) {
                        $mesaEntradaEspecialista = $resMesaEntradaEspecialista['datos'];
                        $idEspecialidad = $mesaEntradaEspecialista['idEspecialidad'];
                        $nombreEspecialidad = $mesaEntradaEspecialista['nombreEspecialidad'];
                        $numeroExpediente = $mesaEntradaEspecialista['numeroExpediente'];
                        $anioExpediente = $mesaEntradaEspecialista['anioExpediente'];
                        $distrito = $mesaEntradaEspecialista['distrito'];
                        $nombreTipoEspecialista = $mesaEntradaEspecialista['nombreTipoEspecialista'];
                        $incisoArticulo8 = $mesaEntradaEspecialista['incisoArticulo8'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $mesaEntradaEspecialista['mensaje'];
                        $clase = $mesaEntradaEspecialista['clase'];
                    }
                    break;
                
                case '3':
                    // Notas
                    $puedeEditar = FALSE;
                    if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 111)) {
                        $puedeEditar = TRUE;
                    }
                    $titulo = "NOTAS Y OFICIOS";
                    $resMesaEntradaNota = $mesaEntradaLogic->obtenerMesaEntradaConNotaPorId($idMesaEntrada);
                    if ($resMesaEntradaNota['estado']) {
                        $mesaEntradaNota = $resMesaEntradaNota['datos'];
                        $idMesaEntradaNota = $mesaEntradaNota['idMesaEntradaNota'];
                        $tema = $mesaEntradaNota['tema'];
                        $incluyeMovimiento = $mesaEntradaNota['incluyeMovimiento'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resMesaEntradaNota['mensaje'];
                        $clase = $resMesaEntradaNota['clase'];
                    }
                    break;
                
                case '4':
                    // Habilitación de Consultorio
                    $titulo = "Habilitación de Consultorios";
                    $idMesaEntradaConsultorio = NULL;
                    $resMesaEntradaConsultorio = $mesaEntradaLogic->obtenerMesaEntradaConsultorioPorId($idMesaEntradaConsultorio, $idMesaEntrada);
                    if ($resMesaEntradaConsultorio['estado']) {
                        $mesaEntradaConsultorio = $resMesaEntradaConsultorio['datos'];
                        $idMesaEntradaConsultorio = $mesaEntradaConsultorio['idMesaEntradaConsultorio'];
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
                        //var_dump($resConsultorioOtrosMedicos);
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

                    break;
                
                case '5':
                    // Matricula J
                    $titulo = "MATRICULA J";
                    break;
                
                case '7':
                    // Autoprescripción
                    $titulo = "AUTOPRESCRIPCIÓN";
                    $resMesaEntradaAutoprescripcion = $mesaEntradaLogic->obtenerMesaEntradaAutoprescripcionPorId($idMesaEntrada);
                    if ($resMesaEntradaAutoprescripcion['estado']) {
                        $mesaEntradaAutoprescripcion = $resMesaEntradaAutoprescripcion['datos'];
                        $idMesaEntradaAutoprescripcion = $mesaEntradaAutoprescripcion['idMesaEntradaAutoprescripcion'];
                        $autorizado1 = $mesaEntradaAutoprescripcion['autorizado1'];
                        $autorizado2 = $mesaEntradaAutoprescripcion['autorizado2'];
                        $documentoAutorizado1 = $mesaEntradaAutoprescripcion['documentoAutorizado1'];
                        $documentoAutorizado2 = $mesaEntradaAutoprescripcion['documentoAutorizado2'];
                        $parentezco1 = $mesaEntradaAutoprescripcion['parentezco1'];
                        $parentezco2 = $mesaEntradaAutoprescripcion['parentezco2'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resMesaEntradaEntregas['mensaje'];
                        $clase = $resMesaEntradaEntregas['clase'];
                    }
                    break;
                
                case '8':
                    // Anulación de Movimiento Matricular
                    $titulo = "Anulación de Movimiento Matricular";
                    $resMesaEntradaAnulacion = $mesaEntradaLogic->obtenerMesaEntradaAnulacionPorId($idMesaEntrada);
                    if ($resMesaEntradaAnulacion['estado']) {
                        $mesaEntradaAnulacion = $resMesaEntradaAnulacion['datos'];
                        $idMesaEntradaAnulacion = $mesaEntradaAnulacion['idMesaEntradaAnulacion'];
                        $fechaMovimiento = $mesaEntradaAnulacion['fechaMovimiento'];
                        $idTipoMovimiento = $mesaEntradaAnulacion['idTipoMovimiento'];
                        $nombreTipoMovimiento = $mesaEntradaAnulacion['nombreTipoMovimiento'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resMesaEntradaAnulacion['mensaje'];
                        $clase = $resMesaEntradaAnulacion['clase'];
                    }
                    break;
                
                case '9':
                    // Denuncia de Extravío o Falsificación
                    $titulo = "Denuncia de Extravío o Falsificación";
                    $resMesaEntradaDenuncia = $mesaEntradaLogic->obtenerMesaEntradaDenunciaPorId($idMesaEntrada);
                    if ($resMesaEntradaDenuncia['estado']) {
                        $mesaEntradaDenuncia = $resMesaEntradaDenuncia['datos'];
                        $idMesaEntradaDenuncia = $mesaEntradaDenuncia['idMesaEntradaDenuncia'];
                        $fechaDenuncia = $mesaEntradaDenuncia['fechaDenuncia'];
                        $fechaExtravio = $mesaEntradaDenuncia['fechaExtravio'];
                        $idTipoDenuncia = $mesaEntradaDenuncia['idTipoDenuncia'];
                        $nombreTipoDenuncia = $mesaEntradaDenuncia['nombreTipoDenuncia'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resMesaEntradaDenuncia['mensaje'];
                        $clase = $resMesaEntradaDenuncia['clase'];
                    }
                    break;
                
                case '10':
                    // Entregas
                    $titulo = "ENTREGAS";
                    $resMesaEntradaEntregas = $mesaEntradaLogic->obtenerEntregaPorIdMesaEntrada($idMesaEntrada);
                    if ($resMesaEntradaEntregas['estado']) {
                        $mesaEntradaEntrega = $resMesaEntradaEntregas['datos'];
                        $idColegiado = $mesaEntradaEntrega['idColegiado'];
                        $fechaEntrega = $mesaEntradaEntrega['fechaEntrega'];
                        $nombreTipoEntrega = $mesaEntradaEntrega['nombreTipoEntrega'];
                        $leyendaTipoEntrega = $mesaEntradaEntrega['leyendaTipoEntrega'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resMesaEntradaEntregas['mensaje'];
                        $clase = $resMesaEntradaEntregas['clase'];
                    }
                    break;
                
                default:
                    // ERROR
                    $continua = FALSE;
                    $mensaje .= "ERROR: idTipoMesaEntrada inválido.";
                    break;
            }

            if ($continua) {
                if (isset($idColegiado) && $idColegiado <> "") {
                    //obtenemos los datos del colegiado
                    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                    if ($resColegiado['estado'] && $resColegiado['datos']) {
                        $colegiado = $resColegiado['datos'];
                        $matricula = $colegiado['matricula'];
                        $esColegiado = "S";
                        $nombreRemitente = 'Matrícula: '.$matricula.' <br>Apellido y Nombre: '.trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resColegiado['mensaje'];
                        $clase = $resColegiado['clase'];
                    }
                } else {
                    if (isset($idRemitente) && $idRemitente <> "") {
                        //obtenemos los datos del colegiado
                        $remitenteLogic = new remitenteLogic();
                        $resRemitente = $remitenteLogic->obtenerRemitentePorId($idRemitente);
                        if ($resRemitente['estado']) {
                            $remitente = $resRemitente['datos'];
                            $nombreRemitente = 'Remitente: '.$remitente['nombre'];
                            $esColegiado = "N";
                        } else {
                            $continua = FALSE;
                            $mensaje .= $resRemitente['mensaje'];
                            $clase = $resRemitente['clase'];
                        }
                    } else {
                        $continua = FALSE;
                        $mensaje .= 'Error al acceder a los datos mesa de entradas';
                        $clase = 'alert alert-danger';
                    }
                }
            } 
        } else {
            $continua = FALSE;
            $mensaje .= $resMesaEntrada['mensaje'];
            $clase = $resMesaEntrada['clase'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'Error de acceso';
        $clase = 'alert alert-danger';
    }

?>
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $titulo.' - MESA ENTRADA Nº '.$idMesaEntrada.' de fecha '.cambiarFechaFormatoParaMostrar($fechaIngreso); ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="col-md-12">
            <h4><?php echo $nombreRemitente; ?></h4>
        </div>
        <?php
        //muestra los datos de la mesa de entradas
        switch ($idTipoMesaEntrada) {
            case '1':
                // Movimientos Matriculares
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <h4><b>Tipo de Movimiento: </b><?php echo $nombreTipoMovimiento.' ('.$nombreTipoMovimientoCompleto.')'; ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4><b>Motivo: </b><?php echo $nombreMotivoCancelacion; ?></h4>
                    </div>
                </div>
                <?php 
                if ($nombrePatologia <> "") {
                ?>
                    <div class="row"><hr></div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4><b>Patología: </b><?php echo $nombrePatologia; ?></h4>
                        </div>
                    </div>
                <?php 
                }
                break;
            
            case '2':
                // Especialidades
                ?>
                <div class="row">
                    <div class="col-md-8">
                        <h4><b>Tipo de Especialista: </b><?php echo $nombreTipoEspecialista; ?></h4>
                    </div>
                    <?php 
                    if ($incisoArticulo8 <> "") {
                    ?>
                        <div class="col-md-4">
                            <h4><b>Inciso Art. 8: </b><?php echo $incisoArticulo8; ?></h4>
                        </div>
                    <?php 
                    } 
                    ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4><b>Especialidad: </b><?php echo $nombreEspecialidad; ?></h4>
                    </div>
                </div>
                <?php
                break;
            
            case '3':
                // Notas
                ?>
                <form id="formNota" name="formNota" method="POST" onSubmit="" action="datosMesaEntrada\abm_nota_oficio.php?notas&id=<?php echo $idMesaEntrada; ?>">
                    <?php 
                    if (isset($idRemitente) && $idRemitente <> "") {
                    ?>
                        <div class="row">
                            <div class="col-md-4">
                                <b>Incluye Movimientos *</b>
                                <br>
                                <label class="radio-inline"><input type="radio" name="incluyeMovimiento" id="incluyeMovimientoSi" value="S" <?php if ($incluyeMovimiento == 'S') { ?> checked="" <?php } ?>>Si</label>
                                <label class="radio-inline"><input type="radio" name="incluyeMovimiento" id="incluyeMovimientoNo" value="N" <?php if ($incluyeMovimiento == 'N') { ?> checked="" <?php } ?>>No</label>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                    <?php
                    }
                    ?>
                    <div class="row">
                        <div class="col-md-8">
                            <label for="tema">Tema *</label>
                            <input class="form-control" autocomplete="OFF" type="text" name="tema" id="tema" placeholder="Ingrese Tema de Nota/Oficio" value="<?php echo $tema; ?>" required=""/>
                        </div>
                    </div>  
                    <div class="row"><hr></div>
                    <div class="row">
                        <div class="col-md-8">
                            <label for="observaciones">Observaciones </label>
                            <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="5" ><?php echo utf8_decode($observaciones); ?></textarea>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>    
                    <div class="row">
                        <div class="col-md-8 text-center">
                            <button type="submit" class="btn btn-success" >Guardar cambios</button>
                            <input type="hidden" name="accion" id="accion" value="MODIFICAR">
                            <input type="hidden" name="idMesaEntradaNota" id="idMesaEntradaNota" value="<?php echo $idMesaEntradaNota; ?>">
                            <input type="hidden" name="esColegiado" id="esColegiado" value="<?php echo $esColegiado; ?>">
                            <?php 
                            if ($esColegiado == "S") {
                            ?>
                                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>">
                                <input type="hidden" name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $nombreRemitente; ?>">
                            <?php 
                            } else {
                            ?>
                                <input type="hidden" name="idRemitente" id="idRemitente" value="<?php echo $idRemitente; ?>">
                                <input type="hidden" name="remitente_buscar" id="remitente_buscar" value="<?php echo $nombreRemitente; ?>">
                            <?php
                            }
                            ?>
                        </div>
                    </div>  
                </form>                    
                <?php
                break;        
            
            case '5':
                // Matricula J
                break;
            
            case '7':
                // Autoprescripción
                if ($autorizado1 == "" && $autorizado2 == "") {
                ?>
                    <div class="row">
                        <div class="col-md-4">
                            <h4><b>Sin Autorizados</h4>
                        </div>
                    </div>
                <?php
                } else {
                    if ($autorizado1 <> "") {
                    ?>
                        <div class="row">
                            <div class="col-md-4">
                                <h4><b>Autorizado: </b><?php echo $autorizado1; ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4><b>Documento N°: </b><?php echo $documentoAutorizado1; ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4><b>Parentezco: </b><?php echo $parentezco1; ?></h4>
                            </div>
                        </div>
                    <?php 
                    } 

                    if ($autorizado2 <> "") {
                    ?>
                        <div class="row">
                            <div class="col-md-4">
                                <h4><b>Autorizado: </b><?php echo $autorizado2; ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4><b>Documento N°: </b><?php echo $documentoAutorizado2; ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4><b>Parentezco: </b><?php echo $parentezco2; ?></h4>
                            </div>
                        </div>
                    <?php
                    }
                }
                break;
            
            case '8':
                // Anulación de Movimiento Matricular
                ?>
                <div class="row">
                    <div class="col-md-4">
                        <h4><b>Movimiento Anulado: </b><?php echo $nombreTipoMovimiento; ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h4><b>Fecha del movimiento: </b><?php echo cambiarFechaFormatoParaMostrar($fechaMovimiento); ?></h4>
                    </div>
                </div>
                <?php
                break;
            
            case '9':
                // Denuncia de Extravío o Falsificación
                ?>
                <div class="row">
                    <div class="col-md-4">
                        <h4><b>Tipo de denuncia: </b><?php echo $nombreTipoDenuncia; ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <h4><b>Fecha de la denuncia: </b><?php echo cambiarFechaFormatoParaMostrar($fechaDenuncia); ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <h4><b>Fecha Extravío / Falsificación / Robo: </b><?php echo cambiarFechaFormatoParaMostrar($fechaExtravio); ?></h4>
                    </div>
                </div>
                <?php
                break;
            
            case '10':
                // Entregas
                ?>
                <div class="row">
                    <div class="col-md-8">
                        <h4><b>Tipo de Entrega: </b><?php echo $nombreTipoEntrega.' ('.$leyendaTipoEntrega.')'; ?></h4>
                    </div>
                </div>
                <?php
                break;
            
            default:
                // ERROR
                $continua = FALSE;
                $mensaje .= "ERROR: idTipoMesaEntrada inválido.";
                break;
        }

        if ($idTipoMesaEntrada <> 3) {
            //si no es Nota/Oficio, muestro observaciones
        ?>
            <div class="row"><hr></div>
            <div class="row">
                <div class="col-md-8">
                    <label for="observaciones">Observaciones </label>
                    <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="5" readOnly><?php echo utf8_decode($observaciones); ?></textarea>
                </div>
            </div>
            <div class="row">&nbsp;</div>    
        <?php
        }
        if (!$continua) {
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
        <?php 
        include('mesa_entrada_volver_listado.php');
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';
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
