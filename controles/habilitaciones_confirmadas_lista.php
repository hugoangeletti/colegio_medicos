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
 
<script type="text/javascript">
    $(document).ready(function() {
        $('#tablaOrdenada').DataTable({
            "pageLength": 50,
            dom: 'Bfrtip',
            buttons: ['pdf']
        });
    });
</script>
<?php
/*
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "dom": 'T<"clear">lfrtip',
            "tableTools": {
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
*/
?>
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
<div class="panel-heading"><h4><b>Solicitudes de Habilitación de Consultorios Inspeccionados</b></h4></div>
<div class="panel-body">
    <div class="row">
        <?php
        if (isset($_POST['idInspector']) && $_POST['idInspector'] != ""){
            $idInspector = $_POST['idInspector'];
        } else {
            $idInspector = NULL;
        }
        ?>
        <div class="row">
            <div class="col-xs-6">
                <form method="POST" action="habilitaciones_confirmadas_lista.php">
                    <div class="col-xs-8">
                        <select class="form-control" id="idInspector" name="idInspector" required="" onChange="this.form.submit()">
                            <option value="">Seleccione Inspector</option>
                            <?php
                            $resInspectores = $habilitacionConsultorioLogic->obtenerInspectores('A');
                            if ($resInspectores['estado']) {
                                foreach ($resInspectores['datos'] as $row) {
                                ?>
                            <option value="<?php echo $row['idInspector'] ?>" <?php if ($idInspector == $row['idInspector']) { ?> selected="" <?php } ?>><?php echo $row['apellidoNombre'] ?></option>
                                <?php
                                }
                            } 
                            ?>     
                        </select>
                    </div>
                </form>    
            </div>
            <div class="col-xs-4">&nbsp;</div>
        </div>
        <?php
        $resHabilitaciones = $habilitacionConsultorioLogic->obtenerHabilitacionesConfirmadasPorInspector($idInspector);
        //var_dump($resRecetarios);
        if ($resHabilitaciones['estado']){
        ?>
            <br>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Dirección</th>
                        <th>Localidad</th>
                        <th>Teléfono</th>
                        <th>Matricula</th>
                        <th>Apellido y Nombre</th>
                        <!--<th>Mail</th>-->
                        <th>Inspección</th>
                        <th>Habilitación</th>
                        <th>Modificar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                      foreach ($resHabilitaciones['datos'] as $dato) 
                      {
                          $idInspectorHabilitacion = $dato['idInspectorHabilitacion'];
                          $inspecciones = array('inspecciones' => array('idInspeccion' => $idInspectorHabilitacion));
                          $idMesaEntrada = $dato['idMesaEntrada'];
                          $matricula = $dato['matricula'];
                          $apellidoNombre = $dato['apellidoNombre'];
                          $domicilio = $dato['domicilio'];
                          $localidad = $dato['localidad'];
                          $telefono = $dato['telefono'];
                          $mail = $dato['mail'];
                          $fechaInspeccion = $dato['fechaInspeccion'];
                          $fechaHabilitacion = $dato['fechaHabilitacion'];
                        if (isset($fechaHabilitacion) && $fechaHabilitacion != "") {
                            $habilitacion = cambiarFechaFormatoParaMostrar($fechaHabilitacion);
                            $colorFont = ' style="color: #000000;"';
                        } else {
                            $habilitacion = 'NO HABILITADO';
                            $colorFont = ' style="color: #DC143C;"';
                        }
                      ?>
                            <tr <?php echo $colorFont; ?>>
                            <td><?php echo $idMesaEntrada;?></td>
                            <td><?php echo $domicilio;?></td>
                            <td><?php echo $localidad;?></td>
                            <td><?php echo $telefono;?></td>
                            <td><?php echo $matricula;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                            <!--<td><?php echo $mail;?></td>-->
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaInspeccion);?></td>
                            <td><?php echo $habilitacion; ?></td>
                            <td>
                                <div align="center">
                                    <form name="myForm"  method="POST" action="habilitaciones_inspeccion_form.php?id=<?php echo $idInspectorHabilitacion; ?>">
                                        <button type="submit" name='confirma' id='confirma' class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                        <input type="hidden"  name="accion" id="accion" value="3">
                                    </form>
                                </div>    
                            </td>
                            <td>
                                <div align="center">
                                    <form name="myForm"  method="POST" action="habilitaciones_inspeccion_form.php?id=<?php echo $idInspectorHabilitacion; ?>">
                                        <button type="submit" name='confirma' id='confirma' class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"></button>
                                        <input type="hidden"  name="accion" id="accion" value="2">
                                    </form>
                                </div>    
                            </td>
                       </tr>
                      <?php
                      }
                  ?>

               </tbody>
            </table>
            <div class="row">&nbsp;</div>
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
