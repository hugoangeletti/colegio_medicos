<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');

$continua = TRUE;
$mensaje = "";
$fapLogic = new fapLogic();
if (isset($_POST['idSapCaratulaArchivo']) || isset($_GET['id'])) {
    if (isset($_POST['idSapCaratulaArchivo'])) {
        $idSapCaratulaArchivo = $_POST['idSapCaratulaArchivo'];
        $resCaratulaArchivo = $fapLogic->obtenerCaratulaArchivoPorId($idSapCaratulaArchivo);
        if ($resCaratulaArchivo['estado']) {
            $caratulaArchivo = $resCaratulaArchivo['datos'];
            $idSapCaratula = $caratulaArchivo['idSapCaratula'];
        } else {
            $continua = FALSE;
            $mensaje .= $resCaratulaArchivo['mensaje'];
        }
    } else {
        $idSapCaratula = $_GET['id'];
        $idSapCaratulaArchivo = NULL;
    }

    if ($continua) {
        //obtengo los datos de la caratula
        $resRegistro = $fapLogic->obtenerSapCaratulaPorId($idSapCaratula);
        if ($resRegistro['estado']) {
            $registro = $resRegistro['datos'];
            $idColegiado = $registro['idColegiado'];
            $nombreCausa = $registro['nombreCausa'];

            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado']) {
                $colegiado = $resColegiado['datos'];
                $matricula = $colegiado['matricula'];
                $apellido = $colegiado['apellido'];
                $nombre = $colegiado['nombre'];
            } else {
                $continua = FALSE;
                $mensaje .= $resColegiado['mensaje'];
            }
        } else {
            $continua = FALSE;
            $mensaje .= $resRegistro['mensaje'];
        }

        if (isset($_POST['accion']) && $_POST['accion'] <> "") {
            $accion = $_POST['accion'];
        } else {
            $accion = NULL;
        }
    }

    if ($continua) {
        if (isset($accion) && $accion <> "") {
            //si ingreso para eliminar el archivo adjunto,
            if ($accion == 'borrar') {
                //eliminar el adjunto
                $resultado = $fapLogic->eliminarCaratulaArchivo($idSapCaratulaArchivo);
                if ($resultado['estado']) {
                    $archivoAdjunto = $resultado['datos'];
                    $nombreArchivo = $archivoAdjunto['nombreArchivo'];
                    $path = '../archivos/fap/'.$idSapCaratula;
                    unlink($path.'/'.$nombreArchivo);
                } else {
                    $mensaje .= "ERROR AL ELIMINAR EL ADJUNTO";
                    $continuaArc = FALSE;
                }
            } else {
                if ($accion == 'adjuntar') {
                    //Agrega adjunto
                    $continuaArc = TRUE;
                    if($_FILES['archivoAdjuntar']['name'] != "") {
                        $fileName = explode(".", $_FILES['archivoAdjuntar']['name']);

                        $nombreAdjunto = $fileName[0];
                        $extensionAdjunto = $fileName[1];
                        $tipoArchivo = $_FILES['archivoAdjuntar']['type'];
                        $tamanio = $_FILES['archivoAdjuntar']['size'];
                        $path = '../archivos/fap/'.$idSapCaratula;

                        $continuaArc = TRUE;
                        $mensaje = "";
                        if ($tamanio > 2000000) {
                            $mensaje .= "DEBE ADJUNTAR UN ARCHIVO CON TAMAÑO MAXIMO 2mb - ";
                            $continuaArc = FALSE;
                        } else {
                            $a_Types = array("jpg", "jpeg", "gif", "png", "pdf", "JPG", "GIF", "PNG", "PDF");
                            
                            if ((($_FILES["archivoAdjuntar"]["type"] == "image/gif")
                                    || ($_FILES["archivoAdjuntar"]["type"] == "image/jpeg")
                                    || ($_FILES["archivoAdjuntar"]["type"] == "image/png")
                                    || ($_FILES["archivoAdjuntar"]["type"] == "image/pjpeg")
                                    || ($_FILES["archivoAdjuntar"]["type"] == "application/pdf")
                                ) && in_array($extensionAdjunto, $a_Types)) { 

                                $nombreAdjunto = $idSapCaratula.'_'.date('Ymd').'_'.date('His').'.'.$extensionAdjunto;
                                $resultado = $fapLogic->agregarCaratulaArchivo($idSapCaratula, $path, $nombreAdjunto, $extensionAdjunto, $tipoArchivo);
                                if ($resultado['estado']) {
                                    //guardo el archivo en el path
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    move_uploaded_file($_FILES['archivoAdjuntar']['tmp_name'], $path.'/'.$nombreAdjunto);
                                } else {
                                    $mensaje .= "ERROR AL ADJUNTAR EL ARCHIVO - ";
                                    $continuaArc = FALSE;
                                }
                            } else {
                                $mensaje .= 'ERROR AL ADJUNTAR EL TIPO DE ARCHIVO, DEBE SER DEL TIPO "JPG", "GIF", "PNG", "PDF" - ';
                                $continuaArc = FALSE;
                            }
                        }
                    } else {
                        $mensaje .= 'ERROR AL ADJUNTAR ARCHIVO, DEBE SELECCIONAR UN ARCHIVO - ';
                        $continuaArc = FALSE;
                    }
                    if (!$continuaArc) {
                    ?>
                        <div class="ocultarMensaje">
                            <div class="alert alert-danger" role="alert">
                                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                <span><?php echo$mensaje; ?></span>
                            </div>
                        </div>
                    <?php 
                    }
                }

            }
        }
    }
} else {
    $continua = FALSE;
    $mensaje .= "Ingreso incorrecto - Falta idSapCaratula - ";
}
if ($continua) {
?>
    <div class="row">
        <div class="col-md-12">
            <h3>Archivos adjuntos al expediente FAP</h3>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-8">
            <h4>Carátula: <b><?php echo $nombreCausa; ?></b></h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <label for="apellido">Apellido y Nombre </label>
            <input class="form-control" type="text" name="apellido" id="apellido" value="<?php echo trim($apellido).' '.trim($nombre); ?>" readonly="" />
        </div>
        <div class="col-md-2">
            <label for="matricula">Matrícula </label>
            <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly=""/>
        </div>
        <div class="col-md-1">
            &nbsp;
        </div>
        <div class="col-md-5">
            <label for="button_adjuntar">Adjuntar archivo</label>
            <p>
                <form id="adjuntar" name="adjuntar" enctype="multipart/form-data" method="POST" action="fap_adjunto_form.php?id=<?php echo $idSapCaratula; ?>">
                    <div class="col-md-7">
                        <input type="file" id="archivoAdjuntar" name="archivoAdjuntar">
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary" id="button_adjuntar">Adjuntar</button>
                        <input type="hidden" name="accion" id="accion" value="adjuntar" />
                    </div>
                </form>
            </p>
        </div>
        <div class="col-md-1">
            <br>
            <a href="fap_listado.php" class="btn btn-default" title="">Volver</a>
        </div>
    </div>
    <div class="row"><div class="col-md-12"><hr style="border: 0; height: 2px; background: #333; "></div></div>

    <?php
    $resAdjuntos = $fapLogic->obtenerCaratulaAdjuntosPorIdCaratula($idSapCaratula);
    if ($resAdjuntos['estado']) {
        $cantidadAdjuntos = sizeof($resAdjuntos['datos']);
        if ($cantidadAdjuntos > 0) {
        ?>
            <div class="row">
                <div class="col-md-2">
                    <h5>Archivos adjuntos: <?php echo $cantidadAdjuntos; ?></h5>
                </div>
                <div class="col-md-2">
                    <form id="generaPdf" name="generaPdf" method="POST" action="fap_genera_pdf.php?id=<?php echo $idSapCaratula; ?>" target="_BLANK">
                        <button type="submit" class="btn btn-primary">Descargar Carpeta</button>
                    </form>
                </div>
            </div>
            <div class="row">&nbsp;</div>
        <?php
        }
        $i = 0;
        $saltarLinea = FALSE;
        ?>
        <div class="row">
        <?php
        foreach ($resAdjuntos['datos'] as $fila) {
            $idSapCaratulaArchivo = $fila['idSapCaratulaArchivo'];
            $path = $fila['path'];
            $nombreArchivo = $fila['nombreArchivo'];
            $extensionAdjunto = $fila['extensionAdjunto'];
            if (isset($path) && isset($nombreArchivo) && isset($extensionAdjunto)) {
                $i += 1;
                ?>
                <div class="col-md-3">
                    <h5>Archivo <?php echo '#'.rellenarCeros($i, 4); ?></h5>
                    <?php
                    if (strtoupper($extensionAdjunto) != 'PDF') {
                    ?>
                        <img class="img-thumbnail" src="<?php echo $path.'/'.$nombreArchivo; ?>" alt="Archivo adjunto">
                    <?php
                    } else {
                    ?>
                        <embed src="<?php echo $path.'/'.$nombreArchivo; ?>" type="application/pdf" width="100%" />
                    <?php
                    }
                    ?>
                    <div class="col-md-6 text-center">
                        <form id="eliminiarAdjunto" name="eliminiarAdjunto" method="POST" action="fap_adjunto_form.php?id=<?php echo $idSapCaratula; ?>" onclick="return confirmar()">
                            <button type="submit" class="btn btn-danger"> Eliminar </button>
                            <input type="hidden" name="accion" id="accion" value="borrar" />
                            <input type="hidden" name="idSapCaratulaArchivo" id="idSapCaratulaArchivo" value="<?php echo $idSapCaratulaArchivo; ?>" />
                        </form>
                    </div>
                    <div class="row">
                        <div class="col-md-6 text-center">
                            <a href="fap_mostrar_adjunto.php?id=<?php echo $idSapCaratulaArchivo ?>" class="btn btn-info">Ver adjunto</a>
                            <!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#archivoAdjunto_<?php echo $i; ?>Modal">Ver adjunto</button>
                            <div id="#archivoAdjunto_<?php echo $i; ?>Modal" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header alert alert-info">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Archivo adjunto</h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php 
                                            if (strtoupper($extensionAdjunto) == "PDF") {
                                            ?>
                                                <embed src="<?php echo $path.'/'.$nombreArchivo; ?>" type="application/pdf" width="100%" height="600px" />
                                            <?php
                                            } else {
                                            ?>
                                                <img src="<?php echo $path.'/'.$nombreArchivo; ?>" alt="Archivo adjunto">
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>   -->     
                        </div>
                    </div>
                </div>
                <?php
                if($i%4 == 0) {
                ?>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                <?php
                }
            }
        }
        ?>
        </div>
        <div class="row">&nbsp;</div>
    <?php
    } else {
    ?>
        <div class="alert alert-danger" role="alert">
            <h3>Hubo un error en la carga de adjuntos (<?php echo $resAdjuntos['mensaje']; ?>)</h3>
        </div>
    <?php
    }
    ?>
    <div class="row">&nbsp;</div>
<?php
} else {
?>
    <div class="alert alert-danger" role="alert">
        <h3><?php echo $mensaje; ?></h3>
    </div>
<?PHP    
}
?>

<?php
require_once '../html/footer.php';
?>

<script type="text/javascript">
    $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    function confirmar() {
    if(confirm('¿Estas seguro de eliminar este adjunto?'))
        return true;
    else
        return false;
    }

    $(document).ready(function()
    {
        $("#myModal").modal("show");
    });
</script>