<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoCargoLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":100,
            "language": {
                "url": "../public/lang/esp.lang"
            },
            dom: 'T<"clear">lfrtip',
            tableTools: {
               "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
               "aButtons": [
                    {
                        "sExtends": "pdf",
                        "mColumns" : [0, 1, 2, 3],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                        "sTitle": "Listado de consejero",
                        "sPdfOrientation": "portrait",
                        "sFileName": "listado_de_consejeros.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                    }
                    
            ]
            }
        });
    });
        
</script>

<?php
$colegiadoCargoLogic = new colegiadoCargoLogic();
?> 
<div class="panel panel-default">
<div class="panel-heading">
    <div class="row">
        <div class="col-md-9">
            <h4><b>Listado Histórico de Consejeros</b></h4>
        </div>
        <div class="col-md-3 text-left">
            <a href="secretaria_consejeros.php" class="btn btn-info">Volver a Consejeros </a>
        </div>
    </div>
</div>
<div class="panel-body">
    <div class="row">&nbsp;</div>
    <?php
    $resConsejeros = $colegiadoCargoLogic->obtenerConsejerosHistoricos();
    if ($resConsejeros['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Matrícula</th>
                    <th>Apellido y Nombres</th>
                    <th>Domicilio</th>
                    <th>Localidad</th>
                    <th>Teléfonos</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Fecha Desde</th>
                    <th>Fecha Hasta</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($resConsejeros['datos'] as $dato) {
                    $idColegiado = $dato['idColegiado'];
                    $idColegiadoCargo = $dato['idColegiadoCargo'];
                    $matricula = $dato['matricula'];
                    $apellido = $dato['apellido'];
                    $nombre = $dato['nombre'];
                    $nombreCargo = $dato['nombreCargo'];
                    $fechaDesde = $dato['fechaDesde'];
                    $fechaHasta = $dato['fechaHasta'];
                    $domicilioCompleto = $dato['domicilioCompleto'];
                    $localidad = $dato['localidad'];
                    $telefonos = $dato['telefonos'];
                    $mail = $dato['mail'];
                    ?>
                    <tr>
                        <td><?php echo $matricula;?></td>
                        <td><?php echo $apellido.' '.$nombre;?></td>
                        <td><?php echo $domicilioCompleto; ?></td>
                        <td><?php echo $localidad; ?></td>
                        <td><?php echo $telefonos; ?></td>
                        <td><?php echo $mail; ?></td>
                        <td><?php echo $nombreCargo;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaDesde);?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaHasta);?></td>
                   </tr>
                  <?php
                  }
              ?>
              
	   </tbody>
	  </table>
    <?php
    } else {
      ?>
        <div class="<?php echo $resConsejeros['clase']; ?>" role="alert">
            <span class="<?php echo $resConsejeros['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resConsejeros['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';