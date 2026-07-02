<?php
if (isset($_GET['idColegiado'])) {
    //imprime lo seleccionado
    $tipoPdf = $_POST['tipoPdf'];
    $imprimir = $_POST['imprimir'];
    switch ($imprimir) {
        case 'CC':
            include_once 'colegiado_imprimir_ctacte.php';
            break;

        case 'PA':
            include_once 'emision_colegiacion_anual_imprimir.php';
            //include_once 'colegiado_imprimir_chequera.php';
            break;

        case 'DE':
            include_once 'colegiado_imprimir_deuda.php';
            break;

        case 'PR':
            include_once 'colegiado_imprimir_pagos_registrados.php';
            break;

        default:
            break;
    }
} else {
?>
    <div>
        <h3><?php echo $resultado['mensaje']; ?></h3>
        <div>&nbsp;</div>
        <div>
            <h3>Cerrar esta pestaña del navegador, No se puedo generar el reporte.</h3>
        </div>
    </div>
<?php
}

