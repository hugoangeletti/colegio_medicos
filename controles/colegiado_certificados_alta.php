<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../dataAccess/tipoCertificadoLogic.php');
$tipoCertificadoLogic = new tipoCertificadoLogic();
require_once ('../dataAccess/notaCambioDistritoLogic.php');
$notaCambioDistritoLogic = new notaCambioDistritoLogic();
require_once ('../dataAccess/distritoLogic.php');
$distritoLogic = new distritoLogic();

/*
!por notificacion del 15/11/2016, se modifica la emision de certificados
!FAP->unicamente colegiados y al día
!Colegiados al día -> todos los certificados con la leyenda Situacion con Tesoreria: al dia
!Colegiados que adeuden hasta 5 cuotas del periodo vigente -> todos los certificados SIN la leyenda 
    Situacion con Tesoreria:
!Colegiados que adeuden 6 o mas cuotas del periodo vigente -> todos los certificados con la leyenda 
    Situacion con Tesoreria: Deudor del periodo actual
!Colegiados que adeuden periodos anteriores o Planes de Pago -> No emitir certificados, solo el de 
    comisiones con la situacion con tesoreria correspondiente
!Inscriptos -> sin la leyenda de Situacion con Tesoreria
*/
$continua = TRUE;
$mensaje = "";

