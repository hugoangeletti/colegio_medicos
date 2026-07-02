<?php
include "../phpqrcode/qrlib.php";    
include "../dataAccess/funcionesPhp.php";

//ofcourse we need rights to create temp dir
$estructura = "../archivos/titulos_qr/";
if (!file_exists($estructura)) {
    mkdir($estructura, 0777, true);
}

$idResolucionDetalle = 10438;
$matricula = 112269;
$creado = date('YmdHis');

$hash_qr = hashData($idResolucionDetalle.'_'.$matricula.'_'.$creado);

$dataCodigo = 'https://www.colmed1.com.ar/tramites-web/verificar/titulo_especialista.php?id='.$hash_qr;

$matrixPointSize = 10;
$errorCorrectionLevel = 'H';

$filename = $estructura.$idResolucionDetalle.'_'.$matricula.'.jpg';
$nombreArchivo = $idResolucionDetalle.'_'.$matricula.'_.jpg';

if (file_exists($estructura.$filename)) {
    unlink($estructura.$filename);
} 

QRcode::png($dataCodigo, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 

$imagen = imagecreatefrompng($filename);
$filename=str_replace(".png", ".jpg", $filename);
imagejpeg($imagen,$filename,100);

$cid = ftp_connect("192.168.2.50");
$resultado = ftp_login($cid, "webcolmed","web.2017");

$pathMatricula = "/Legajos/".$matricula;
$pathArchivo = $pathMatricula."/Titulos_Especialistas_QR";

$continua = TRUE;
if (!ftp_chdir($cid, $pathMatricula)) {
    if (!ftp_mkdir($cid, $pathMatricula)) {
        $continua = FALSE;
        $mensaje = "Ha habido un problema durante la creación de $pathMatricula\n";
    }
} 

if ($continua) {
    /*
    if (!ftp_chdir($cid, $pathArchivo)) {
        if (!ftp_mkdir($cid, $pathArchivo)) {
            $continua = FALSE;
            $mensaje = "Ha habido un problema durante la creación de $pathArchivo\n";
        }
    }
    */
    if (($cid) && ($resultado)) {
        ftp_pasv($cid, true);            
        ftp_chdir($cid, $pathMatricula);

        $upload = ftp_put($cid, $nombreArchivo, $filename, FTP_BINARY);
        if (!$upload) {
            $continua = FALSE;
            $mensaje = "Ha ocurrido un error al subir el archivo";
        } else {
            $mensaje = 'OK';
            ftp_close($cid);
        }
    } else {
        $mensaje = "Ha ocurrido un error en el login";
    }
} else {
    $mensaje = "Ha ocurrido un error al crear carpeta de la matricula";
}

if ($mensaje <> 'OK') {
    echo $mensaje;
} else {
    //guardar hash y path del archivo

}
