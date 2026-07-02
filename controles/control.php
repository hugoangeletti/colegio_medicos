<?php
require_once '../dataAccess/config.php';
require_once '../dataAccess/funcionesSeguridad.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/usuarioLogic.php';
$userName = $_POST['userName'];
//$pass = hashData($_POST['clave']);
$pass = $_POST['clave'];
$resUsuario = $usuarioLogic->validarUsuario($userName, $pass);
if ($resUsuario['estado']){
    if ($resUsuario['datos']){
        $usuario = $resUsuario['datos'];
        //ingreso valido, se loguea
        $idUsuario = $usuario['idUsuario'];
        $nombreCompleto = $usuario['nombreCompleto'];
        $tipoUsuario = $usuario['tipoUsuario'];
        if ($tipoUsuario == "E") {
            //si es empleado tiene permisos, sino no puede actualizar datos
            $soloConsulta = FALSE;
        } else {
            $soloConsulta = TRUE;
        }


        $resLog = $usuarioLogic->logUsuario($idUsuario);

        $_SESSION['user'] = $userName;
        $_SESSION['user_id'] = $idUsuario;
        $_SESSION["autentificado"] = "SI";
        $_SESSION['user_mac'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['private'] = hashData("C9l1n3s9s39m9");
        $_SESSION['private_alternative'] = $_SESSION['private'];
        $_SESSION['user_entidad'] = array("Id" => $idUsuario, "nombreUsuario" => $userName, 'tipoUsuario' => $tipoUsuario, 'soloConsulta' => $soloConsulta, 'nombreCompleto' => $nombreCompleto);
        $_SESSION['user_last_activity'] = time();

        if (date("m") > 6) {
            $_SESSION['periodoActual'] = date("Y");
        } else {
            $_SESSION['periodoActual'] = date("Y") - 1;
        }
        
        header('Location: administracion.php');
        exit;
    } else {
        header('Location: login.php?err=OK');
        exit;
    }
} else {
    header('Location: ../html/pagina-error.php');
    exit;
}
