<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":10,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3, 4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de Habilitaciones Solicitadas",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_habilitaciones_solicitadas.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
            });
            
   
</script>

<?php
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
<div class="panel-heading"><h4><b>Solicitudes de Habilitación de Consultorios</b></h4></div>
<div class="panel-body">
    <div class="row">
        <?php
    $resHabilitaciones = $habilitacionConsultorioLogic->obtenerHabilitacionesSolicitadas();
    //var_dump($resRecetarios);
    if ($resHabilitaciones['estado']){
    ?>
        <br>
        <form method="POST" name="formHabilitaciones" id="formHabilitaciones" action="habilitaciones_asignar_inspector.php">        
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Fecha Solicitud</th>
                    <th>Dirección</th>
                    <th>Localidad</th>
                    <th>Teléfono</th>
                    <th>Matricula</th>
                    <th>Apellido y Nombre</th>
                    <th>Mail</th>
                    <th>Eliminar</th>
                    <th>Asignar Inspector</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resHabilitaciones['datos'] as $dato) 
                  {
                      $idMesaEntrada = $dato['idMesaEntrada'];
                      $matricula = $dato['matricula'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $domicilio = $dato['domicilio'];
                      $localidad = $dato['localidad'];
                      $telefono = $dato['telefono'];
                      $mail = $dato['mail'];
                      $idConsultorio = $dato['idConsultorio'];
                      $fechaSolicitud = $dato['fechaIngreso'];
                  ?>
                    <tr>
                	<td><?php echo $idMesaEntrada;?></td>
        			<td><?php echo cambiarFechaFormatoParaMostrar($fechaSolicitud);?></td>
                    <td><?php echo $domicilio;?></td>
        			<td><?php echo $localidad;?></td>
        			<td><?php echo $telefono;?></td>
        			<td><?php echo $matricula;?></td>
        			<td><?php echo $apellidoNombre;?></td>
        			<td><?php echo $mail;?></td>
                        <td>
                            <div align="center">
                                <a href="habilitaciones_eliminar_form.php?idMesaEntrada=<?php echo $idMesaEntrada; ?>" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"></a>
<!--                                <form method="POST" action="habilitaciones_eliminar_form.php">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"> Eliminar </button>
                                    <input type="hidden" id="idMesaEntrada" name="idMesaEntrada" value="<?php echo $idMesaEntrada; ?>">
                                </form>-->
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <input type="checkbox" name="idMesaEntrada[]" id="idMesaEntrada" value="<?php echo $idMesaEntrada; ?>" <?php //onclick="muestraConfirmacion('asignaInspector')" ?>/>
                            </div>    
                        </td>
                   </tr>
                  <?php
                  }
              ?>
              
	   </tbody>
        </table>
        <div class="row">&nbsp;</div>
        <div class="row" id="asignaInspector" <?php //style="display: none;" ?>>
            <div class="col-md-2">&nbsp;</div>
            <div class="col-md-5">
                <select class="form-control" id="idInspector" name="idInspector" required="">
                    <option value="">Seleccione Inspector</option>
                    <?php
                    $resInspectores = $habilitacionConsultorioLogic->obtenerInspectores('A');
                    if ($resInspectores['estado']) {
                        foreach ($resInspectores['datos'] as $row) {
                        ?>
                            <option value="<?php echo $row['idInspector'] ?>"><?php echo $row['apellidoNombre'] ?></option>
                        <?php
                        }
                    } 
                    ?>     
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" name='confirma' id='confirma' class="btn btn-success">Confirma Asignar Inspector</button>
            </div>
            <div class="col-md-2">&nbsp;</div>
        </div>
        </form>
    <?php
    } else {
    ?>
        <div class="<?php echo $resHabilitaciones['clase']; ?>" role="alert">
            <span class="<?php echo $resHabilitaciones['icono']; ?>" ></span>
            <span><strong><?php echo $resHabilitaciones['mensaje']; ?></strong></span>
        </div>
    <?php
    }  
    ?>
</div>
</div>
</div>
<?php
require_once '../html/footer.php';
?>
<script type="text/javascript">
    function muestraConfirmacion(bloque) {
        //verifica si hay alguna inspeccion marcada entonces habilita el boton confirmar,
        //sino lo oculta
        var chequeado = document.getElementById("idMesaEntrada").checked;
        var muestra = 'block';
        if (!chequeado) {
            muestra = 'none';
        }
        
        var checkboxes = document.getElementById("formHabilitaciones").idMesaEntrada;
        
        var cont = 0; 
        for (var x=0; x < checkboxes.length; x++) {
            if (checkboxes[x].checked) {
             cont = cont + 1;
            }
        }
        
        obj = document.getElementById(bloque);
        //if (cont > 0) {
        //    obj.style.display = 'block';
        //} else {
        //    obj.style.display = 'none';
        //}
        if (cont = 0 && !chequeado) {
            muestra = 'none';
        }
        obj.style.display = muestra;
    }
    
</script>
