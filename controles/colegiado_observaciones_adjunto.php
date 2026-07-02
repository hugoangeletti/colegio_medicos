<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoObservacionLogic.php');
$colegiadoObservacionLogic = new colegiadoObservacionLogic();
?>
<script>
$(document).ready(
    function () {
        $('#tablaObservaciones').DataTable({
            "iDisplayLength":7,
             "order": [[ 0, "desc" ]],
             "bLengthChange": false,
            "bFilter": false,
            "language": {
                "url": "../public/lang/esp.lang"
            }
        });
    }
    
);

function confirmar()
{
	if(confirm('¿Estas seguro de elimiar el adjunto?'))
		return true;
	else
		return false;
}

</script>
<?php
if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
    }
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idColegiadoObservacion = $_GET['id'];
    } else {
        $idColegiadoObservacion = NULL;
    }
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Observaciones</h4>
                </div>
                <div class="col-md-3 text-left">
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_observaciones.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-info" >Volver a Observaciones del colegiado</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
                </div>
                <div class="col-md-4">
                    <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                </div>
                <div class="col-md-6 text-right">
                </div>
            </div>
            <?php
            if (isset($_POST['mensaje'])) {
            ?>
               <div class="ocultarMensaje"> 
                   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
               </div>
            <?php
            }
            //busco observaciones
            $resObservaciones = $colegiadoObservacionLogic->obtenerColegiadoObservacionPorId($idColegiadoObservacion);
            if ($resObservaciones['estado']){
                $observacion = $resObservaciones['datos'];
                $observaciones = $observacion['observaciones'];
                //if (count($resObservaciones['datos']) > 0){
            ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Observaciones </label>
                            <textarea class="form-control" name="observaciones" id="observaciones" rows="5" readonly=""><?php echo $observaciones; ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <form name="MiForm" id="MiForm" method="post" action="datosObservaciones/cargar_adjunto.php?idColegiado=<?php echo $idColegiado;?>&id=<?php echo $idColegiadoObservacion; ?>" enctype="multipart/form-data">
                                <h4>Seleccione imagen a cargar</h4>
                                <div class="form-group">
                                  <div class="col-sm-8">
                                    <input type="file" class="form-control" id="imagen" name="imagen" multiple>
                                  </div>
                                  <button name="submit" class="btn btn-primary">Cargar Imagen</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="col-md-12">                    
                        <h4>Imágenes cargadas</h4>
                <?php
                    $resAdjuntos = $colegiadoObservacionLogic->obtenerAdjuntoPorObservacion($idColegiadoObservacion);
                    if ($resAdjuntos['estado']) {
                        foreach ($resAdjuntos['datos'] as $value) {
                            $idColegiadoObservacionAdjunto = $value['id'];
                            $fileFoto = $value['pathArchivo'].'/'.$value['nombreArchivo'];
                            
                        ?>
                            <div class="col-md-2">
                                <?php
                                $foto = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21".$fileFoto, "rb");
                                if ($foto) {
                                    $contents=stream_get_contents($foto);
                                    fclose ($foto);

                                    $fotoVer = base64_encode($contents);
                                    if ($value['tipoArchivo'] == 'application/pdf') {
                                    ?>
                                        <a href="datosObservaciones/ver_adjunto.php?id=<?php echo $idColegiadoObservacionAdjunto; ?>&pdf=<?php echo $fileFoto; ?>" target="_blank" onclick="window.open(this.href, this.target, 'width=900,height=600'); return false;">
                                            <img width="150" height="150" src="../public/images/pdf_Imagen.png" />
                                        </a>
                                    <?php 
                                    } else {
                                    ?>
                                        <a href="datosObservaciones/ver_adjunto.php?id=<?php echo $idColegiadoObservacionAdjunto; ?>" target="_blank" onclick="window.open(this.href, this.target, 'width=900,height=600'); return false;">
                                            <img class="img" width="150" height="150" src="data:<?php echo $value['tipoArchivo'] ?>;base64,<?php echo $fotoVer; ?>" />
                                        </a>
                                    <?php 
                                    }
                                    ?>
                                    <br>
                                    <a href="datosObservaciones/eliminar_adjunto.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoObservacion; ?>&idAdjunto=<?php echo $idColegiadoObservacionAdjunto; ?>" class="btn btn-xs btn-danger glyphicon glyphicon-erase" title="Eliminar Adjunto" onclick="return confirmar()"></a>
                                <?php
                                } else {
                                    echo "hubo error al conectarse";
                                }
                                ?>
                            </div>
                        <?php
                        }
                    } else {
                    ?>
                        <h3><?php echo $resAdjuntos['mensaje']; ?></h3>
                    <?php
                    }
                    ?>
                    </div>
            <?php
            } else {
            ?>
                <div class="<?php echo $resObservaciones['clase']; ?>" role="alert">
                    <span class="<?php echo $resObservaciones['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resObservaciones['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
            ?>
        </div>
    </div>
<?php
}
?>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';

