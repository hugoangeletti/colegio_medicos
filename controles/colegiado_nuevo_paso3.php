<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
?>
<script>
      $(document).ready(function()
      {
         $("#myModal").modal("show");
      });
    </script>
<?php
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaAltaMatriculaLogic.php');
$mesaEntradaAltaMatriculaLogic = new mesaEntradaAltaMatriculaLogic();
require_once ('../dataAccess/colegiadoArchivoLogic.php');
$colegiadoArchivoLogic = new colegiadoArchivoLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiacionAnualLogic.php');
$colegiacionAnualLogic = new colegiacionAnualLogic();

if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Alta";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    if (isset($_GET['tipo']) || isset($_POST['tipoIngreso'])){
        $otroDistrito = TRUE;
        $panel = "panel-warning";
        $titulo = "Alta de Matricula de Otro Distrito";
        $botonConfirma = "btn-warning";
        $matriculaReadOnly = '';
        $idTipoMovimiento = $_POST['tipoMovimiento'];
        $distritoOrigen = $_POST['distritoOrigen'];
        $fechaOtroDistrito = $_POST['fechaOtroDistrito'];
        if (isset($_GET['tipo'])) {
            $tipoIngreso = "?tipo=".$_GET['tipo'];
        } else {
            if (isset($_POST['tipoIngreso'])) {
                $tipoIngreso = "?tipo=".$_POST['tipoIngreso'];            
            } else {
                $idColegiado = NULL;
            }
        }
        
    } else {
        $otroDistrito = FALSE;
        $panel = "panel-success";
        $titulo = "Alta de matriculado del Distrito I";
        $botonConfirma = "btn-success";
        $matriculaReadOnly = 'readonly=""';
        $idTipoMovimiento = NULL;
        $distritoOrigen = NULL;
        $fechaOtroDistrito = NULL;
    }
    
    if (isset($_GET['idMesaEntrada'])) {
        $idMesaEntrada = $_GET['idMesaEntrada'];
    } else {
        $idMesaEntrada = NULL;
    }
    
    //si es de otro distrito debo generar la mesa de entrada
    if (isset($_GET['tipo']) && $_GET['tipo'] == 'otro') {
        $botonMesa = TRUE;
        //verifico si ya no tiene generado el movimiento en mesa de entradas
        $resMesaNueva = $mesaEntradaAltaMatriculaLogic->noHayMesaEntradaRegistrada($idColegiado, $idTipoMovimiento);
        if ($resMesaNueva['estado']) {
            //se genera el movimiento en mesa de entradas
            $resMesa = $mesaEntradaAltaMatriculaLogic->realizarAltaMesaEntrada($idColegiado, $idTipoMovimiento, $distritoOrigen);
            if ($resMesa['estado']) {
                $idMesaEntrada = $resMesa['idMesaEntrada'];
            } else {
            ?>
                <div class="<?php echo $resMesa['clase']; ?>" role="alert">
                    <span class="<?php echo $resMesa['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resMesa['mensaje']; ?></strong></span>
                </div>        
            <?php
                $botonMesa = FALSE;
            }
        } else {
            $idMesaEntrada = $resMesaNueva['idMesaEntrada'];
            if ($idMesaEntrada == 0) {
                $botonMesa = FALSE;
            ?>
                <div class="alert alert-error" role="alert">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    <span><strong>HUBO UN ERROR AL GENERAR EL MOVIMIENTO EN LA MESA DE ENTRADAS; DEBE IR AL SISTEMA A INGRESAR ESTE NUEVO MOVIMIENTO</strong></span>
                </div>        
            <?php                        
            }
        }
    } else {
        $botonMesa = FALSE;
    }

    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $estado = $colegiado['estado'];
        $fechaTitulo = $colegiado['fechaTitulo'];
        $tipoMovimiento = $colegiado['tipoEstado'];
        $fechaTituloMinima = $periodoActual.'-05-31';
        $antiguedad = calcular_antiguedad($fechaTitulo, $fechaTituloMinima);
        $estadoMatricular = $colegiado['idEstadoMatricular'];
        $tituloDigital = $colegiado['tituloDigital'];
    }
    //genero la chequera en caso de no ser un inscripto
    if ($idTipoMovimiento != 8) {
        $botonChequera = TRUE;
        if ($colegiadoDeudaAnualLogic->noTieneDeudaAnual($idColegiado)) {
            $resColegiacion = $colegiacionAnualLogic->obtenerColegiacionAnualPorPeriodo($periodoActual, $antiguedad);
            if ($resColegiacion['estado']) {
                $datosColegiacion = $resColegiacion['datos'][0];
                $resCuotasColegiacion = $colegiacionAnualLogic->obtenerColegiacionAnualCuotas($periodoActual);
                if ($resCuotasColegiacion['estado']) {
                    $cuotasLiquidar = $resCuotasColegiacion['datos'];
                    $resDeudaAnual = $colegiacionAnualLogic->generarColegiacionAnual($idColegiado, $antiguedad, $estadoMatricular, $datosColegiacion, "N", "", NULL, $cuotasLiquidar);
                    //$resDeudaAnual = generarDeudaAnual($idColegiado, $fechaTitulo, $estado);
                    if (!$resDeudaAnual['estado']) {
                        $botonChequera = FALSE;
                    ?>
                        <div class="<?php echo $resDeudaAnual['clase']; ?>" role="alert">
                            <span class="<?php echo $resDeudaAnual['icono']; ?>" aria-hidden="true"></span>
                            <span><strong><?php echo $resDeudaAnual['mensaje']; ?></strong></span>
                        </div>        
                    <?php
                    }
                } else {
                    $botonChequera = FALSE;
                    ?>
                    <div class="<?php echo $resColegiacion['clase']; ?>" role="alert">
                        <span class="<?php echo $resColegiacion['icono']; ?>" aria-hidden="true"></span>
                        <span><strong><?php echo $resColegiacion['mensaje']; ?></strong></span>
                    </div>        
                <?php
                }
            } else {
                $botonChequera = FALSE;
                ?>
                <div class="<?php echo $resColegiacion['clase']; ?>" role="alert">
                    <span class="<?php echo $resColegiacion['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resColegiacion['mensaje']; ?></strong></span>
                </div>        
            <?php
            }
        }
    } else {
        $botonChequera = FALSE;
    }    
    
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        //busco si estan las imagenes para cargar en la tabla
        $hayArchivos = FALSE;
        $fileFoto = rellenarCeros($matricula, 8).'.jpg';
        $fileFirma = rellenarCeros($matricula, 8).'.bmp';
        $foto = @fopen (FTP_ARCHIVOS."/Fotos/".$fileFoto, "rb");
        if ($foto) {
            $firma = @fopen (FTP_ARCHIVOS."/Firmas/".$fileFirma, "rb");
            if ($firma) {
                if ($tituloDigital == 1) {
                    //es con titulo digital, lo busco
                    $fileTitulo = rellenarCeros($matricula, 8).'.pdf';
                    $titulo = @fopen (FTP_ARCHIVOS."/Titulos/".$fileTitulo, "rb");
                    if ($titulo) {
                        $tituloDetalle = 'El título digital fue ingresado.';
                    } else {
                        $tituloDetalle = 'ERROR: no encontro el titulo digital';
                    }
                }
                $hayArchivos = TRUE;
            }
        } 
        if ($hayArchivos) {
        ?>
        <div class="panel <?php echo $panel; ?>">
            <div class="panel-heading">
                <h4><?php echo $titulo; ?></h4>
            </div>
            <div class="panel-body">
                <?php if ($tipoMovimiento != 'I') { ?>
                    <h4>Nuevo colegiado, debe imprimir la chequera del período.</h4>
                <?php } else { ?>
                    <h4>Nuevo Inscripto.</h4>
                <?php } ?>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-7">
                        <div class="row">
                            <div class="col-md-8">
                                Apellido y Nombres
                                <b><input class="form-control" type="text" value="<?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>" readonly=""/></b>
                            </div>
                            <div class="col-md-4">
                                Matr&iacute;cula
                                <b><input class="form-control" type="text" value="<?php echo $colegiado['matricula']; ?>" readonly=""/></b>
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-5">
                        <?php
                        $tieneFotoFirma = FALSE;
                        if (isset($_GET['err'])) {
                        ?>
                            <div class="alert alert-danger ocultarMensaje">No se encontraron los archivos para asociar, verifique si los nombres son correctos.</div>
                        <?php
                        } else {
                            ?>
                            <div class="col-md-4">
                            <?php
                            //verifica que tenga foto y firma para mostrar
                            $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '1');
                            if ($resArchivos['estado'] && isset($resArchivos['datos'])){
                                $archivos = $resArchivos['datos'];
                                $fileFoto = trim($archivos['nombre']);
                                // insertamos la foto y firma
                                $foto = @fopen (FTP_ARCHIVOS."/Fotos/".$fileFoto, "rb");
                                if ($foto) {
                                    $contents=stream_get_contents($foto);

                                    $fotoVer = base64_encode($contents);
                                    $tieneFotoFirma = TRUE;
                                    ?>
                                <img class="img img-thumbnail" style="height: 150px " src="data:image/jpg;base64,<?php echo $fotoVer; ?>" />
                                <br>Foto
                            <?php
                                }
                            }
                            ?>
                            </div>
                            <div class="col-md-4">
                            <?php
                            $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '2');
                            if ($resArchivos['estado'] && isset($resArchivos['datos'])){
                                $archivos = $resArchivos['datos'];
                                $fileFirma = trim($archivos['nombre']);
                                $firma = @fopen (FTP_ARCHIVOS."/Firmas/".$fileFirma, "rb");
                                if ($firma) {
                                    $contents=stream_get_contents($firma);
                                    $firmaVer = base64_encode($contents);
                                    $tieneFotoFirma = TRUE;
                                    ?>
                                    <img class="img img-thumbnail" src="data:image/jpg;base64,<?php echo $firmaVer; ?>" height="80" width="200" />
                                    <br>Firma
                            <?php
                                }
                            }
                            ?>
                            </div>
                            <div class="col-md-4">
                            <?php
                            if ($tituloDigital == 1) {
                                if (substr($tituloDetalle, 0, 5) == 'ERROR' ) {
                                ?>
                                        <div class="alert alert-danger"><?php echo $tituloDetalle; ?></div>
                                <?php
                                } else {
                                    ?>
                                    <div class="alert alert-success"><?php echo $tituloDetalle; ?></div>
                            <?php
                                }
                            } 
                            ?>
                                <a href="http://colmed2.com.ar/TitulosDigitales/LoginDigitales" target="_BLANK">DEBE INGRESAR AL NUEVO MATRICULADO AL SISTEMA UNIFICADO</a>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                
                <?php
                if ($botonMesa) {
                ?>               
                    <!-- datosColegiado/imprimirHojaRutaMovimiento.php?idColegiado=<?php echo $idColegiado; ?>&idMesaEntrada=<?php echo $idMesaEntrada; ?> -->     
                    <div class="row">&nbsp;</div>
                    <div class="col-md-12 text-center">
                        <form id="imprimeHojaRuta" name="imprimeHojaRuta" method="POST" target="_BLANK" 
                              action="mesa_entrada_imprimir.php?id=<?php echo $idMesaEntrada; ?>&ingreso=NUEVA_MATRICULA">
                            <div class="col-md-12">&nbsp;</div>
                            <div class="col-md-12">
                                <button type="submit"  class="btn btn-info btn-lg" onclick="show('cerrar')">Imprimir Hoja de Ruta (Mesa de Entradas) </button>
                            </div>
                        </form>
                    </div>
                <?php
                }
                if ($botonChequera) {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="col-md-12 text-center">
                        <form id="imprimeChequera" name="imprimeChequera" method="POST" onSubmit="" target="_BLANK" action="colegiado_tesoreria_imprimir.php?idColegiado=<?php echo $idColegiado; ?>">
                            <div class="col-md-12">&nbsp;</div>
                            <div class="col-md-12">
                                <button type="submit"  class="btn btn-info btn-lg" onclick="show('cerrar')">Imprimir Chequera </button>
                                    <input type="hidden" name="tipoPdf" id="tipoPdf" value="I" />
                                    <input type="hidden" name="imprimir" id="imprimir" value="PA" />
                                <?php
                                if ($otroDistrito) {
                                ?>
                                    <input type="hidden" name="tipoIngreso" id="tipoIngreso" value="otro" />
                                <?php
                                }
                                ?>
                            </div>
                        </form>
                    </div>
                <?php                     
                }
                ?>
                <form id="cerrar" name="cerrar" method="POST" style="display: <?php if ($tipoMovimiento != 'I' && !$botonChequera) { ?> none; <?php } else { ?> block; <?php } ?>" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>">
                    <div class="row">&nbsp;</div>
                    <div class="col-md-12 text-right">
                        <button type="submit"  class="btn <?php echo $botonConfirma; ?> btn-lg" >Cerrar </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        } else {
            ?>
            <div class="col-md-6">
                <h4>Debe tomar la foto y la firma, luego asociarla a la nueva matrícula.</h4>
            </div>
        <?php
        }
        ?>
    <?php
    } else {
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php
    }
}
require_once '../html/footer.php';
?>
<script type="text/javascript">
    function show(bloq) {
	 obj = document.getElementById(bloq);
         obj.style.display = 'block';
	 //obj.style.display = (obj.style.display=='none') ? 'block' : 'none';
    }
</script>