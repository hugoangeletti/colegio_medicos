<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaDomicilios').DataTable({
                    "iDisplayLength":7,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3,4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de Llamadas",
                                "sPdfOrientation": "portrait",
                                "sFileName": "ListadoDeLlamadas.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
    }
);
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        //include 'menuColegiado.php';
    }
    
    if (isset($_POST['mensaje'])) {
    ?>
       <div class="ocultarMensaje"> 
           <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php
//        if (substr($_POST['mensaje'], 0, 2) == 'OK'){
//            include 'domicilio_actualizar_imprimir.php';
//        }
    }
    
    //busco los domicilios anteriores
    $resDomicilios = $colegiadoDomicilioLogic->obtenerDomiciliosPorIdColegiado($idColegiado);
    if ($resDomicilios['estado']){
    ?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Domicilio actual y anteriores del colegiado</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-4">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
            </div>
            <div class="col-md-6 text-right">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="domicilio_actualizar.php?idColegiado=<?php echo $idColegiado;?>&ori=domicilios">
                    <button type="submit"  class="btn btn-success" >Actualizar Domicilio</button>
                </form>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12">
            <table  id="tablaDomicilios" class="display">
                <thead>
                    <tr>
                        <th style="text-align: center; display: none;">Id</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: center;">Domicilio</th>
                        <th style="text-align: center;">Localidad</th>
                        <th style="text-align: center;">Origen</th>
                        <th style="text-align: center;">Fecha Actualizaci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resDomicilios['datos'] as $dato){
                        $idColegiadoDomicilio = $dato['idColegiadoDomicilio'];
                        $domicilio = $dato['domicilio'];
                        $localidad = $dato['nombreLocalidad'];
                        $fechaActualizacion = $dato['fechaActualizacion'];
                        $origen = $dato['origen'];
                        $estado = $dato['idEstado'];
                        if ($estado == 1) {
                            $estadoDomicilio = 'Activo';
                        } else {
                            $estadoDomicilio = 'Baja';
                        }
                        ?>
                        <tr>
                            <td style="display: none"><?php echo $fechaActualizacion;?></td>
                            <td style="text-align: left;"><?php echo $estadoDomicilio;?></td>
                            <td style="text-align: left;"><?php echo $domicilio;?></td>
                            <td style="text-align: left;"><?php echo $localidad;?></td>
                            <td style="text-align: left;"><?php echo $origen;?></td>
                            <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaActualizacion);?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
    <?php
    } else {
    ?>
        <div class="<?php echo $resDomicilios['clase']; ?>" role="alert">
            <span class="<?php echo $resDomicilios['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resDomicilios['mensaje']; ?></strong></span>
        </div>        
    <?php        
    }
}
?>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
