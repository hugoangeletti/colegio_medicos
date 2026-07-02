<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/secretarioadhocLogic.php');
$secretarioadhocLogic = new secretarioadhocLogic();
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
                                "mColumns" : [0, 1, 2, 3, 4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Listado de movimientos por expediente",
                                "sPdfOrientation": "portrait",
                                "sFileName": "listado_de_movimientos_por_expediente.pdf"
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
<div class="panel-heading"><h4><b>Secretarios ad-hoc</b></h4></div>
<div class="panel-body">
    <div class="row">
        <div class="col-xs-9">&nbsp;</div>
        <div class="col-xs-3">
            <form method="POST" action="secretarioadhoc_form.php">
                <div align="right">
                <button type="submit" class="btn btn-success btn-lg">Nuevo Secretario ad-hoc</button>
                <input type="hidden" id="accion" name="accion" value="1">
                </div>
            </form>
        </div>
    </div>
    <?php
    $resSecretarios = $secretarioadhocLogic->obtenerSecretariosadhoc();
    //var_dump($facturas);
    if ($resSecretarios['estado']){
    ?>
        <br>
        <?php
        if (sizeof($resSecretarios['datos'])>0){
        ?>    
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Apellido y Nombres</th>
                        <th>Estado actual</th>
                        <th style="width: 30px">Editar</th>
                    </tr>
                </thead>
          <tbody>
              <?php
                  foreach ($resSecretarios['datos'] as $dato) 
                  {
                      $idSecretarioadhoc = $dato['id'];
                      $nombre = $dato['nombre'];
                      $estado = $dato['estado'];
                  ?>
                    <tr>
                	<td><?php echo $idSecretarioadhoc;?></td>
                        <td><?php echo $nombre;?></td>
                        <td><?php
                                switch ($estado) {
                                    case "A":
                                        ?>
                                        <div style="color: green;">Activo</div>
                                        <?php
                                        break;

                                    case "B":
                                        ?>
                                        <div style="color: red;">Anulado</div>
                                        <?php
                                        break;

                                    default:
                                        break;
                                }
                        ?></td>
                        <td>
                            <div align="center">
                                <form method="POST" action="secretarioadhoc_form.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="3">
                                    <input type="hidden" id="idSecretarioadhoc" name="idSecretarioadhoc" value="<?php echo $idSecretarioadhoc; ?>">
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
        <div class="<?php echo $resSecretarios['clase']; ?>" role="alert">
            <span class="<?php echo $resSecretarios['icono']; ?>" ></span>
            <span><strong><?php echo $resSecretarios['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
} else {
?>
        <div class="<?php echo $resSecretarios['clase']; ?>" role="alert">
            <span class="<?php echo $resSecretarios['icono']; ?>" ></span>
            <span><strong><?php echo $resSecretarios['mensaje']; ?></strong></span>
        </div>
<?php
}    
?>
</div>
</div>
<?php
require_once '../html/footer.php';