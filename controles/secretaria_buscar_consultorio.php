<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/busquedaLogic.php');
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 1, "asc" ]],
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
                                "sTitle": "Listado de medicos con sanciones",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_sanciones.pdf"
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
<div class="panel-heading"><h4><b>Búsqueda de Consultorios</b></h4></div>
<div class="panel-body">
    <?php
    if (isset($_POST['calle']) && $_POST['calle'] != ""){
        $calle = $_POST['calle'];
    } else {
        $calle = NULL;
        $lateral = NULL;
        $numero = NULL;
    }
    if (isset($_POST['lateral']) && $_POST['lateral'] != ""){
        $lateral = $_POST['lateral'];
    } else {
        $lateral = NULL;
    }
    if (isset($_POST['numero']) && $_POST['numero'] != ""){
        $numero = $_POST['numero'];
    } else {
        $numero = NULL;
    }
    ?>
    <div class="row">
        <form method="POST" action="secretaria_buscar_consultorio.php">
        <div class="col-xs-2">
            <b>Calle </b>  
            <input class="form-control" type="text" name="calle" id="calle" value="<?php echo $calle; ?>" required="" />
        </div>
        <div class="col-xs-2">
            <b>Lateral </b>  
            <input class="form-control" type="text" name="lateral" id="lateral" value="<?php echo $lateral; ?>" />
        </div>
        <div class="col-xs-2">
            <b>Número </b>  
            <input class="form-control" type="text" name="numero" id="numero" value="<?php echo $numero; ?>" />
        </div>
        <div class="col-xs-6">
            <br>
            <button type="submit" class="btn btn-success">Buscar</button>
        </div>
    </form>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    if (isset($calle)) {
        $busquedaLogic = new busquedaLogic();
        $resConsultorios = $busquedaLogic->obtenerConsultorios($calle, $lateral, $numero);
        if ($resConsultorios['estado']){
        ?>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Apellido y Nombres</th>
                        <th>Matricula</th>
                        <th>Calle</th>
                        <th>Lateral</th>
                        <th>Numero</th>
                        <th>Localidad</th>
                        <th>Habilitación</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resConsultorios['datos'] as $dato) 
                    {
                        $idConsultorio = $dato['id'];
                        $apellidoNombre = $dato['apellidoNombre'];
                        $matricula = $dato['matricula'];
                        $calle = $dato['calle'];
                        $lateral = $dato['lateral'];
                        $fechaHabilitacion = $dato['fechaHabilitacion'];
                        $numero = $dato['numero'];
                        $localidad = $dato['localidad'];
                        $estado = $dato['estado'];
                        $observacion = $dato['observacion'];
                        $origen = $dato['origen'];
                    ?>
                        <tr>
                            <td><?php echo $idConsultorio;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                            <td><?php echo $matricula;?></td>
                            <td><?php echo $calle;?></td>
                            <td><?php echo $lateral;?></td>
                            <td><?php echo $numero;?></td>                        
                            <td><?php echo $localidad;?></td>
                            <td><?php echo $fechaHabilitacion;?></td>
                            <td><?php echo $estado;?></td>
                            <td><?php echo $observacion;?></td>
                            <td><?php echo $origen;?></td>
                       </tr>
                      <?php
                    }
                    ?>
                </tbody>
            </table>
        <?php
        } else {
          ?>
            <div class="<?php echo $resConsultorios['clase']; ?>" role="alert">
                <span class="<?php echo $resConsultorios['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resConsultorios['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
    }
?>
</div>
</div>
<?php
require_once '../html/footer.php';