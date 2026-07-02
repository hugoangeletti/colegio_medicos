<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoCargoLogic.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
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
        
    function confirmaAnular()
    {
        if(confirm('¿Estas seguro de Borrar registro?'))
            return true;
        else
            return false;
    }

</script>

<?php
$colegiadoCargoLogic = new colegiadoCargoLogic();
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
    <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   

if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 108)) {
    //ingresa por secretaria
    $soloConsulta = FALSE;
} else {
    $soloConsulta = TRUE;
}
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Listado de Consejeros</b></h4></div>
<div class="panel-body">
    <?php 
    if (!$soloConsulta) {
    ?>
    <div class="row">
        <div class="col-xs-10">
            <a href="secretaria_consejeros_imprimir.php" class="btn btn-info btn-lg" target="_BLANK">Imprimir lista </a>
            <a href="secretaria_consejeros_imprimir_foto.php" class="btn btn-info btn-lg" target="_BLANK">Imprimir lista con foto </a>
            <a href="secretaria_consejeros_historico.php" class="btn btn-info btn-lg">Ver histórico </a>
        </div>
        <div class="col-xs-2">
            <a href="secretaria_consejeros_form.php?agregar" class="btn btn-info btn-lg">Nuevo Consejero </a>
        </div>
    </div>
    <?php 
    }
    ?>
    <div class="row">&nbsp;</div>
    <?php
    $resConsejeros = $colegiadoCargoLogic->obtenerConsejerosVigentes();
    if ($resConsejeros['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Matrícula</th>
                    <th>Apellido y Nombres</th>
                    <th>Domicilio</th>
                    <th>Localidad</th>
                    <th>Teléfonos</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Fecha Desde</th>
                    <th>Fecha Hasta</th>
                    <?php 
                    if (!$soloConsulta) {
                    ?>
                    <th style="width: 200px;">Acciones</th>
                    <?php
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $numeroOrden = 0;
                foreach ($resConsejeros['datos'] as $dato) {
                    $idColegiado = $dato['idColegiado'];
                    $idColegiadoCargo = $dato['idColegiadoCargo'];
                    $idCargoColegio = $dato['idCargoColegio'];
                    $matricula = $dato['matricula'];
                    $apellido = $dato['apellido'];
                    $nombre = $dato['nombre'];
                    $nombreCargo = $dato['nombreCargo'];
                    $fechaDesde = $dato['fechaDesde'];
                    $fechaHasta = $dato['fechaHasta'];
                    $domicilioCompleto = $dato['domicilioCompleto'];
                    $localidad = $dato['localidad'];
                    $telefonoFijo = $dato['telefonoFijo'];
                    $telefonoMovil = $dato['telefonoMovil'];
                    $mail = $dato['mail'];
                    $fechaMesaDesde = $dato['fechaMesaDesde'];
                    $fechaMesaHasta = $dato['fechaMesaHasta'];
                    if ($idCargoColegio <> 11) {
                        //si no es consejero, y tiene fechaMesaDesde muestro las fechas
                        if (isset($fechaMesaDesde) && $fechaMesaDesde <> "") {
                            $nombreCargo .= '<br>('.cambiarFechaFormatoParaMostrar($fechaMesaDesde).' al '.cambiarFechaFormatoParaMostrar($fechaMesaHasta).')';
                        }
                    }
                    $numeroOrden++;
                    ?>
                    <tr>
                        <td><?php echo $numeroOrden;?></td>
                        <td><?php echo $matricula;?></td>
                        <td><?php echo $apellido.' '.$nombre;?></td>
                        <td><?php echo $domicilioCompleto; ?></td>
                        <td><?php echo $localidad; ?></td>
                        <td><?php echo $telefonoFijo.'<br>'.$telefonoMovil; ?></td>
                        <td><?php echo $mail; ?></td>
                        <td><?php echo $nombreCargo;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaDesde);?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaHasta);?></td>
                        <?php 
                        if (!$soloConsulta) {
                        ?>
                            <td style="text-align: center;">
                                <a href="secretaria_consejeros_form.php?id=<?php echo $idColegiadoCargo; ?>&editar" class="btn btn-info">Editar </a>
                                <a href="datosConsejero/abm_consejero.php?id=<?php echo $idColegiadoCargo; ?>&baja" class="btn btn-info" onclick="return confirmaAnular()">Dar de baja </a>
                            </td>
                        <?php 
                        }
                        ?>
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