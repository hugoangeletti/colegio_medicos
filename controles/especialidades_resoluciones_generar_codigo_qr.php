<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/resolucionesLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();

include "../phpqrcode/qrlib.php";    

// Desactivar toda notificación de error
error_reporting(0);
 
// Notificar solamente errores de ejecución
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ERROR);

//ofcourse we need rights to create temp dir
$estructura = "../archivos/titulos_qr/";
if (!file_exists($estructura)) {
    mkdir($estructura, 0777, true);
}

$idResolucionDetalle = $_GET['id'];
$idResolucion = $_GET['idResolucion'];
$matricula = $_GET['matricula'];
$idColegiadoEspecialista = $_GET['idColegiadoEspecialista'];
$creado = date('YmdHis');

$hash_qr = hashData($idColegiadoEspecialista.'_'.$matricula.'_'.$creado);

$dataCodigo = 'https://www.colmed1.com.ar/verificar/titulo_especialista.php?id='.$hash_qr;

$matrixPointSize = 10;
$errorCorrectionLevel = 'H';

$filename = $estructura.$idColegiadoEspecialista.'_'.$matricula.'.jpg';
$nombreArchivo = $idColegiadoEspecialista.'_'.$matricula.'.jpg';

if (file_exists($estructura.$filename)) {
    unlink($estructura.$filename);
} 

QRcode::png($dataCodigo, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 

$imagen = imagecreatefrompng($filename);
$filename=str_replace(".png", ".jpg", $filename);
imagejpeg($imagen,$filename,100);

$continua = TRUE;

$cid = ftp_connect("192.168.2.50");
$resultado = ftp_login($cid, "webcolmed","web.2017");
if ((!$cid) || (!$resultado)) {
    $continua = FALSE;
    $mensaje = "Fallo en la conexión";
}

$pathMatricula = "/Legajos/".$matricula;
$pathArchivo = $pathMatricula."/Titulos_Especialistas_QR";

if (!ftp_chdir($cid, $pathMatricula)) {
    if (!ftp_mkdir($cid, $pathMatricula)) {
        $continua = FALSE;
        $mensaje = "Ha habido un problema durante la creación de $pathMatricula\n";
    }
}

if ($continua) {
    if (!ftp_chdir($cid, $pathArchivo)) {
        if (!ftp_mkdir($cid, $pathArchivo)) {
            $continua = FALSE;
            $mensaje = "Ha habido un problema durante la creación de $pathArchivo\n";
        }
    }
}

if ($continua) {
    if (($cid) && ($resultado)) {
        ftp_pasv($cid, true);            
        ftp_chdir($cid, $pathArchivo);

        $upload = ftp_put($cid, $nombreArchivo, $filename, FTP_BINARY);
        if (!$upload && $colegiadoEspecialistaLogic->noExisteCodigoQR($idColegiadoEspecialista)) {
            $mensaje = "Ha ocurrido un error al subir el archivo";
            $archivoConPath = $pathArchivo.'/'.$nombreArchivo;
            $mensaje = "$pathArchivo/$nombreArchivo ya existe\n";
        } else {
            $mensaje = 'Codigo QR generado con exito!';
        }

        $resultado = $colegiadoEspecialistaLogic->guardarQrColegiadoEspecialista($idColegiadoEspecialista, $hash_qr, $pathArchivo, $nombreArchivo);
        if ($resultado['estado']) {
            $codigo_qr = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21".$pathArchivo.'/'.$nombreArchivo, "rb");
            if ($codigo_qr) {
                $contents = stream_get_contents($codigo_qr);
                //fclose ($codigo_qr);

                $fotoVer = base64_encode($contents);
                ?>
            <?php
            } else {
                $continua = FALSE;
                $mensaje = "Ha ocurrido un error al recuperar codigo QR";
            }
        } else {
            $continua = FALSE;
            $mensaje = "Ha ocurrido un error al guardar codigo QR";
        }
        ftp_close($cid);
    } else {
        $continua = FALSE;
        $mensaje = "Ha ocurrido un error en el login";
    }
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9 text-left">
                <h4>Generar codigo QR para Título de Especialistas</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="especialidades_resoluciones_matriculas.php?idResolucion=<?php echo $idResolucion; ?>">
                        <button type="submit"  class="btn btn-info" >Volver a la resolución</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2">&nbsp;</div>
            <div class="col-md-6"><h4>Matrícula: &nbsp;<?php echo $matricula; ?></h4></div>
        </div>
        <div class="row">
            <div class="col-md-2">&nbsp;</div>
            <div class="col-md-6">
                <?php 
                if ($continua) {
                ?>
                    <div class="row alert alert-success"><h4><?php echo $mensaje; ?></h4></div>
                    <img class="img img-thumbnail" width="150" src="data:image/jpg;base64,<?php echo $fotoVer; ?>" />
                <?php 
                } else {
                ?>
                    <div class="row alert alert-danger"><h4><?php echo $mensaje; ?></h4></div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
