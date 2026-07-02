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
require_once ('../dataAccess/colegiadoArchivoLogic.php');
$colegiadoArchivoLogic = new colegiadoArchivoLogic();

if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Alta";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $tipoIngreso = "";
    if (isset($_GET['tipo']) || isset($_POST['tipoIngreso'])){
        $otroDistrito = TRUE;
        $panel = "panel-warning";
        $titulo = "Alta de Matricula de Otro Distrito";
        $botonConfirma = "btn-warning";
        $matriculaReadOnly = '';
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
    }
    $display = 'none';
    
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        ?>
        <div class="panel <?php echo $panel; ?>">
            <div class="panel-heading">
                <h4><?php echo $titulo; ?></h4>
            </div>
            <div class="panel-body">
                <?php
                if (isset($_GET['err'])) {
                ?>
                    <h4>Debe tomar la foto y la firma, luego asociarla a la nueva matrícula.</h4>
                <?php 
                } else {
                ?>
                    <h4>La foto y la firma están asociadas al matriculado.</h4>
                <?php
                }
                ?>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-3">
                        Apellido y Nombres
                        <b><input class="form-control" type="text" value="<?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-2">
                        Matr&iacute;cula
                        <b><input class="form-control" type="text" value="<?php echo $colegiado['matricula']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-7">
                        <?php
                        //busco si estan las imagenes para cargar en la tabla
                        $hayArchivos = FALSE;
                        $fileFoto = rellenarCeros($matricula, 8).'.jpg';
                        $fileFirma = rellenarCeros($matricula, 8).'.bmp';
                        $foto = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Fotos/".$fileFoto, "rb");
                        if ($foto) {
                            $firma = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Firmas/".$fileFirma, "rb");
                            if ($firma) {
                                $hayArchivos = TRUE;
                            }
                        } 
                        ?>
                            <?php
                            $tieneFotoFirma = FALSE;
                            if (isset($_GET['err'])) {
                            ?>
                                <div class="col-md-12">
                                    <div class="alert alert-danger ocultarMensaje">No se encontraron los archivos para asociar, verifique si los nombres son correctos.</div>
                                </div>
                            <?php
                            } else {
                                ?>
                                <div class="col-md-6">
                                    <?php
                                    //verifica que tenga foto y firma para mostrar
                                    $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '1');
                                    if ($resArchivos['estado'] && isset($resArchivos['datos'])){
                                        $archivos = $resArchivos['datos'];
                                        $fileFoto = trim($archivos['nombre']);
                                        // insertamos la foto y firma
                                        $foto = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Fotos/".$fileFoto, "rb");
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
                                <div class="col-md-6">
                                <?php
                                $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '2');
                                if ($resArchivos['estado'] && isset($resArchivos['datos'])){
                                    $archivos = $resArchivos['datos'];
                                    $fileFirma = trim($archivos['nombre']);
                                    $firma = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Firmas/".$fileFirma, "rb");
                                    if ($firma) {
                                        $contents=stream_get_contents($firma);
                                        $firmaVer = base64_encode($contents);
                                        $tieneFotoFirma = TRUE;
                                        ?>
                                        <img class="img img-thumbnail" src="data:image/jpg;base64,<?php echo $firmaVer; ?>" height="80" width="200" />
                                        <br>Firma
        <!--                            <form  method="POST" action="colegiado_credencial.php">
                                        <button type="submit" class="btn btn-info" name='volver' id='name'>Imprimir Credencial </button>
                                    </form>-->
                                <?php
                                    }
                                }
                                ?>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="col-md-12">
                            <?php
                            if (!$tieneFotoFirma && $colegiado['tipoEstado'] <> 'F') {
                            ?>
                                <div class="col-md-6">
                                    <h4>Debe asociar la foto y la firma a la nueva matrícula.</h4>
                                </div>
                                <div class="col-md-6">
                                    <form  method="POST" action="datosColegiado/asociar_foto_firma.php<?php echo $tipoIngreso; ?>">
                                        <button type="submit" class="btn btn-lg btn-info" name='asociar' id='asociar' onclick="show('siguiente')">Asociar imágenes </button>
                                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                                        <input type="hidden" name="matricula" id="matricula" value="<?php echo $matricula; ?>" />
                                        <input type="hidden" name="accion" id="accion" value="alta" />
                                    </form>
                                </div>
                            <?php
                            } else {
                                $display = 'block';
                            }
                            ?>
                        </div>
                        <?php
                        if ($tipoIngreso == "?tipo=otro") { 
                        ?>
                <form id="siguiente" autocomplete="off" name="siguiente" style="display: <?php echo $display; ?>;" method="POST" onSubmit="" action="colegiado_nuevo_paso3.php?idColegiado=".$idColegiado.$tipoIngreso">
                    <div class="row">&nbsp;</div>
                        <h4><b>Debe ingresar los datos del movimiento matricular</b></h4>
                        <?php
                        if (isset($_POST['tipoMovimiento']) && $_POST['tipoMovimiento']) {
                            $tipoMovimiento = $_POST['tipoMovimiento'];
                        } else {
                            $tipoMovimiento = NULL;
                        }
                        if (isset($_POST['distritoOrigen']) && $_POST['distritoOrigen']) {
                            $distritoOrigen = $_POST['distritoOrigen'];
                        } else {
                            $distritoOrigen = NULL;
                        }
                        if (isset($_POST['fechaOtroDistrito']) && $_POST['fechaOtroDistrito']) {
                            $fechaOtroDistrito = $_POST['fechaOtroDistrito'];
                        } else {
                            $fechaOtroDistrito = NULL;
                        }
                        
                        ?>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Tipo de ingreso *</label>
                                <select class="form-control" id="tipoMovimiento" name="tipoMovimiento" required="">
                                    <option value="" selected>Seleccione Tipo de Movimiento</option>
                                    <option value="5" <?php if($tipoMovimiento == '5') { ?> selected <?php } ?>>Ingreso definitivo al Distrito I</option>
                                    <option value="8" <?php if($tipoMovimiento == '8') { ?> selected <?php } ?>>Inscripto al Distrito I</option>
                                    <option value="10" <?php if($tipoMovimiento == '10') { ?> selected <?php } ?>>Colegiado del Distrito I, inscripto al Distrito</option>
                                </select>
                                <!--<div class="radio-inline">
                                    <input type="radio" name="tipoMovimiento" value="5" <?php if($tipoMovimiento == '5') { ?> selected <?php } ?>>Ingreso definitivo al Distrito I
                                </div>
                                <div class="radio-inline">
                                    <input type="radio" name="tipoMovimiento" value="8" <?php if($tipoMovimiento == '8') { ?> selected <?php } ?>>Inscripto al Distrito I
                                </div>
                                <div class="radio-inline">
                                    <input type="radio" name="tipoMovimiento" value="10" <?php if($tipoMovimiento == '10') { ?> selected <?php } ?>>Colegiado del Distrito I, inscripto al Distrito
                                </div>-->
                            </div>
                            <div class="col-md-2">
                                <label>Distrito de Cambio *</label>
                                <select class="form-control" id="distritoOrigen" name="distritoOrigen" required="">
                                    <option value="" selected>Seleccione Distrito</option>
                                    <option value="2" <?php if($distritoOrigen == '2') { ?> selected <?php } ?>>Distrito 2</option>
                                    <option value="3" <?php if($distritoOrigen == '3') { ?> selected <?php } ?>>Distrito 3</option>
                                    <option value="4" <?php if($distritoOrigen == '4') { ?> selected <?php } ?>>Distrito 4</option>
                                    <option value="5" <?php if($distritoOrigen == '5') { ?> selected <?php } ?>>Distrito 5</option>
                                    <option value="6" <?php if($distritoOrigen == '6') { ?> selected <?php } ?>>Distrito 6</option>
                                    <option value="7" <?php if($distritoOrigen == '7') { ?> selected <?php } ?>>Distrito 7</option>
                                    <option value="8" <?php if($distritoOrigen == '8') { ?> selected <?php } ?>>Distrito 8</option>
                                    <option value="9" <?php if($distritoOrigen == '9') { ?> selected <?php } ?>>Distrito 9</option>
                                    <option value="10" <?php if($distritoOrigen == '10') { ?> selected <?php } ?>>Distrito 10</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Fecha de Matriculación de origen *</label>
                                <input class="form-control" type="date" name="fechaOtroDistrito" value="<?php echo $fechaOtroDistrito; ?>" required=""/>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                    <?php 
                    } else {
                        if ($tipoIngreso == "?tipo=baja") {
                            //debo cargar todos los movimientos matriculares
                        }
                    }
                    ?>
                    <div class="col-md-12 text-right">
                        <button type="submit"  class="btn <?php echo $botonConfirma; ?> btn-lg" >Siguiente </button>
                    </div>
                </form>
            </div>
        </div>
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
	 obj.style.display = (obj.style.display=='none') ? 'block' : 'none';
}

</script>