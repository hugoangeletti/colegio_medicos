<?php
require_once '../../dataAccess/mesaEntradaAltaMatriculaLogic.php';

$tipoRemitente = "C";
$idTipoMesaEntrada = "1";
$idMotivo = $tipoMovimiento;
$fechaDesde = date('Y-m-d');
$ColORem = 'IdColegiado';
$distrito = $distritoOrigen;
$obraSocial = NULL;
$idPatologia = NULL;
$observaciones = '';

$idMesaEntrada = $mesaEntradaAltaMatriculaLogic->realizarAltaMesaEntrada($idColegiado, $tipoRemitente, $ColORem, $idTipoMesaEntrada, $observaciones);
if ($idMesaEntrada > 0) {
    $estadoAlta = realizarAltaMovimiento($idMesaEntrada, $idTipoMovimiento, $fechaDesde, $idMotivo, $distrito, $obraSocial, $idPatologia);

    if ($estadoAlta == 1) {
        $estadoAlta = realizarImpactoMovimiento($idColegiado, $idTipoMovimiento, $fechaDesde, $distrito, $idMesaEntrada, $idPatologia);
        if ($estadoAlta <= 0) {
            //hubo error al cargar el impacto del movimiento
            echo 'error al cargar el impacto del movimiento';
            exit;
        }
    } else {
        //hubo error al cargar el alta del movimiento
        echo 'hubo error al cargar el alta del movimiento';
        exit;
    }
} else {
    //hubo error al crear el moviento en mesa de entrada
    echo 'hubo error al crear el moviento en mesa de entrada';
    exit;
}                        