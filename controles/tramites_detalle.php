<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/tramiteLogic.php');
$tramiteLogic = new tramiteLogic();
?>
        <script>
            $(document).ready(function () {
                $('#tablaOrdenada').DataTable({
                    "iDisplayLength":50,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "order": [[ 1, "asc" ]],
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
if (isset($_POST['id'])) {
    $idTramite = $_POST['id'];
} else {
    $idTramite = NULL;
}

if (isset($idTramite)) {
    if (isset($_POST['mensaje']))
    {
     ?>
       <div class="ocultarMensaje"> 
       <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php    
    }   

    ?> 
    <div class="panel panel-info">
    <div class="panel-heading"><h4>Detalle de movimientos matriculares y altas (id:<?php echo $idTramite; ?>)</h4></div>
    <div class="panel-body">
        <div class="row">&nbsp;</div>
        <?php
        $resTramites = $tramiteLogic->obtenerTramiteDetalle($idTramite);
        if ($resTramites['estado']){
        ?>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Movimiento</th>
                        <th>Fecha</th>
                        <th>Matricula</th>
                        <th>Apellido y Nombre</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                      foreach ($resTramites['datos'] as $dato) 
                      {
                          $idTramiteDetalle = $dato['idTramiteDetalle'];
                          $nombreMovimiento = $dato['nombreMovimiento'];
                          $fecha = $dato['fecha'];
                          $matricula = $dato['matricula'];
                          $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                      ?>
                        <tr>
                            <td><?php echo $idTramiteDetalle;?></td>
                            <td><?php echo $nombreMovimiento;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fecha);?></td>
                            <td><?php echo $matricula;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                       </tr>
                      <?php
                      }
                  ?>
    	   </tbody>
    	  </table>
        <?php
        } else {
          ?>
            <div class="<?php echo $resTramites['clase']; ?>" role="alert">
                <span class="<?php echo $resTramites['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resTramites['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
    ?>
    </div>
    </div>
<?php
} else {
?>
    <div class="alert alert-danger" role="alert">
        <span><strong>Ingreso incorrecto</strong></span>
    </div>
<?php    
}
?>
<!-- BOTON VOLVER -->    
<div class="row">&nbsp;</div>
<div class="col-md-12" style="text-align:right;">
    <form  method="POST" action="tramites_listado.php">
        <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
   </form>
</div>  
<?php
require_once '../html/footer.php';