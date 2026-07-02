<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();
require_once ('../dataAccess/cobranzaLogic.php');
$cobranzaLogic = new cobranzaLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../dataAccess/colegiadoDebitosLogic.php');

require_once ('datosCobranza/procesarLote.php'); //es el que procesa el lote enviado

if (isset($_POST['inicio']) && $_POST['inicio'] == "OK") {
    /*
    23/2/2026 - cambie el path de descarga por borrado del original
    $path_lotes_archivos = "archivos/cobranza/lotes/a_procesar";
    los copi de este path al que deberia estar archivos/lotes/a_procesar
    $cantidadArchivos = 0;
    foreach(glob("../archivos/cobranza/lotes/a_procesar/*") as $archivos_carpeta) {
        if (!is_dir($archivos_carpeta)){
            //echo $archivos_carpeta.'<br>';
            $archivoProcesar = explode('/', $archivos_carpeta);
            //print_r($archivoProcesar);
            //echo '<br>';
            $archivoProcesar = $archivoProcesar[5];

            $archivoProcesado = '../archivos/lotes/a_procesar/'.$archivoProcesar;
            copy($archivos_carpeta, $archivoProcesado);
            $cantidadArchivos++;
        }
    }
    */
// Configurar PHP 5 para reportar absolutamente todos los errores
error_reporting(E_ALL);

// Forzar a PHP a mostrar los errores directamente en la pantalla/navegador
ini_set('display_errors', 1);

// Mostrar también los errores que ocurren durante el inicio/carga de PHP
ini_set('display_startup_errors', 1);

    $lugaresProcesar = array(22, 23, 25, 26, 28, 29, 30);
    $resLugares = $lugarPagoLogic->obtenerLugaresDePago();
    if ($resLugares['estado']) {
        //primero descomprimmo los zips y muevo los archivos que no sean de cobranza
        echo '<b>INICIA PROCESO descomprimir y mover archivos</b><br>';
        $hayArchivos = 0;
        foreach ($resLugares['datos'] as $lugarPago) {
            if (!in_array($lugarPago['id'], $lugaresProcesar)) { continue; }
            $idLugarPago = $lugarPago['id']; 
            echo 'lugarPago->'.$lugarPago['nombre'].'<br>';
            foreach(glob("../archivos/lotes/a_procesar/*") as $archivos_carpeta) {             
                if (!is_dir($archivos_carpeta)){
                    //echo $archivos_carpeta.'<br>';
                    $archivoProcesar = explode('/', $archivos_carpeta);
                    //print_r($archivoProcesar);
                    //echo '<br>';
                    $archivoProcesar = $archivoProcesar[4];
                    $archivo = explode('.', $archivoProcesar);
                    //echo "Cantidad elemento->".sizeof($archivo).' - archivo->'.$archivoProcesar.'<br>';
                    switch (sizeof($archivo)) {
                        case '1':
                            $tipoArchivo = ""; //sin extension
                            $nombreArchivo = $archivo[0];
                            break;
                        
                        case '3':
                            $tipoArchivo = $archivo[2]; //es el html de pago mis cuentas
                            $nombreArchivo = $archivo[0].'.'.$archivo[1];
                            break;
                        
                        case '4':
                            $tipoArchivo = $archivo[3]; //es el html de pago mis cuentas
                            $nombreArchivo = $archivo[0].'.'.$archivo[1].'.'.$archivo[2];
                            break;
                        
                        default:
                            $tipoArchivo = $archivo[1];    
                            $nombreArchivo = $archivo[0];
                            break;
                    }
                    //print_r($tipoArchivo);
                    //echo '<br>';
                    switch ($idLugarPago) {
                        case '22':
                            if ($tipoArchivo == "zip" || $tipoArchivo == "ZIP") {
                                if (substr($archivoProcesar, 0, 16) == "BPColegioMedicos") {
                                    $anio = substr($nombreArchivo, 16, 4); 
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio."/zips";
                                    $pathDescarga = "../archivos/lotes/a_procesar";
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $archivoProcesado = $path . '/' . $archivoProcesar;

                                    $zip = new ZipArchive;
                                    if ($zip->open($archivos_carpeta) === TRUE) {
                                        $path = getcwd(); // Path del directorio actual
                                        $zip->extractTo($pathDescarga); // Extraemos el contenido en el directorio actual
                                        $zip->close();
                                    }
                                    rename($archivos_carpeta, $archivoProcesado);               
                                    echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                                }
                            } else {
                                if (substr($archivoProcesar, 0, 11) == "Retribucion") {
                                    //es el archivo retirbucion.txt, lo envio a la carpeta Retribucion, 
                                    $anio = substr($nombreArchivo, 12, 4);
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio."/retribucion";
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $archivoProcesado = $path . '/' . $archivoProcesar;
                                    rename($archivos_carpeta, $archivoProcesado);                
                                    echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                                }
                            }
                            break;
                        
                        case '25':
                            if ($tipoArchivo == "zip" || $tipoArchivo == "ZIP") {
                                //179g1611
                                if (substr($archivoProcesar, 0, 4) == "179g" || substr($archivoProcesar, 0, 4) == "179G") {
                                    $anio = date('Y'); 
                                    $mes = rellenarCeros(date('m'), 2);
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio."/zips/".$mes;
                                    $pathDescarga = "../archivos/lotes/a_procesar";
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $archivoProcesado = $path . '/' . $archivoProcesar;

                                    $zip = new ZipArchive;
                                    if ($zip->open($archivos_carpeta) === TRUE) {
                                        $path = getcwd(); // Path del directorio actual
                                        $zip->extractTo($pathDescarga); // Extraemos el contenido en el directorio actual
                                        $zip->close();
                                    }
                                    rename($archivos_carpeta, $archivoProcesado);               
                                    echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                                }
                            }
                            break;

                        case '26':
                            if ($tipoArchivo == "li2" || $tipoArchivo == "LI2" || $tipoArchivo == "Li2") {
                                //es el archivo de link con el resumen, lo envio a la carpeta resumen, 
                                $idLugarPago = 26;
                                $anio = date('Y');
                                $path = "../archivos/lotes/".$idLugarPago."/".$anio."/resumen";
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                $archivoProcesado = $path . '/' . $archivoProcesar;
                                rename($archivos_carpeta, $archivoProcesado);                
                                echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                            }
                            break;

                        case '28':
                            if (substr($archivoProcesar, 0, 8) == "RDEBLIQD") {
                                //es el archivo de informaion, lo envio a la carpeta procedado, 
                                $anio = substr($nombreArchivo, 9, 4); //RDEBLIQD_202208030127
                                $path = "../archivos/lotes/".$idLugarPago."/".$anio."/procesado";
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                $archivoProcesado = $path . '/' . $archivoProcesar;
                                rename($archivos_carpeta, $archivoProcesado);                
                                echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                            }
                            break;

                        case '29':
                            if ($tipoArchivo == "html" || $tipoArchivo == "HTML") {
                                //es el archivo de pago mis cuentas con el resumen, lo envio a la carpeta resumen, 
                                $anio = substr($nombreArchivo, 14, 2);
                                $anio = 2000 + $anio;
                                $path = "../archivos/lotes/".$idLugarPago."/".$anio."/resumen";
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                $archivoProcesado = $path . '/' . $archivoProcesar;
                                rename($archivos_carpeta, $archivoProcesado);                
                                echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                            } else {
                                if ($tipoArchivo == "PDF") {
                                    if (substr($archivoProcesar, 0, 2) == "FE" || substr($archivoProcesar, 0, 2) == "RC") {
                                        //es el archivo de pago mis cuentas con el resumen, lo envio a la carpeta resumen, 
                                        $anio = 2000 + $anio;
                                        $anio = substr($nombreArchivo, 14, 2); //FE-2199-31072022-0011B00095786
                                    }
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio."/resumen";
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $archivoProcesado = $path . '/' . $archivoProcesar;
                                    rename($archivos_carpeta, $archivoProcesado);                
                                    echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                                }
                            }
                            break;

                        case '30':
                            if ($tipoArchivo == "zip" || $tipoArchivo == "ZIP") {
                                if (substr($archivoProcesar, 0, 8) == "cobranza" || substr($archivoProcesar, 0, 9) == "rendicion") {
                                    if (substr($archivoProcesar, 0, 8) == "cobranza") {
                                        $anio = substr($nombreArchivo, 8, 4); 
                                    } else {
                                        if (substr($archivoProcesar, 0, 9) == "rendicion") {
                                            $anio = substr($nombreArchivo, 15, 4); 
                                        } else {
                                            $anio = date('Y');
                                        }
                                    }
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio."/zips";
                                    $pathDescarga = "../archivos/lotes/a_procesar";
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $archivoProcesado = $path . '/' . $archivoProcesar;

                                    $zip = new ZipArchive;
                                    if ($zip->open($archivos_carpeta) === TRUE) {
                                        $path = getcwd(); // Path del directorio actual
                                        //$zip->extractTo($pathDescarga); // Extraemos el contenido en el directorio actual
                                        for( $i = 0 ; $i < $zip->numFiles ; $i++ ) {
                                            if ( $zip->getNameIndex( $i ) != '/') { // && $zip->getNameIndex( $i ) != '__MACOSX/_' ) {
                                                //print $zip->getNameIndex( $i ) . '<br>';
                                                $zip->extractTo( $pathDescarga, array($zip->getNameIndex($i)) );
                                            }
                                        }
                                        $zip->close();
                                    } else {
                                        echo "error al descomprimir<br>";
                                    }
                                    rename($archivos_carpeta, $archivoProcesado);               

                                    //enviamos a resumen los pdf que vienen en el zip
                                    foreach(glob("../archivos/lotes/a_procesar/*") as $archivos_carpeta) {             
                                        if (!is_dir($archivos_carpeta)){
                                            //echo $archivos_carpeta.'<br>';
                                            $archivoProcesar = explode('/', $archivos_carpeta);
                                            //print_r($archivoProcesar);
                                            //echo '<br>';
                                            $archivoProcesar = $archivoProcesar[4];
                                            $archivo = explode('.', $archivoProcesar);
                                            //echo "Cantidad elemento->".sizeof($archivo).' - archivo->'.$archivoProcesar.'<br>';
                                            //adhdbcar_030822_101528_conv03504.lis.pdf
                                            if (sizeof($archivo) == 3) {
                                                $tipoArchivo = $archivo[2]; //es el html de pago mis cuentas
                                                $nombreArchivo = $archivo[0].'.'.$archivo[1];
                                                if ($tipoArchivo == "PDF" || $tipoArchivo == "pdf") {
                                                    //es el archivo de pago mis cuentas con el resumen, lo envio a la carpeta resumen, 
                                                    $anio = substr($nombreArchivo, 13, 2); 
                                                    $anio = 2000 + $anio;
                                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio."/resumen";
                                                    if (!file_exists($path)) {
                                                        mkdir($path, 0777, true);
                                                    }
                                                    $archivoProcesado = $path . '/' . $archivoProcesar;
                                                    rename($archivos_carpeta, $archivoProcesado);                
                                                }
                                            }
                                        }
                                    }
                                    echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                                }
                            } else {
                                if ($tipoArchivo == "PDF" || $tipoArchivo == "pdf") {
                                    if (substr($archivoProcesar, 0, 2) == "nd" || substr($archivoProcesar, 0, 2) == "rd") {
                                    //es el archivo de debito CBU con el resumen, lo envio a la carpeta resumen, 
                                        $anio = date('Y');
                                    }
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio."/resumen";
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777, true);
                                    }
                                    $archivoProcesado = $path . '/' . $archivoProcesar;
                                    rename($archivos_carpeta, $archivoProcesado);                
                                    echo 'Archivo->'.$archivos_carpeta.'<br>'; 
                                }
                            }
                            break;

                        default:
                            echo 'es lote a procesar->'.$archivos_carpeta.'<br>';
                            break;
                    }
                }
            }
        }
        
        echo '<br><b>INICIA PROCESO</b><br>';
        //ahora se procesan todos los archivos de lotes de pagos
        foreach ($resLugares['datos'] as $lugarPago) {
            if (!in_array($lugarPago['id'], $lugaresProcesar)) { continue; }
            $idLugarPago = $lugarPago['id']; 
            echo 'Procesar -> '.$lugarPago['nombre'].'<br>';
            $hayArchivos = 0;
            foreach(glob("../archivos/lotes/a_procesar/*") as $archivos_carpeta) {             
                if (!is_dir($archivos_carpeta)){
                    //echo $archivos_carpeta.'<br>';
                    $archivoProcesar = explode('/', $archivos_carpeta);
                    //print_r($archivoProcesar);
                    //echo '<br>';
                    $archivoProcesar = $archivoProcesar[4];
                    $archivo = explode('.', $archivoProcesar);
                    if (!$cobranzaLogic->verificarArchivoExistente($idLugarPago, $archivoProcesar)) {
                        switch ($idLugarPago) {
                            case '22': //BAPRO
                                if (substr($archivoProcesar, 0, 16) == "BPColegioMedicos") {                            
                                    $tipoArchivo = $archivo[1];
                                    $nombreArchivo = $archivo[0];
                                    if ($tipoArchivo == "txt" || $tipoArchivo == "TXT") {
                                        //es el archivo a procesar, primero lo descomprimo y luego lo envio a la carpeta zips
                                        $arrLineas = array(file($archivos_carpeta));
                                        $cantidadLineas=sizeof($arrLineas[0]);
                                        $anio = intval(substr($nombreArchivo, 16, 4));
                                        $mes = substr($nombreArchivo, 20, 2);
                                        $dia = substr($nombreArchivo, 22, 2);
                                        $fechaApertura = $anio.'-'.$mes.'-'.$dia;
                                        $hayArchivos += 1;
                                        $procesar = TRUE;
                                        $path = "../archivos/lotes/".$idLugarPago."/".$anio;
                                        echo 'Procesa archivo->'.$archivos_carpeta.' fechaApertura->'.$fechaApertura.'<br>';
                                    } else {
                                        $procesar = FALSE;
                                    }
                                } else {
                                    $procesar = FALSE;
                                }
                                break;
                            
                            case '23': //PAGOFACIL
                                if (substr($archivoProcesar, 0, 5) == "70108") {
                                    $tipoArchivo = $archivo[1];
                                    $nombreArchivo = $archivo[0];
                                    if ($tipoArchivo == "csv") {
                                        //es el archivo a procesar
                                        $arrLineas = array(file($archivos_carpeta));
                                        $anio = intval(substr($nombreArchivo, 6, 2)) + 2000;
                                        $mes = substr($nombreArchivo, 8, 2);
                                        $dia = substr($nombreArchivo, 10, 2);
                                        $fechaApertura = $anio.'-'.$mes.'-'.$dia;                                
                                        $cantidadLineas=sizeof($arrLineas[0]);
                                        $hayArchivos += 1;
                                        $procesar = TRUE;
                                        $path = "../archivos/lotes/".$idLugarPago."/".$anio;
                                        echo 'Procesa archivo->'.$archivos_carpeta.' fechaApertura->'.$fechaApertura.'<br>';
                                    } else {
                                        $procesar = FALSE;
                                    }
                                } else {
                                    $procesar = FALSE;
                                }
                                break;
                            
                            case '25': //RAPIPAGO
                                if (substr($archivoProcesar, 0, 2) == "RP") {
                                    $tipoArchivo = $archivo[1];
                                    $nombreArchivo = $archivo[0];
                                    if ($tipoArchivo == "179") {
                                        $fechaApertura = NULL; // va null porque lo toma del primer registro
                                        $arrLineas = array(file($archivos_carpeta));
                                        $cantidadLineas=sizeof($arrLineas[0]);
                                        $hayArchivos += 1;
                                        $procesar = TRUE;
                                        echo 'Procesa archivo->'.$archivos_carpeta.' fechaApertura->'.$fechaApertura.'<br>';
                                        $anio = date('Y');
                                        $path = "../archivos/lotes/".$idLugarPago."/".$anio;
                                    } else {
                                        $procesar = FALSE;
                                    }
                                } else {
                                    $procesar = FALSE;
                                }
                                break;
                            
                            case '26': //LINK
                                if (substr($archivoProcesar, 0, 4) == "0182" || substr($archivoProcesar, 0, 4) == "0GHR") {
                                    $nombreArchivo = $archivo[0];
                                    $fechaApertura = NULL; // va null porque lo toma del primer registro
                                    $arrLineas = array(file($archivos_carpeta));
                                    $cantidadLineas=sizeof($arrLineas[0]);
                                    $hayArchivos += 1;
                                    $procesar = TRUE;
                                    echo 'Procesa archivo->'.$archivos_carpeta.' fechaApertura->'.$fechaApertura.'<br>';
                                    $anio = date('Y');
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio;
                                } else {
                                    $procesar = FALSE;
                                }
                                break;
                            
                            case '28': //TARJETAS
                                if (substr($archivoProcesar, 0, 8) == "LDEBLIQD" || substr($archivoProcesar, 0, 8) == "RDEBLIQC") {
                                    $tipoArchivo = $archivo[1];
                                    $nombreArchivo = $archivo[0];
                                    if ($tipoArchivo == "txt" || $tipoArchivo == "TXT") {
                                        $anio = substr($nombreArchivo, 9, 4);
                                        $mes = substr($nombreArchivo, 13, 2);
                                        $dia = substr($nombreArchivo, 15, 2);
                                        $fechaApertura = $anio.'-'.$mes.'-'.$dia;                                
                                        $arrLineas = array(file($archivos_carpeta));
                                        $cantidadLineas=sizeof($arrLineas[0]);
                                        $hayArchivos += 1;
                                        $procesar = TRUE;
                                        echo 'Procesa archivo->'.$archivos_carpeta.' fechaApertura->'.$fechaApertura.'<br>';
                                        $path = "../archivos/lotes/".$idLugarPago."/".$anio;
                                    } else {
                                        $procesar = FALSE;
                                    }
                                } else {
                                    $procesar = FALSE;
                                }
                                break;
                            
                            case '29': //PagoMisCuentas
                                if (substr($archivoProcesar, 0, 7) == "cob2199") {
                                    $tipoArchivo = $archivo[1];
                                    $nombreArchivo = $archivo[0];
                                    $anio = substr($tipoArchivo, 4, 2) + 2000;
                                    $mes = substr($tipoArchivo, 2, 2);
                                    $dia = substr($tipoArchivo, 0, 2);
                                    $fechaApertura = $anio.'-'.$mes.'-'.$dia;
                                    $arrLineas = array(file($archivos_carpeta));
                                    $cantidadLineas=sizeof($arrLineas[0]);
                                    $hayArchivos += 1;
                                    $procesar = TRUE;
                                    $path = "../archivos/lotes/".$idLugarPago."/".$anio;
                                    echo 'Procesa archivo->'.$archivos_carpeta.' fechaApertura->'.$fechaApertura.'<br>';
                                } else {
                                    $procesar = FALSE;
                                }
                                break;
                            
                            case '30': //CBU
                                if (substr($archivoProcesar, 0, 8) == "sda03504") {
                                    $tipoArchivo = $archivo[1];
                                    $nombreArchivo = $archivo[0];
                                    if ($tipoArchivo == "txt" || $tipoArchivo == "TXT") {
                                        $fechaApertura = NULL; // va null porque lo toma del primer registro
                                        $arrLineas = array(file($archivos_carpeta));
                                        $cantidadLineas=sizeof($arrLineas[0]);
                                        $hayArchivos += 1;
                                        $procesar = TRUE;
                                        $anio = date('Y');
                                        $path = "../archivos/lotes/".$idLugarPago."/".$anio;
                                        echo 'Procesa archivo->'.$archivos_carpeta.' fechaApertura->'.$fechaApertura.'<br>';
                                    } else {
                                        $procesar = FALSE;
                                    }
                                } else {
                                    $procesar = FALSE;
                                }
                                break;
                            
                            default:
                                // code...
                                break;
                        }
                    } else {
                        echo 'El archivo->'.$archivos_carpeta.' ya fue procesado<br>';
                        $path = "../archivos/lotes/ya_procesado";
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        $archivo = explode("/", $archivos_carpeta);
                        print_r($archivo);
                        $archivoLote = $archivo[4];
                        echo '<br>'.$archivo[4].'<br>';
                        $archivoProcesado = $path . '/' . $archivoLote;
                        $archivoProcesar = '../archivos/lotes/a_procesar/'.$archivoLote;
                        echo $archivoProcesado.'<br>';
                        rename($archivoProcesar, $archivoProcesado);
                    }
                }
                if ($procesar) {
                    $resProcesaLote = procesarLote($idLugarPago, $path, $archivos_carpeta, $archivoProcesar, $fechaApertura);
                    print_r($resProcesaLote);
                    echo '-----><br>';
                }
            }   
            echo 'hayArchivos -> '.$lugarPago['nombre'].' -> '.$hayArchivos.'<br>';
        } 
    } else {
        echo 'error -> '.$resLugares['mensaje'];
    }
} else {
    ?>
    <div class="col-md-12">
        <h3>Lotes a procesar</h3>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $cantidadArchivos = 0;
    foreach(glob("../archivos/lotes/a_procesar/*") as $archivos_carpeta) {
        if (!is_dir($archivos_carpeta)){
            echo $archivos_carpeta.'<br>';
            $cantidadArchivos++;
        }
    }
    if ($cantidadArchivos > 0) {
    ?>
        <div class="row">&nbsp;</div>
        <div class="col-md-3">
            <!--<form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="archivos_lotes_a_procesar.php">
                <button type="submit"  class="btn btn-default" >Confirma proceso</button>
                <input type="hidden" name="inicio" id="inicio" value="OK">
            </form>-->
            <form id="formColegiado" name="formColegiado" method="POST" action="archivos_lotes_a_procesar.php">
                <button type="submit" class="btn btn-default">Confirma proceso</button>
                <input type="hidden" name="inicio" id="inicio" value="OK">
            </form>
        </div>
        <div class="row">&nbsp;</div>
    <?php
    } else {
    ?>
        <div class="col-md-12 alert alert-warning">
            <h4>
                No hay archivos a procesar!!!
                <a href="subir_archivos.php" class="btn btn-info">Subir lotes</a>
            </h4>
        </div>
    <?php
    }
}
?>
<div class="row">&nbsp;</div>
<div class="col-md-3">
    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cobranza_lotes.php">
        <button type="submit"  class="btn btn-info" >Volver a Lotes</button>
        <input type="hidden" name="anioCobranza" id="anioCobranza" value="<?php echo date('Y'); ?>">
    </form>
</div>
<div class="row">&nbsp;</div>
<!-- Modal de Procesamiento Bootstrap 3 -->
<div class="modal fade" id="modalProcesando" tabindex="-1" role="dialog" aria-labelledby="modalProcesandoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content text-center" style="padding: 20px;">
            <div class="modal-body">
                <!-- Icono de carga compatible con Bootstrap 3 -->
                <div class="progress progress-striped active" style="margin-bottom: 15px;">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                        <span class="sr-only">Procesando...</span>
                    </div>
                </div>
                <h4 class="modal-title" id="modalProcesandoLabel" style="font-weight: bold;">¡Procesando Lote!</h4>
                <p class="text-muted" style="margin-bottom: 0; margin-top: 5px;">Por favor, espera un momento.</p>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#formColegiado').on('submit', function() {
            // Abre el modal usando la API de Bootstrap 3
            $('#modalProcesando').modal('show');
        });
    });    
</script>
<?php
require_once '../html/footer.php';
