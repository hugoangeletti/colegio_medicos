<?php
require_once ('../../dataAccess/config.php');
//permisoLogueado();
//require_once ('../html/head.php');
//require_once ('../html/header.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/informeContableLogic.php');

$continua = TRUE;
if (isset($_GET['id'])) {
    $idInforme = $_GET['id'];
    $informeContableLogic = new informeContableLogic();
    $resInformes = $informeContableLogic->obtenerInformeContablePorId($idInforme);
    if ($resInformes['estado']) {
        $informe = $resInformes['datos'];
        $sub_path = $informe['path'];
        $origen = $informe['origen'];
        $mesProcesado = $informe['mes'];
        $path = '../../archivos/'.$sub_path.'/'.$origen;
        //echo $path.'<br>';

        //inicia
        $dir = $path;
        $zip = new ZipArchive();
        $filename = $mesProcesado.'_'.$origen.'.zip'; 
        if ($zip->open($path.'/'.$filename,ZIPARCHIVE::CREATE) === true) {
            //leer el directorio y cargar al zip todos los archivos
            if (is_dir($dir)) {
                if ($files = opendir($dir)) {
                    // recorro los archivo buscandolo los del tipo del array
                    while (($file = readdir($files)) !== false) {
                        //print_r($file);
                        //echo '<br>';
                        //if ($file == '.' || $file == '..' || is_dir($dir."/". $file)) continue;
                        if ($file == '.' || $file == '..') continue;

                        $file_info = pathinfo($dir."/". $file);                
                        $extension = strtolower($file_info['extension']);
                        if ($extension <> 'txt') continue;
                        //print_r($file_info);
                        //echo '<br>';
                        //echo 'agrega->'.$dir."/". $file.'<br>';
                        $zip->addFile($dir."/". $file, $file);
                    }
                    //echo "numficheros: " . $zip->numFiles . "\n";
                    //echo "estado:" . $zip->status . "\n";                
                    $zip->close();
                    //echo 'Creado '.$filename;
                    //exit;

                    // Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
                    header("Content-type: application/Zip");
                    header("Content-disposition: attachment; filename=".$filename);
                    $size = filesize($dir.'/'.$filename);
                    header("Content-Length:".$size);
                    // leemos el archivo creado
                    readfile($dir.'/'.$filename);                
                    unlink($dir.'/'.$filename);//Destruye el archivo temporal
                    exit;

                } else {
            ?>
                <div class="row">
                    <div class="col-md-12" >
                        <div class="alert alert-danger" role="alert">
                            <?php
                            echo 'Error creando '.$filename.'<br>';
                            ?>
                            <a href="informe_contable_lista.php" class="btn btn-primary">Volver</a>
                       </div> 
                    </div>
                </div>
            <?php
                }
            } else {
            ?>
                <div class="row">
                    <div class="col-md-12" >
                        <div class="alert alert-danger" role="alert">
                            <?php
                            echo 'Error del directorio '.$dir.'<br>';
                            ?>
                            <a href="informe_contable_lista.php" class="btn btn-primary">Volver</a>
                       </div> 
                    </div>
                </div>
            <?php
            }
        } else {
        ?>
            <div class="row">
                <div class="col-md-12" >
                    <div class="alert alert-danger" role="alert">
                        <?php
                        echo 'Error al generar '.$filename.'<br>';
                        ?>
                        <a href="informe_contable_lista.php" class="btn btn-primary">Volver</a>
                   </div> 
                </div>
            </div>
        <?php
        }
    } else {
    ?>
        <div class="row">
            <div class="col-md-12 alert alert-danger" role="alert">
                <h4>ERROR: <?php echo $resInformes['mensaje']; ?></h4>
                <a href="informe_contable_lista.php" class="btn btn-primary">Volver</a>
            </div>
        </div>
    <?php
    }
} else {
?>
    <div class="row">
        <div class="col-md-12" >
            <div class="alert alert-danger" role="alert">
                ACCESO INCORRECTO<br>
                <a href="informe_contable_lista.php" class="btn btn-primary">Volver</a>
           </div> 
        </div>
    </div>
<?php
}

require_once '../html/footer.php';
