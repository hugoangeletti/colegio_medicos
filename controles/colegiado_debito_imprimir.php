<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
require_once ('../dataAccess/colegiadoDebitosLogic.php');
require_once ('../dataAccess/bancoLogic.php');

$continuar = true;
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
    $tipo = $_GET['tipo'];
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-6">
                    <h4>Imprimir adhesión al débito automático</h4>
                </div>
                <div class="col-md-3 text-left">
                    <a href="colegiado_debito_envia_mail.php?idColegiado=<?php echo $idColegiado; ?>&tipo=<?php echo $tipo; ?>" class="btn btn-default">Envía mail</a>
                </div>
                <div class="col-md-3 text-left">
                    <a href="colegiado_debito.php?idColegiado=<?php echo $idColegiado; ?>&tipo=<?php echo $tipo; ?>" class="btn btn-info">Volver</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="col-md-9">
                <?php
                include 'colegiado_debito_generar_pdf.php';
                if (isset($adhesionDebitoPDF)) {
                ?>
                    <div class="row">
                       <embed src='data:application/pdf;base64,<?php echo $adhesionDebitoPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                    </div> 
                <?php 
                } else {
                    echo 'ERROR AL OBTENER EL RECIBO';
                }
                ?>
            </div>
        </div>
    </div>
<?php
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
    </div>
    <a href="colegiado_debito.php?idColegiado=<?php echo $idColegiado; ?>&tipo=<?php echo $tipo; ?>" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");

