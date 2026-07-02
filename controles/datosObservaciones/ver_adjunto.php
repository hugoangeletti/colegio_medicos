<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoObservacionLogic.php');
$colegiadoObservacionLogic = new colegiadoObservacionLogic();


?>
<h1>Adjunto</h1>
<?php
$idAdjunto = $_GET['id']; 
if (isset($_GET['pdf'])) {
    $fileFoto = $_GET['pdf'];
    $mi_pdf = fopen ("ftp://webcolmed:web.2017@192.168.2.50:21".$fileFoto, "r");
    if (!$mi_pdf) {
        echo "<p>No puedo abrir el archivo para lectura</p>";
        exit;
    }

    header('Content-type: application/pdf');

    fpassthru($mi_pdf);  
    fclose ($fileFoto);
} else {
    $resAdjunto = $colegiadoObservacionLogic->obtenerAdjunto($idAdjunto);
    if ($resAdjunto['estado']){
        $adjunto = $resAdjunto['datos'];
        $datosImagen = $adjunto['pathArchivo'].'/'.$adjunto['nombreArchivo'];
        $imagen = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21".$datosImagen, "rb");
        if ($imagen) {
            $contents=stream_get_contents($imagen);
            fclose ($imagen);

            $imagenVer = base64_encode($contents);
            ?>
            <img class="img img-thumbnail" height="800" src="data:<?php echo $adjunto['tipoArchivo'] ?>;base64,<?php echo $imagenVer; ?>" />
            <?php
        } else {
            echo "hubo error al conectarse";
        }
    } else {
        echo "NO ENCONTRADA<br>";
    }
}          
