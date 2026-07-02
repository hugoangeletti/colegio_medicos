<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/envios_caja_medicosLogic.php';

$continua = true;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idEnviosCajaMedicos = $_GET['id'];
    $envioLogic = new enviosCajaMedicosLogic();
    $resEnvio = $envioLogic->obtenerEnvioPorId($idEnviosCajaMedicos);
    if ($resEnvio['estado']) {
        $envio = $resEnvio['datos'];
        $fechaDesde = $envio['fechaDesde'];
        $fechaHasta = $envio['fechaHasta'];
        $mail = $envio['mail'];
        $path = $envio['path'];
        $nombrePdf = $envio['nombrePdf'];
    } else {
        $continua = FALSE;
        $mensaje .= $resEnvio['mensaje'];
        $clase = $resEnvio['clase'];
    }
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-11">
                    <h4>Imprimir Envio</h4>
                </div>
                <div class="col-md-1 text-right">
                    <a href="envios_caja_medicos.php" class="btn btn-info">Volver</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            if ($continua) {
            ?>
                <div class="row">
                    <div class="col-md-1">
                        Fecha desde: <?php echo cambiarFechaFormatoParaMostrar($fechaDesde); ?>
                    </div>
                    <div class="col-md-1">
                        Fecha hasta: <?php echo cambiarFechaFormatoParaMostrar($fechaHasta); ?>
                    </div>
                    <div class="col-md-4">
                        Archivo: <?php 
                                if (isset($nombrePdf)) {
                                    echo $path.$nombrePdf; 
                                } else {
                                    echo '<br>SIN GENERAR.</br>';
                                }
                                ?>
                    </div>
                    <div class="col-md-4">
                        <form id="formEnvio" name="formEnvio" method="POST" onSubmit="" action="envios_caja_medicos_mail.php?id=<?php echo $idEnviosCajaMedicos; ?>">
                            <div class="col-md-10">
                                Mail registrado:
                                <input class="form-control" type="text" name="mail" id="mail" value="<?php echo $mail; ?>" />
                            </div>
                            <div class="col-md-2">
                                <br>
                                <button type="submit"  class="btn btn-default" >Enviar mail </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
                $listadoPDF = NULL;
                if (!isset($nombrePdf)) {
                    //como no existe se debe generar el pdf
                    $generar = TRUE;
                    include_once ('datosTramites/generar_pdf_altas.php');
                }
                $camino = $_SERVER['DOCUMENT_ROOT'].'/'.PATH_PDF.$path;
                $nombreArchivo = $camino.$nombrePdf;
                //echo 'camino->'.$camino.'<br>';
                //echo 'nombrePdf->'.$nombrePdf.'<br>';
                if (isset($nombrePdf) && file_exists($nombreArchivo)) {
                    //obtiene el certificado y lo guarda como base64 para mostrar
                    $pdf_content = file_get_contents($nombreArchivo);        
                    $listadoPDF = base64_encode($pdf_content);                
                    if (isset($listadoPDF)) {
                    ?>
                        <div class="row">
                           <embed src='data:application/pdf;base64,<?php echo $listadoPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                        </div> 
                    <?php
                    } else {
                        echo 'ERROR AL OBTENER EL LISTADO PDF';    
                    }
                } else {
                ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="alert alert-danger">NO EXISTE EL LISTADO</h4>
                        </div>
                    </div>
                <?php
                }
            } else {
                echo $mensaje;
            }
            ?>
        </div>
    </div>
<?php            
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
    </div>
    <a href="envios_caja_medicos.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");

