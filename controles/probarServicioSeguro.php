<?php
require_once '../dataAccess/config.php';
//permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesPhp.php';

$continuar = true;
if (isset($_GET['matricula']) && isset($_GET['numeroDocumento'])) {
    $numeroDocumento = $_GET['numeroDocumento'];
    $matricula = $_GET['matricula'];

    //obtener los datos del colegiDO
    ini_set('xdebug.var_display_max_depth', -1);
    ini_set('xdebug.var_display_max_children', -1);
    ini_set('xdebug.var_display_max_data', -1);
    set_time_limit(0);

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, 'https://vps-4498059-x.dattaweb.com/api/get-coberturas?dni='.$numeroDocumento.'&matricula='.$matricula);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    //curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);

    $headers = ['X-App-Key:' . '65fa2fcfbe50c606f3a6920e2fdece0398132876c8cdaad292ae3ee0ca4be54c'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $respuesta = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
        case 200:  
            $rta=(json_decode($respuesta,true));
            if (isset($rta) && isset($rta['respuesta'])) {
                $respuesta = $rta['respuesta'];
                if (isset($respuesta['error'])) {
                    $continuar = FALSE;
                    ?>
                    <h4 style="color: red;"><b><?php echo $respuesta['error']; ?></b></h4>
                <?php
                } else {
                    $continuar = TRUE;
                    //guardo respuesta api en variable %$pdf
                    $pdf = $respuesta;
                    //Añado cabecera PDF
                    header('Content-Type: application/pdf');
                    //Escritura de datos en fichero factura1.pdf
                    file_put_contents('Certificado.pdf',$respuesta);
                    echo 'Done';
                    ?>
                    <h4 style="color: green;"><b>Encontrado</b></h4>
                <?php
                }
            } else {
                $pdf_data = $respuesta;

                // And a path where the file will be created
                $path = '../archivos/seguro/2024/certifiado.pdf';

                // Then just save it like this
                file_put_contents( $path, $pdf_data );

                echo $path;
            }
            break;

        case 400:  
            $rta=(json_decode($respuesta,true));
            var_dump($rta);
            $continuar = FALSE;
            break;

        default:
            echo 'Codigo HTTP inesperado: '.$http_code."<br>";
            $continuar = FALSE;
            break;
        }

    }
} else {
    $continuar = FALSE;
}
if ($continuar) {
?>
    <div class="col-md12"><h6>Obtener Certificado de Cobertura</h6></div>
    <div class="container-fluid p-3" style="background-color: #8699a4 ; color: white">
        <div class="row">
            <div class="col-md-5">
                <h5>DNI <?php echo $numeroDocumento; ?></h5>
                <h5>M.P. <?php echo $matricula; ?></h5>
            </div>
            <div class="col-md-5">
            </div>
            <div class="col-md-2">
                <!--<a href="cuotasColegiacion.php?id=<?php echo $idColegiado; ?>" class="btn btn-dark">Volver</a>-->
            </div>
        </div>
    </div>
    <!--
    <div class="container-fluid p-3" >
       <embed src='data:application/pdf;base64,<?php echo $chequera; ?>' height="600px" width='100%' type='application/pdf'>   
    </div> 
    -->
<?php
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
    </div>
    <a href="tramites.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");
?>
  </div>

</body>
