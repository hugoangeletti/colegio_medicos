<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoObservacionLogic.php');
$colegiadoObservacionLogic = new colegiadoObservacionLogic();
require_once ('../../dataAccess/colegiadoLogic.php');

$continua = TRUE;
$mensaje = "OK";
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
    } else {
        $continua = FALSE;
        $mensaje = $resColegiado['mensaje'];
    }
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idColegiadoObservacion = $_GET['id'];
    } else {
        $continua = FALSE;
        $mensaje = "NO INICIALIZO LA OBSERVACION";
    }
} else {
    $continua = FALSE;
    $mensaje = "NO INICIALIZO MATRICULA";
}

if ($continua) {
    // Comprobamos si ha ocurrido un error.
    if (!isset($_FILES["imagen"]) || $_FILES["imagen"]["error"] > 0)
    {
        $continua = FALSE;
        $mensaje = "NO INICIALIZO LA IMAGEN";
    }
    else
    {
        // Verificamos si el tipo de archivo es un tipo de imagen permitido.
        // y que el tamaño del archivo no exceda los 16MB
        $permitidos = array("image/jpg", "image/jpeg", "image/gif", "image/png", "application/pdf");
        $limite_kb = 16384;

        $tipoArchivo = $_FILES['imagen']['type'];
        $tamanio = $_FILES['imagen']['size'];
        if (in_array($tipoArchivo, $permitidos) && $tamanio <= $limite_kb * 1024)
        {
            // Primero creamos un ID de conexión a nuestro servidor
            $cid = ftp_connect("192.168.2.50");
            // Luego creamos un login al mismo con nuestro usuario y contraseña
            $resultado = ftp_login($cid, "webcolmed","web.2017");
            // Comprobamos que se creo el Id de conexión y se pudo hacer el login
            if ((!$cid) || (!$resultado)) {
                $continua = FALSE;
                $mensaje = "Fallo en la conexión";
            } else {
                $pathMatricula = "/Legajos/".$matricula;
                $pathArchivo = $pathMatricula."/adjunto";

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
                    ftp_pasv($cid, true);            
                    ftp_chdir($cid, $pathArchivo);
                    $archivoSubidoTmp = $_FILES['imagen']['tmp_name']; //Obteniendo el nombre del archivo
                    $archivoSubido = $_FILES['imagen']['name']; //Obteniendo el nombre del archivo
                    $extension = end(explode('.', $_FILES['imagen']['name']));
                    $nombreArchivo = "O_".$idColegiadoObservacion."_".date('Ymd')."_".date('His').".".$extension;
            
                    $upload = ftp_put($cid, $nombreArchivo, $archivoSubidoTmp, FTP_BINARY);
                    if (!$upload) {
                        $continua = FALSE;
                        $mensaje = "Ha ocurrido un error al subir el archivo";
                    } else {
                        ftp_close($cid);

                        // Insertamos en la base de datos.
                        $resultado = $colegiadoObservacionLogic->agregarAdjunto($idColegiadoObservacion, $archivoSubido, $tipoArchivo, $nombreArchivo, $pathArchivo);
                        if (!$resultado['estado']) {
                            $continua = FALSE;
                            $mensaje = $resultado['mensaje'];                            
                        }
                    }
                }
            }
        }
        else
        {
            $continua = FALSE;
            $mensaje = "Formato de archivo no permitido o excede el tamaño límite de $limite_kb Kbytes.";
        }
    }
}

if($continua) {
    $tipoMensaje = 'alert alert-success';
} else {
    $tipoMensaje = 'alert alert-danger';
}

?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../colegiado_observaciones_adjunto.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoObservacion; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $tipoMensaje;?>">
    </form>
</body>


