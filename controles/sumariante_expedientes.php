<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();
require_once ('../dataAccess/sumarianteLogic.php');
$sumarianteLogic = new sumarianteLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":25,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3, 4, 5, 6],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de expedientes",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_expedientes.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
            });
            
   
</script>

<?php
$idSumariante = $_POST['idSumariante'];
$resSumariante = $sumarianteLogic->obtenerSumariantePorId($idSumariante);
if ($resSumariante['estado']){
    $sumariante = $resSumariante['datos'];
    $nombreSumariante = $sumariante['sumarianteBuscar'];
} else {
    $nombreSumariante = NULL;
}
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Expedientes asignados a <?php echo $nombreSumariante; ?></b></h4></div>
<div class="panel-body">
    <?php
    if (isset($_POST['tipoSumariante']) && $_POST['tipoSumariante'] != ""){
        $tipoSumariante = $_POST['tipoSumariante'];
    } else {
        $tipoSumariante = 'T';
    }
    ?>
    <div class="row">
        <div class="col-xs-6">
            <form method="POST" action="sumariante_expedientes.php">
                <div class="col-xs-6">
                    <b>Tipo de Sumariante:</b>
                    <select class="form-control" id="tipoSumariante" name="tipoSumariante" required onChange="this.form.submit()">
                        <option value="T" <?php if($tipoSumariante == "T") { echo 'selected'; } ?>>Titular</option>
                        <option value="S" <?php if($tipoSumariante == "S") { echo 'selected'; } ?>>Suplente</option>
                    </select>
                </div>
                <input type="hidden" id="idSumariante" name="idSumariante" value="<?php echo $idSumariante; ?>">
            </form>    
        </div>
        <div class="col-xs-6"></div>
    </div>
    <?php
    $resExpedientes = $eticaExpedienteLogic->obtenerExpedientePorSumarianteTipo($idSumariante, $tipoSumariante);
    //var_dump($facturas);
    if ($resExpedientes['estado']){
    ?>
        <br>
        <?php
        if (sizeof($resExpedientes['datos'])>0){
        ?>    
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Matr&iacute;cula</th>
                        <th>Apellido y Nombres</th>
                        <th>Car&aacute;tula</th>
                        <th>Nro. Expediente</th>
                        <th>Estado</th>
                        <th>Fecha Carga</th>
                        <th style="width: 30px">Ver</th>
                    </tr>
                </thead>
          <tbody>
              <?php
                  foreach ($resExpedientes['datos'] as $dato) 
                  {
                      $idEticaExpediente = $dato['idEticaExpediente'];
                      $idColegiado = ($dato['idColegiado']);
                      $nroExpediente = ($dato['nroExpediente']);
                      $caratula = $dato['caratula'];
                      $observaciones = $dato['observaciones'];
                      $idEticaEstado = $dato['idEticaEstado'];
                      $idUsuario = $dato['idUsuario'];
                      $fecha = $dato['fecha'];
                      $matricula = $dato['matricula'];
                      $apellido = $dato['apellido'];
                      $nombres = $dato['nombres'];
                      $eticaEstado = $dato['eticaEstado'];
                  ?>
                    <tr>
                	<td><?php echo $idEticaExpediente;?></td>
			<td><?php echo $matricula;?></td>
                        <td><?php echo $apellido.' '.$nombres;?></td>
                        <td><?php echo $caratula;?></td>
                        <td><?php echo $nroExpediente;?></td>
                        <td><?php echo $eticaEstado;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar(substr($fecha, 0, 10));?></td>
                        <td>
                            <div align="center">
                                <form method="POST" action="eticaExpediente_form.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="4">
                                    <input type="hidden" id="idEticaExpediente" name="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>">
                                    <input type="hidden" id="idSumariante" name="idSumariante" value="<?php echo $idSumariante; ?>">
                                    <input type="hidden" id="tipoSumariante" name="tipoSumariante" value="<?php echo $tipoSumariante; ?>">
                                </form>
                            </div>    
                        </td>
                   </tr>
                  <?php
                  }
              ?>
              
	   </tbody>
	  </table>
    <?php
    } else {
      ?>
        <div class="<?php echo $resExpedientes['clase']; ?>" role="alert">
            <span class="<?php echo $resExpedientes['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resExpedientes['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
} else {
?>
    <div class="<?php echo $resExpedientes['clase']; ?>" role="alert">
        <span class="<?php echo $resExpedientes['icono']; ?>" aria-hidden="true"></span>
        <span><strong><?php echo $resExpedientes['mensaje']; ?></strong></span>
    </div>
<?php
}    
?>
    <!-- BOTON VOLVER -->    
    <div class="col-md-12" style="text-align:right;">
        <form  method="POST" action="sumariante_lista.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
       </form>
    </div>  

</div>
</div>
<?php
require_once '../html/footer.php';