if (isset($_GET['idColegiado']) || isset($_POST['idColegiado'])) {
    if (isset($_GET['tramites_web'])) {
        $online = TRUE;
        $idSolicitudCertificadoWeb = $_GET['id'];
        $resSolicitud = $colegiadoCertificadosLogic->obtenerSolicitudCertificadoWebPorId($idSolicitudCertificadoWeb);
        if ($resSolicitud['estado']) {
            $solicitud = $resSolicitud['datos'];
            $idTipoCertificado = $solicitud['idTipoCertificado'];
            $presentado = $solicitud['presentado'];
        } else {
            $continua = FALSE;
            $mensaje .= 'No puede generar el certificado -> ';
        }
    } else {
        $online = FALSE;
        $idSolicitudCertificadoWeb = NULL;
        $idTipoCertificado = NULL;
    }
    if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 50)) {
        $permiteConDeuda = TRUE;
    } else {
        $permiteConDeuda = FALSE;
    }
        
    if (isset($_GET['idColegiado'])) {
        $idColegiado = $_GET['idColegiado'];
    } else {
        $idColegiado = $_POST['idColegiado'];
    }
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resContacto['estado']) {
            $contacto = $resContacto['datos'];
            $mail = $contacto['email'];
            $noEnviaMail = $contacto['noEnviaMail'];
            if ($noEnviaMail) {
                $mail = NULL;
            }
            $mailOriginal = $mail;
        } else {
            $mail = NULL;
        }



        $continua = TRUE;
    
        if (isset($_POST['mensaje'])) {
        ?>
           <div class="ocultarMensaje"> 
               <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
           </div>
         <?php
            $idColegiadoEspecialista = $_POST['idColegiadoEspecialista'];
            $idTipoCertificado = $_POST['idTipoCertificado'];
            $presentado = $_POST['presentado'];
            $distrito = $_POST['distrito'];
            $estadoConTesoreria = $_POST['estadoConTesoreria'];
            $cuotasAdeudadas = $_POST['cuotasAdeudadas'];
            $idNotaCambioDistrito = $_POST['idNotaCambioDistrito'];
            $conFirma = $_POST['conFirma'];
            $conLeyendaTeso = $_POST['conLeyendaTeso'];
            $codigoDeudor = $_POST['codigoDeudor'];
            $tipoCertificado = $_POST['tipoCertificado'];
            $enviaMail = $_POST['enviaMail'];
        } else {
            $idColegiadoEspecialista = NULL;
            if (!isset($idTipoCertificado)) {
                if (isset($_POST['idTipoCertificado'])) {
                    $idTipoCertificado = $_POST['idTipoCertificado'];
                } else {
                    $idTipoCertificado = NULL;
                }
            }
            if (!$online) {
                $presentado = '';
            }
            $distrito = '';
            $estadoConTesoreria = '';
            $cuotasAdeudadas = '';
            $idNotaCambioDistrito = NULL;
            $codigoDeudor = NULL;
            $tipoCertificado = '';
            //si es fallecido, jubilado, inscripto -> no se imprime la leyenda de Situacion Con Tesoreria
            $cod = array('F', 'I', 'J');
            if (in_array($colegiado['tipoEstado'], $cod)) {
                $conLeyendaTeso = 'N';
            } else {
                $conLeyendaTeso = 'S';
            }       
            //obtengo el estado actual con tesoreria
            if ($conLeyendaTeso == 'S') {
                $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaParaCertificadosPorColegiado($idColegiado, PERIODO_ACTUAL);
                if ($resEstadoTeso['estado']){
                    $codigoDeudor = $resEstadoTeso['codigoDeudor'];
                    $cuotasAdeudadas = $resEstadoTeso['cuotasAdeudadas'];
                    if (($codigoDeudor > 1 && !$permiteConDeuda) || ($codigoDeudor == 1 && $cuotasAdeudadas > 3)) {
                        $readOnly = 'readonly=""';
                        $idTipoCertificado = 5; //solo comisiones
                    } else {
                        $readOnly = '';
                    }
                    $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudor);
                    if ($resEstadoTesoreria['estado']){
                        $estadoConTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                    } else {
                        $estadoConTesoreria = $resEstadoTesoreria['mensaje'];
                    }                
                } else {
                    $clase = $resEstadoTeso['clase'];
                    $icono = $resEstadoTeso['icono'];
                    $mensaje = $resEstadoTeso['mensaje'];
                    $continua = FALSE;
                }
            }
        }

    ?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Solicitud de Certificado <?php if ($online) { echo 'ON-LINE'; } ?></h4>
            </div>
            <div class="col-md-3 text-left">
                <?php 
                if ($online) {
                ?>
                    <a href="certificados_online.php" class="btn btn-info">Volver a pendientes</a>
                <?php 
                } else { 
                ?>
                    <a href="colegiado_certificados.php?idColegiado=<?php echo $idColegiado;?>" class="btn btn-info">Volver a Certificados del colegiado</a>
                <?php
                }
                ?>
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
        <div class="col-md-5"><label>Estado con Tesorer&iacute;a:&nbsp; </label> <?php echo $estadoConTesoreria; ?></div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center"><h4><b>Nuevo Certificado</b></h4></div>
    </div>

    <?php
    if ($continua) {
        if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 20)) {
            $permiteSinFotoFirma = TRUE;
            $permiteExterior = TRUE;
        } else {
            $permiteSinFotoFirma = FALSE;
            $permiteExterior = FALSE;
        }
        
        if ((isset($_SESSION['tieneFoto']) && $_SESSION['tieneFoto']) || $permiteSinFotoFirma) { 
            $permiteSinFotoFirma = TRUE;
        } else {
            $permiteSinFotoFirma = FALSE;
        }

        if (isset($_POST['idCertificado'])) {
            $idCertificado = $_POST['idCertificado'];
        } else {
            $idCertificado = NULL;
        }
        if ($permiteConDeuda) {
            $resTipoCertificado = $tipoCertificadoLogic->obtenerTipoCertificadoFiltrado(0);
        } else {
            $resTipoCertificado = $tipoCertificadoLogic->obtenerTipoCertificadoFiltrado($codigoDeudor);
        }
        ?>
        <div class="row">&nbsp;</div>
        <?php 
        if (!$online) {
        ?>
        <div class="row">
            <form id="datosCertificado" autocomplete="off" name="datosCertificado" method="POST" onSubmit="" action="colegiado_certificados_alta.php" onChange="this.form.submit()" >
                <div class="col-md-6">
                    <label>Tipo de Certificado *</label>
                    <select class="form-control" id="idTipoCertificado" name="idTipoCertificado" required="" onChange="this.form.submit()">
                        <?php
                        if ($resTipoCertificado['estado']) {
                            if ($codigoDeudor <= 1 || $permiteConDeuda) {
                            ?>
                                <option value="">Seleccione Tipo de Certificado</option>
                                <?php
                                    foreach ($resTipoCertificado['datos'] as $row) {
                                        if ($row['imprimirSinFotoFirma'] == 'S' || ($row['imprimirSinFotoFirma'] == 'N' && $permiteSinFotoFirma)) {
                                            if ($row['paraExterior'] == 'N' || ($row['paraExterior'] == 'S' && $colegiado['estado'] == 3 && $permiteExterior)) {
                                                if ($colegiado['estado'] == 8 && $row['id'] == 9) { continue; }
                                        ?>
                                                <option value="<?php echo $row['id'] ?>" <?php if($idTipoCertificado == $row['id']) { echo 'selected'; } ?>><?php echo $row['nombre'] ?></option>
                                        <?php
                                            }
                                        }
                                    }
                                    ?>
                            <?php
                            } else {
                            ?>
                                <option value="<?php echo $resTipoCertificado['datos'][0]['id'] ?>" selected=""><?php echo $resTipoCertificado['datos'][0]['nombre'] ?></option>
                            <?php
                            }
                        } else {
                        ?>
                            <div class="col-md-12">
                                <div class="<?php echo $resTipoCertificado['clase']; ?>" role="alert">
                                    <span class="<?php echo $resTipoCertificado['icono']; ?>" aria-hidden="true"></span>
                                    <span><strong><?php echo $resTipoCertificado['mensaje']; ?></strong></span>
                                </div>        
                            </div>
                        <?php
                        }
                        ?>
                    </select>
                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                </div>
            </form>
        </div>
        <?php
        }
        if (isset($idTipoCertificado)) {
            $resTipoCertificado = $tipoCertificadoLogic->obtenerTipoCertificadoPorId($idTipoCertificado);
            if ($resTipoCertificado['estado']) {
                $tipoCertificado = $resTipoCertificado['datos']['detalle'];
                $certificadoConFirma = $resTipoCertificado['datos']['conFirma'];
                $muestraDestino = $resTipoCertificado['datos']['muestraDestino'];
                $aConPresentado = array('10', '11', '13', '14', '15', '16', '17', '19');
                //if ($idTipoCertificado == 10 || $idTipoCertificado == 11 || $idTipoCertificado == 13 || $idTipoCertificado == 14 || $idTipoCertificado == 15) {
                if (in_array($idTipoCertificado, $aConPresentado)) {    
                    $presentado = $tipoCertificado;
                } else {
                    if ($online) {
                        $placePresentado = "";
                    } else {
                        $presentado = "";
                        if ($idTipoCertificado == 5) {
                            $placePresentado = 'placeholder="Ingrese Nombre de la Comisión"';
                        } else {
                            $placePresentado = 'placeholder="Ingrese texto. Ejemplo: QUIEN CORRESPONDA"';
                        }
                    }
                }
                
                $conFirma = $certificadoConFirma;
                $enviaMail = 'N';
                ?>
                <form id="datosCertificado" autocomplete="off" name="datosCertificado" method="POST" onSubmit="" action="datosColegiadoCertificado/genera_certificado.php">
                <!--<form id="datosCertificado" autocomplete="off" name="datosCertificado" method="POST" onSubmit="" target="_blank" action="genera_certificado_imprime.php">-->
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <?php
                        if ($muestraDestino == 'S') {
                        ?>
                            <div class="col-md-6">
                                <label>Para ser presentado *</label>
                                <input class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" id="presentado" name="presentado" <?php echo $placePresentado; ?> value="<?php echo $presentado; ?>" required=""/>
                            </div>
                        <?php
                        } else {
                        ?>
                            <input type="hidden" name="presentado" id="presentado" value="<?php echo $presentado; ?>" />
                        <?php
                        }
                        ?>
                        <?php
                        if ($certificadoConFirma == 'S') {
                        ?>
                            <div id="grupoConFirma" class="col-md-2 text-center">
                                <label>Con firma?</label><br>
                                <label class="radio-inline"><input type="radio" name="conFirma" id="conFirma" value="S" <?php if ($conFirma == 'S') { ?> checked="" <?php } ?>>Si</label>
                                <label class="radio-inline"><input type="radio" name="conFirma" id="conFirma" value="N" <?php if ($conFirma == 'N' || $idTipoCertificado == 6) { ?> checked="" <?php } ?>>No</label>
                            </div>
                            <?php 
                            if ($idTipoCertificado == 6) {
                                //como es a todo efecto se inicializa como que no va con firma, entonces no muestar el radio de envia por mail
                                $styleEnviaMail = 'style="display: none"';
                            } else {
                                $styleEnviaMail = 'style="display: block"';
                            }
                            ?>
                            <div id="grupoEnviaMail" class="col-md-4" <?php echo $styleEnviaMail; ?>>
                                <label>Env&iacute;a por mail?</label>
                                <label class="radio-inline"><input type="radio" name="enviaMail" id="enviaMail" value="S" <?php if ($enviaMail == 'S') { ?> checked="" <?php } ?>>Si</label>
                                <label class="radio-inline"><input type="radio" name="enviaMail" id="enviaMail" value="N" <?php if ($enviaMail == 'N') { ?> checked="" <?php } ?>>No</label>
                                <input type="email" class="form-control" id="mail" name="mail" value="<?php echo $mail; ?>" placeholder="Ingrese un correo electrónico válido" >
                            </div>
                        <?php
                        } else {
                        ?>
                            <input type="hidden" name="conFirma" id="conFirma" value="<?php echo $conFirma; ?>" />
                            <input type="hidden" name="enviaMail" id="enviaMail" value="<?php echo $enviaMail; ?>" />
                            <input type="hidden" name="mail" id="mail" value="<?php echo $mail; ?>" />
                        <?php
                        }
                        ?>
                    <?php
                    if ($idTipoCertificado == 1) {
                        //muestro el campo de cambio de distrito
                    ?>
                        <div class="col-md-2">
                            <label>Distrito de cambio *</label>
                            <select class="form-control" id="distrito" name="distrito" required="" <?php echo $readOnly; ?>>
                                <?php
                                $resDistritos = $distritoLogic->obtenerDistritos();
                                if ($resDistritos['estado']) {
                                    ?>
                                    <option value="">Seleccione Distrito</option>
                                        <?php
                                            foreach ($resDistritos['datos'] as $row) {
                                                if ($row['id'] > 1) {
                                            ?>
                                                <option value="<?php echo $row['id'] ?>" <?php if($distrito == $row['id']) { echo 'selected'; } ?>><?php echo $row['romano'] ?></option>
                                            <?php
                                                }
                                            }
                                            ?>
                                    <?php
                                } else {
                                ?>
                                    <div class="col-md-12">
                                        <div class="<?php echo $resDistritos['clase']; ?>" role="alert">
                                            <span class="<?php echo $resDistritos['icono']; ?>" aria-hidden="true"></span>
                                            <span><strong><?php echo $resDistritos['mensaje']; ?></strong></span>
                                        </div>        
                                    </div>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Nota cambio de Distrito</label>
                            <select class="form-control" id="idNotaCambioDistrito" name="idNotaCambioDistrito" <?php echo $readOnly; ?>>
                                <?php
                                $resNotaCambio = $notaCambioDistritoLogic->obtenerNotasCambioDistrito();
                                if ($resNotaCambio['estado']) {
                                    ?>
                                    <option value="">Seleccione Nota</option>
                                        <?php
                                            foreach ($resNotaCambio['datos'] as $row) {
                                            ?>
                                                <option value="<?php echo $row['id'] ?>" <?php if($idNotaCambioDistrito == $row['id']) { echo 'selected'; } ?>><?php echo $row['nombre'] ?></option>
                                            <?php
                                            }
                                            ?>
                                    <?php
                                } else {
                                ?>
                                    <div class="col-md-12">
                                        <div class="<?php echo $resNotaCambio['clase']; ?>" role="alert">
                                            <span class="<?php echo $resNotaCambio['icono']; ?>" aria-hidden="true"></span>
                                            <span><strong><?php echo $resNotaCambio['mensaje']; ?></strong></span>
                                        </div>        
                                    </div>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">&nbsp;</div>
                    <?php
                    }
                    
                    if ($idTipoCertificado == 3) {
                        //muestro el campo de especialista
                    ?>
                            <div class="col-md-6">
                                <label>Especialidades *</label>
                                <select class="form-control" id="idColegiadoEspecialista" name="idColegiadoEspecialista" required="">
                                    <?php
                                    $resEspecialidades = $colegiadoEspecialistaLogic->obtenerEspecialidadesPorIdColegiadoVigentes($idColegiado);
                                    if ($resEspecialidades['estado']) {
                                        foreach ($resEspecialidades['datos'] as $row) {
                                        ?>
                                            <option value="<?php echo $row['idColegiadoEspecialista'] ?>" <?php if($idColegiadoEspecialista == $row['idColegiadoEspecialista']) { echo 'selected'; } ?>><?php echo $row['nombreEspecialidad'].' (Con fecha: '.$row['fechaEspecialista'].')' ?></option>
                                        <?php
                                        }
                                    } else {
                                    ?>
                                        <div class="col-md-12">
                                            <div class="<?php echo $resEspecialidades['clase']; ?>" role="alert">
                                                <span class="<?php echo $resEspecialidades['icono']; ?>" aria-hidden="true"></span>
                                                <span><strong><?php echo $resEspecialidades['mensaje']; ?></strong></span>
                                            </div>        
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                    <?php
                    }
                    ?>
                        </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-6 text-right">
                            <button type="submit"  class="btn btn-success btn-lg" >Confirma Certificado</button>
                            <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                            <input type="hidden" name="idTipoCertificado" id="idTipoCertificado" value="<?php echo $idTipoCertificado; ?>" />
                            <input type="hidden" name="mailOriginal" id="mailOriginal" value="<?php echo $mailOriginal; ?>" />
                            <input type="hidden" name="estadoConTesoreria" id="estadoConTesoreria" value="<?php echo $estadoConTesoreria; ?>" />
                            <input type="hidden" name="cuotasAdeudadas" id="cuotasAdeudadas" value="<?php echo $cuotasAdeudadas; ?>" />
                            <input type="hidden" name="conLeyendaTeso" id="conLeyendaTeso" value="<?php echo $conLeyendaTeso; ?>" />
                            <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="<?php echo $codigoDeudor; ?>" />
                            <input type="hidden" name="tipoCertificado" id="tipoCertificado" value="<?php echo $tipoCertificado; ?>" />
                            <?php 
                            if ($online) {
                            ?>
                                <input type="hidden" name="idSolicitudCertificadoWeb" id="idSolicitudCertificadoWeb" value="<?php echo $idSolicitudCertificadoWeb; ?>" />
                            <?php 
                            }
                            ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php 
                            //si cargo el certificado con exito, muestro boton para imprimir
                            if (isset($idCertificado)) {
                            ?>
                                <a href="datosColegiadoCertificado/imprimir_certificado.php?idColegiado=<?php echo $idColegiado;?>&idCertificado=<?php echo $idCertificado; ?>" class="btn btn-default" role="button" target="_blank">Imprimir Certificado</a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>    
                </form>
            <?php
            } else {
            ?>
                <div class="col-md-12">
                    <div class="<?php echo $resTipoCertificado['clase']; ?>" role="alert">
                        <span class="<?php echo $resTipoCertificado['icono']; ?>" aria-hidden="true"></span>
                        <span><strong><?php echo $resTipoCertificado['mensaje']; ?></strong></span>
                    </div>        
                </div>
            <?php
            }
        }
    } else {
    ?>
        <div class="col-md-12">
            <div class="<?php echo $clase; ?>" role="alert">
                <span class="<?php echo $icono; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $mensaje; ?></strong></span>
            </div>        
        </div>
    <?php
    }
    ?>
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
        <div class="alert alert-error" role="alert">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
            <span><strong>Ingreso incorrecto!</strong></span>
        </div>        
    </div>
    <div class="row">&nbsp;</div>
    <div class="col-md-12">
        <?php 
        if ($online) {
        ?>
            <a href="certificados_online.php" class="btn btn-success">Volver a pendientes</a>
        <?php 
        } else { 
        ?>
            <a href="colegiado_consulta.php" class="btn btn-success">Volver a buscar colegiado</a>
        <?php
        }
        ?>
    </div>
<?php
}
require_once '../html/footer.php';
?>
<script>
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="conFirma"]:checked').val();
    });
    $(document).on('click', '[name="conFirma"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            var x = document.getElementById("grupoEnviaMail");
            //if (x.style.display === "none") {
            if (lastSelected != 'S') {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
            //alert("radio box with value " + $('[name="conFirma"][value="' + lastSelected + '"]').val() + " was deselected");
        }
        lastSelected = $(this).val();
    });
    /*
    var rad = document.datosCertificado.conFirma;
    var prev = null;
    for(var i = 0; i < rad.length; i++) {
        rad[i].onclick = function() {
            (prev)? console.log(prev.value):null;
            if(this !== prev) {
                prev = this;
                alert('Cambia radio');
            }
            console.log(this.value)
        };
    }
    */
</script>