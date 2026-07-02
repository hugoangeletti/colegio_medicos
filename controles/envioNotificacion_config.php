<?php
/*
//produccion
$desarrollo = (__DIR__) . '/';
require_once $desarrollo . '../dataAccess/conection.php';
require_once $desarrollo . '../dataAccess/funcionesPhp.php';
require_once $desarrollo . '../dataAccess/envioMailDiarioLogic.php';
require_once $desarrollo . '../dataAccess/colegiadoDeudaAnualLogic.php';
require_once $desarrollo . '../dataAccess/colegiadoPlanPagoLogic.php';;
define("ENV", "prod");
define("FTP_ARCHIVOS", "ftp://webcolmed:web.2017@192.168.2.50:21");
define("MAIL_MASIVO", "noreply@colmed1.org.ar");
define("MAIL_MASIVO_PASS", "ColMed@NRPL3214??");

//obtengo el periodo actual
$periodoActual = date('Y');
$mes = date('m');
if ($mes >= 1 && $mes < 5) {
    $periodoActual -= 1;
}
define("PERIODO_ACTUAL", $periodoActual);
//fin produccion
*/

/**/
//desarrollo
$desarrollo = "";
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/envioMailDiarioLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
//define("ENV", "desa");
//fin desarrollo

