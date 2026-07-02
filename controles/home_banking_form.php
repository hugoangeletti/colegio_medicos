<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/homeBankingLogic.php');
$homeBankingLogic = new homeBankingLogic();

$continua = TRUE;
$mensaje = "";

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}

$anio = date('Y');
$mes = date('m');
$periodo = date('Ym');

$resHomeBanking = $homeBankingLogic->obtenerNuevaLiquidacionPorPeriodo($periodo);
if ($resHomeBanking['estado']) {
    $codigo = $resHomeBanking['codigo'];
    $periodo = date('Ym');
    $fechaVencimiento = ultmioDiaDelMes(date('Y-m-d'));
    $mes = date('m');
    switch ($mes) {
         case '10':
             $mes = '0A';
             break;
         
         case '11':
             $mes = '0B';
             break;
         
         case '12':
             $mes = '0C';
             break;
         
         default:
             break;
    }
    $dia = rellenarCeros(date('d'), 2);
    $refresh = 'PGHR'.$mes.$dia;
    $control = 'CGHR'.$mes.$dia;

    $diaPMC = rellenarCeros(date('d'), 2);
    $mesPMC = rellenarCeros(date('m'), 2);
    $anioPMC = substr(date('Y'), 2, 2);
    $pagoMisCuentas = 'FAC2199.'.$diaPMC.$mesPMC.$anioPMC;

    $anio = date('Y');
    $path = "archivos/homeBanking/".$anio;

} else {
    $continua = FALSE;
    $mensaje .= "ERROR->".$resHomeBanking['mensaje'];
}
        
if ($continua) {
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Liquidación HomeBanking.</h4>
                </div>
                <div class="col-md-3 text-right">
                    <a href="home_banking.php" class="btn btn-primary" >Volver</a>
                </div> 
            </div>
        </div>
        <div class="panel-body">
            <form id="datosHomeBanking" name="datosHomeBanking" method="POST" action="datosHomeBanking\genera_archivos.php">
                <div class="row">
                    <div class="col-md-2">
                        <label>Período: </label>
                        <input class="form-control" type="text" name="periodo" id="periodo" value="<?php echo $periodo; ?>" readonly=""/>
                    </div>
                    <div class="col-md-2">
                        <label>Código: </label>
                        <input class="form-control" type="text" name="codigoLiquidacion" id="codigoLiquidacion" value="<?php echo $codigo; ?>" readonly=""/>
                    </div>
                    <div class="col-md-2">
                        <label>Fecha de Vencimiento: </label>
                        <input class="form-control" type="date" name="fechaVencimiento" id="fechaVencimiento" value="<?php echo $fechaVencimiento; ?>" min="<?php echo date('Y-m-d'); ?>" readonly/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        <label>Archivo CONTROL: </label>
                        <input class="form-control" type="text" name="archivoControl" id="archivoControl" value="<?php echo $control; ?>" readonly=""/>
                    </div>
                    <div class="col-md-2">
                        <label>Archivo REFRESH: </label>
                        <input class="form-control" type="text" name="archivoRefresh" id="archivoRefresh" value="<?php echo $refresh; ?>" readonly=""/>
                    </div>
                    <div class="col-md-2">
                        <label>Archivo PagoMisCuentas: </label>
                        <input class="form-control" type="text" name="archivoPMC" id="archivoPMC" value="<?php echo $pagoMisCuentas; ?>" readonly=""/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6 text-center">
                        <br>
                        <button type="submit" name='confirma' id='confirma' class="btn btn-primary">Generar envio</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <a href="home_banking.php" class="btn btn-primary" >Volver</a>
        </div>
    </div>
<?php            
}
require_once '../html/footer.php';
