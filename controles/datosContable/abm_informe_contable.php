<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/informeContableLogic.php');

$continua = TRUE;
$mensaje = "";

$periodo = PERIODO_ACTUAL;
if (isset($_POST['mesProcesado']) && $_POST['mesProcesado'] <> "") {
    $mesProcesado = $_POST['mesProcesado'];
} else {
    $continua = FALSE;
    $mensaje .= 'mesProcesado no ingresado - ';
}
$informeContableLogic = new informeContableLogic();
if ($continua){
    $resultado = $informeContableLogic->generarInformeContable($periodo, $mesProcesado, null);

    if ($resultado['estado']) {
        //se generan los archivos para descarga
        $resInformes = $informeContableLogic->obtenerInformesPorMesProcesado($mesProcesado);
        if ($resInformes['estado']) {
            $sub_path = 'informe_contable/'.PERIODO_ACTUAL.'/'.$mesProcesado;
            $path = '../../archivos/'.$sub_path;
            foreach ($resInformes['datos'] as $informe) {
                $idInforme = $informe['id'];
                $origen = $informe['origen'];
                //$path = $path.'/'.$origen;
                if (!file_exists($path.'/'.$origen)) {
                    mkdir($path.'/'.$origen, 0777, true);
                }

                //procesamos cabecera
                $resInformeCabecera = $informeContableLogic->obtenerInformeCabeceraPorIdInforme($idInforme);
                //var_dump($resDebitoDetalle); exit;
                $cantidadRegistros = sizeof($resInformeCabecera['datos']);
                if ($resInformeCabecera['estado'] && $cantidadRegistros > 0){
                    //preparamos el txt de salida
                    $nombreArchivo = 'VCabecer.txt';
                    $nombreArchivoCompleto = $path.'/'.$origen.'/'.$nombreArchivo;
                    if (file_exists($nombreArchivoCompleto)) {
                        unlink($nombreArchivoCompleto);
                    }
                    $fileControl = fopen($nombreArchivoCompleto, "w")or  die("Problemas en la creacion del archivo ".$nombreArchivoCompleto);

                    foreach ($resInformeCabecera['datos'] as $dato) {
                        $linea = $dato['linea_archivo'];
                        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
                            $claseMensaje = "alert alert-danger";
                            $continua = false;   
                            $mensaje = "NO SE PUDO GENERAR EL ARCHIVO ".$nombreArchivo." TXT.";
                            break;
                        }
                    } 
                    fclose($fileControl);
                }   

                //procesamos items
                $resInformeItems = $informeContableLogic->obtenerInformeItemsPorIdInforme($idInforme);
                //var_dump($resDebitoDetalle); exit;
                $cantidadRegistros = sizeof($resInformeItems['datos']);
                if ($resInformeItems['estado'] && $cantidadRegistros > 0){
                    //preparamos el txt de salida
                    $nombreArchivo = 'VItems.txt';
                    $nombreArchivoCompleto = $path.'/'.$origen.'/'.$nombreArchivo;
                    if (file_exists($nombreArchivoCompleto)) {
                        unlink($nombreArchivoCompleto);
                    }
                    $fileControl = fopen($nombreArchivoCompleto, "w")or  die("Problemas en la creacion del archivo ".$nombreArchivoCompleto);

                    foreach ($resInformeItems['datos'] as $dato) {
                        $linea = $dato['linea_archivo'];
                        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
                            $claseMensaje = "alert alert-danger";
                            $continua = false;   
                            $mensaje = "NO SE PUDO GENERAR EL ARCHIVO ".$nombreArchivo." TXT.";
                            break;
                        }
                    } 
                    fclose($fileControl);
                }   

                //procesamos pagos
                $nombreArchivo = 'VMedPago.txt';
                $resInformePagos = $informeContableLogic->obtenerInformePagosPorIdInforme($idInforme);
                //var_dump($resDebitoDetalle); exit;
                $cantidadRegistros = sizeof($resInformePagos['datos']);
                if ($resInformePagos['estado'] && $cantidadRegistros > 0){
                    //preparamos el txt de salida
                    $nombreArchivoCompleto = $path.'/'.$origen.'/'.$nombreArchivo;
                    if (file_exists($nombreArchivoCompleto)) {
                        unlink($nombreArchivoCompleto);
                    }
                    $fileControl = fopen($nombreArchivoCompleto, "w")or  die("Problemas en la creacion del archivo ".$nombreArchivoCompleto);

                    foreach ($resInformePagos['datos'] as $dato) {
                        $linea = $dato['linea_archivo'];
                        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
                            $claseMensaje = "alert alert-danger";
                            $continua = false;   
                            $mensaje = "NO SE PUDO GENERAR EL ARCHIVO ".$nombreArchivo." TXT.";
                            break;
                        }
                    } 
                    fclose($fileControl);
                }   
                $informeContableLogic->guardarNombreArchivo($idInforme, $sub_path);
            }
        } else {
            $resultado['mensaje'] = $resInformes['mensaje'];
            $resultado['icono'] = "glyphicon glyphicon-remove";
            $resultado['clase'] = "alert alert-danger";
            $resultado['estado'] = FALSE;
        }
    }
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = $continua;
}
if (!$resultado['estado']) {
    var_dump($_POST);
    echo '<br>';
    var_dump($resultado);
    exit;
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../informe_contable_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="periodo" id="periodo" value="<?php echo $periodo; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../informe_contable_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="periodo" id="periodo" value="<?php echo $periodo; ?>">
            <input type="hidden"  name="mesProcesado" id="mesProcesado" value="<?php echo $mesProcesado;?>">
        </form>
    <?php
    }
    ?>
</body>

