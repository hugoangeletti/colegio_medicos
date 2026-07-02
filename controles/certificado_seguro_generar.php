<?php
require_once ('../dataAccess/config.php');
//permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoCertificadoSeguroLogic.php');
$colegiadoCertificadoSeguroLogic = new colegiadoCertificadoSeguroLogic();

if (isset($_POST['anio']) && $_POST['anio'] <> "" && isset($_POST['mes']) && $_POST['mes'] <> "") {
    $periodo = $_POST['anio'].$_POST['mes'];
    echo '<b>INICIA PROCESO PERIODO '.$periodo.'</b><br>';
    echo "../archivos/seguros/".$periodo."/*";
    $hayArchivos = 0;
    foreach(glob("../archivos/seguros/".$periodo."/*") as $archivos_carpeta) {             
        if (!is_dir($archivos_carpeta)){
            echo $archivos_carpeta.'<br>';
            $archivoProcesar = explode('/', $archivos_carpeta);
            print_r($archivoProcesar);
            echo '<br>';
            $pathOrigen = $archivoProcesar[1].$archivoProcesar[2].$archivoProcesar[3];
            $archivoProcesar = $archivoProcesar[4];
            $archivo = explode('.', $archivoProcesar);
            echo "Cantidad elemento->".sizeof($archivo).' - archivo->'.$archivoProcesar.'<br>';
            if (sizeof($archivo) == 2) {
                //se procesa
                $nombreCompleto = explode('-', $archivo[0]);
                $i = sizeof($nombreCompleto);
                echo 'Elementos->'.$i.' ';
                $numeroDocumento = $nombreCompleto[$i-1];
                echo 'Documento->'.$numeroDocumento;

                //buscar al colegiado por numero de documento
                $resColegiado = $colegiadoCertificadoSeguroLogic->obtenerColegiadoPorDocumento($numeroDocumento);
                if ($resColegiado['estado']) {
                    $colegiado = $resColegiado['datos'];
                    $idColegiado = $colegiado['idColegiado'];
                    $correoElectronico = $colegiado['correoElectronico'];
                } else {
                    $idColegiado = NULL;
                }

                //gurdamos el registro del certificado
                $resCertificado = $colegiadoCertificadoSeguroLogic->guardarCertificadoPorColegiado($idColegiado, $numeroDocumento, $periodo, $pathOrigen, $nombreArchivo, $correoElectronico);
                if ($resCertificado['estado']) {
                    echo ' OK';
                } else {
                    echo ' Error->'.$resCertificado['mensaje'];
                }
                echo '<br>';
            }
        }
    }
} else {
    if (isset($_POST['periodo']) && $_POST['periodo'] <> "") {
        $periodo = $_POST['periodo'];
        ?>
        <div class="col-md-12">
            <h3>Archivos a procesar</h3>
        </div>
        <div class="row">&nbsp;</div>
        <?php
        $cantidadArchivos = 0;
        foreach(glob("../archivos/seguro/".$periodo."/*") as $archivos_carpeta) {             
            if (!is_dir($archivos_carpeta)){
                echo $archivos_carpeta.'<br>';
                $cantidadArchivos++;
            }
        }
        if ($cantidadArchivos > 0) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="col-md-3">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="archivos_lotes_a_procesar.php">
                    <button type="submit"  class="btn btn-default" >Confirma proceso</button>
                    <input type="hidden" name="inicio" id="inicio" value="OK">
                    <input type="hidden" name="periodo" id="periodo" value="<?php echo $periodo; ?>">
                </form>
            </div>
            <div class="row">&nbsp;</div>
        <?php
        } else {
        ?>
            <div class="col-md-12 alert alert-warning">
                <h4>No hay archivos a procesar!!!</h4>
            </div>
        <?php
        }
    } else {
    ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="certificado_seguro_generar.php">
                <div class="col-md-1">
                    <label>Año *</label>
                    <select class="form-control" id="anio" name="anio" required>
                        <option value="" selected>Saleccione Año</option>
                        <option value="<?php echo date('Y'); ?>" ><?php echo date('Y'); ?></option>
                        <option value="<?php echo (date('Y') + 1); ?>" ><?php echo (date('Y') + 1); ?></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Mes *</label>
                    <select class="form-control" id="mes" name="mes" required>
                        <option value="" selected>Saleccione Mes</option>
                        <option value="01" >Enero</option>
                        <option value="02" >Febrero</option>
                        <option value="03" >Marzo</option>
                        <option value="04" >Abril</option>
                        <option value="05" >Mayo</option>
                        <option value="06" >Junio</option>
                        <option value="07" >Julio</option>
                        <option value="08" >Agosto</option>
                        <option value="09" >Septiembre</option>
                        <option value="10" >Octubre</option>
                        <option value="11" >Noviembre</option>
                        <option value="12" >Diciembre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit"  class="btn btn-default" >Confirma proceso</button>
                    <input type="hidden" name="inicio" id="inicio" value="OK">
                </div>
            </form>
        </div>
        <div class="row">&nbsp;</div>
    <?php
    }
}
?>
<div class="row">&nbsp;</div>
<div class="col-md-3">
    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="certificado_seguro_generar.php">
        <button type="submit"  class="btn btn-info" >Volver</button>
    </form>
</div>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
