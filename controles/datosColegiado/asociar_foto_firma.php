<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoArchivoLogic.php');
$colegiadoArchivoLogic = new colegiadoArchivoLogic();

$nuevo = FALSE;
//var_dump($_POST); exit;
if (isset($_POST['idColegiado']) && isset($_POST['matricula'])) {
    $idColegiado = $_POST['idColegiado'];
    $matricula = $_POST['matricula'];
    if (isset($_POST['tituloDigital']) && $_POST['tituloDigital'] <> "") {
        $tituloDigital = $_POST['tituloDigital'];
    } else {
        $tituloDigital = NULL;
    }
    if (isset($_GET['tipo']) && $_GET['tipo'] != "") {
        $tipoIngreso = '&tipo='.$_GET['tipo'];
    } else {
        $tipoIngreso = "";
    }
    if (isset($_POST['accion']) && $_POST['accion'] == 'alta'){
        $nuevo = TRUE;
    } else {
        $nuevo = FALSE;
    }
    //busco si estan las imagenes para cargar en la tabla
    $error = '';
    $fileFoto = rellenarCeros($matricula, 8).'.jpg';
    $foto = fopen (FTP_ARCHIVOS."/Fotos/".$fileFoto, "rb");
    $fileFirma = rellenarCeros($matricula, 8).'.bmp';
    $firma = fopen (FTP_ARCHIVOS."/Firmas/".$fileFirma, "rb");
    if(isset($tituloDigital) && $tituloDigital == 'SI') {
        $fileTitulo = rellenarCeros($matricula, 8).'.pdf';
        $titulo = fopen (FTP_ARCHIVOS."/Titulos/".$fileTitulo, "rb");
    } else {
        $titulo = NULL;
    }
    //echo FTP_ARCHIVOS;
    if ($foto && $firma && (!isset($titulo) || $titulo)) {
        //existen los dos archivos y el titulo es digital y existe entonces cargo en la tabla los dos registros
        $resultado = $colegiadoArchivoLogic->agregarArchivo($idColegiado, 1, $fileFoto, 1, 0, 1);
        if ($resultado['estado']) {
            $resultado = $colegiadoArchivoLogic->agregarArchivo($idColegiado, 2, $fileFirma, 1, 0, 1);
            if ($resultado['estado']) {
                if (isset($titulo)) {
                    $resultado = $colegiadoArchivoLogic->agregarArchivo($idColegiado, 3, $fileTitulo, 1, 0, 1);
                }
            } else {
                $error = '&err=AR';
            }
        } else {
           $error = '&err=AR';
        }
    } else {
        $error = '&err=AR';
    }
    
}

if ($nuevo) {
    if ($error == '') {
        if ($_GET['tipo'] == "otro") {
            header('Location: ../colegiado_nuevo_otro.php?idColegiado='.$idColegiado);
        } else {
            if ($_GET['tipo'] == "baja") {
                header('Location: ../colegiado_nuevo_baja.php?idColegiado='.$idColegiado);
            } else {
                header('Location: ../colegiado_nuevo_paso3.php?idColegiado='.$idColegiado);
            }
        }
    } else {
        header('Location: ../colegiado_nuevo_archivos.php?idColegiado='.$idColegiado.$tipoIngreso.$error);
    }
} else {
    header('Location: ../colegiado_consulta.php?idColegiado='.$idColegiado.$error);
}