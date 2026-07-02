<?php
session_start(); //comentar esta linea si no se trabaja con sesiones
//require_once 'dataAccess/sessionControl.php';
ini_set('default_charset', 'utf8');
date_default_timezone_set("America/Argentina/Buenos_Aires");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define("URL_TOTAL", "http://www.colmed1.com/colegio/");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_HOST", "localhost");
define("DB_SELECTED", "colmed1");
define("PATH_PDF", "colegio");

define("HOST", '192.168.2.50');
define("BASE_NAME", 'colmed1');
define("USER_NAME", 'hugo');
define("PASS", 'hugo');
/*
 * paths para utilizar absoluto y permitir
 * url amigable a traves de .htaccess
define("PATH_HOME", URL_TOTAL);
define("PATH_CSS", URL_TOTAL . "css/");
define("PATH_CONTROLS", URL_TOTAL . "controles/");
define("PATH_HTML", URL_TOTAL . "html/");
define("PATH_JS", URL_TOTAL . "js/");
define("PATH_DATAACCESS", URL_TOTAL . "dataAccess/");
define("PATH_IMAGES", URL_TOTAL . "images/");
 */

define("FTP_ARCHIVOS", "ftp://webcolmed:web.2017@192.168.2.50:21");
define("MAIL_MASIVO", "noreply@colmed1.org.ar");
//define("MAIL_MASIVO_PASS", "YWY1NDE4OTMwNGZlODE2NDRhNzQzMjI3");
define("MAIL_MASIVO_PASS", "ColMed@NRPL3214??");
//define("MAIL_MASIVO", "sistemas@colmed1.org.ar");
//define("MAIL_MASIVO_PASS", "@sistem@s_1965");

//obtengo el periodo actual
$periodoActual = date('Y');
$periodoActualContable = date('Y');
$mes = date('m');
if ($mes >= 1 && $mes <= 6) {
    $periodoActual -= 1;
}
if ($mes >= 1 && $mes <= 4) {
    $periodoActualContable -= 1;
}
define("PERIODO_ACTUAL", $periodoActual);
define("PERIODO_ACTUAL_CONTABLE", $periodoActualContable);

//vencimientos de los trimestres para chequeras con actualizacion trimestral
define("CHEQUERA_TRIMESTRAL", TRUE);
define("VENCIMIENTO_TRIMESTRE_UNO", $periodoActual."-09-30");
define("VENCIMIENTO_TRIMESTRE_DOS", $periodoActual."-12-31");
define("VENCIMIENTO_TRIMESTRE_TRES", ($periodoActual+1)."-03-31");
define("VENCIMIENTO_TRIMESTRE_CUATRO", ($periodoActual+1)."-06-30");

//cantidad de dias para mostrar los expedientes de especialistas para armar las resoluciones
define("DIAS_VIGENCIA_EXPEDIENTES", "545");

//mail para envios
define("MAIL_ENVIO_CAJA", "mbolognesi@cajademedicos.com.ar");

require_once (__DIR__) . '/conection_pdo.php';
require_once (__DIR__) . '/funcionesSeguridad.php';

