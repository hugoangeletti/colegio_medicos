<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/envioMailDiarioLogic.php');
$envioMailDiarioLogic = new envioMailDiarioLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            //dom: 'T<"clear">lfrtip',
        });
    });              
</script>

<?php
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Notificaciones enviadas por colegiado</b></h4></div>
<div class="panel-body">
        <?php 
        if (!isset($_POST['idColegiado']) && !isset($_GET['idColegiado'])) {
        ?>
        <div class="col-md-12">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_envio_notificaciones.php">
                <div class="row">
                    <div class="col-md-3" style="text-align: right;">
                        <label>Matr&iacute;cula o Apellido y Nombre *</label>
                    </div>
                    <div class="col-md-7">
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                    </div>
                    <div class="col-md-2">
                        <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                    </div>
                </div>
            </form>
        </div>
        <?php
        } else { 
            if (isset($_POST['idColegiado']) && $_POST['idColegiado']<>'') {
                $idColegiado = $_POST['idColegiado'];
            } else {
                if (isset($_GET['idColegiado'])) {
                    $idColegiado = $_GET['idColegiado'];
                } else {
                    $idColegiado = NULL;
                }
            } 

            if (isset($idColegiado)) {
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                if ($resColegiado['estado'] && $resColegiado['datos']) {
                    $colegiado = $resColegiado['datos'];
                    $matricula = $colegiado['matricula'];
                    $apellidoNombre = trim($colegiado['apellido']).', '.  trim($colegiado['nombre']);
                    ?>
                    <div class="col-md-2">
                        <label>Matr&iacute;cula:&nbsp;</label> <?php echo $colegiado['matricula']; ?>
                    </div>
                    <div class="col-md-6">
                        <label>Apellido y Nombres:&nbsp;</label> <?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                    </div>
                    <div class="col-md-4">    
                    </div>
                    <div class="col-md-12">&nbsp;</div>
                    <?php
                    $resNotificaciones = $envioMailDiarioLogic->obtenerEnviosPorIdColegiado($idColegiado);
                    if ($resNotificaciones['estado']){
                    ?>
                        <div class="col-md-12 table-responsive">
                            <table id="tablaOrdenada" class="display">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Fecha de envío</th>
                                        <th>Mail</th>
                                        <th>Detalle del envío</th>
                                    </tr>
                                </thead>
                          <tbody>
                              <?php
                                  foreach ($resNotificaciones['datos'] as $dato) 
                                  {
                                      $idEnvioMailDiarioColegiado = $dato['idEnvioMailDiarioColegiado'];
                                      $fechaEnvio = substr($dato['fechaEnvio'], 0, 10);
                                      $mail = $dato['mail'];
                                      $detalleEnvio = $dato['detalleEnvio'];
                                      $idMailRechazado = $dato['idMailRechazado'];
                                      if (isset($idMailRechazado) && $idMailRechazado <> "") {
                                        $mail .= ' - FUE RECHAZADO';
                                      }
                                  ?>
                                    <tr>
                                        <td><?php echo $idEnvioMailDiarioColegiado;?></td>
                                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaEnvio);?></td>
                                        <td><?php echo $mail;?></td>
                                        <td><?php echo $detalleEnvio;?></td>
                                   </tr>
                                  <?php
                                  }
                              ?>

                           </tbody>
                          </table>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="col-md-12">
                            <div class="<?php echo $resNotificaciones['clase']; ?>" role="alert">
                                <span class="<?php echo $resNotificaciones['icono']; ?>" ></span>
                                <span><strong><?php echo $resNotificaciones['mensaje']; ?></strong></span>
                            </div>
                        </div>
                    <?php
                    }    
                } else {
                ?>
                    <div class="col-md-12">
                        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
                            <span class="<?php echo $resColegiado['icono']; ?>" ></span>
                            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
                        </div>
                    </div>
                <?php
                }
            }
        }
        ?>
    </div>
</div>
<?php
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idColegiado').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
    
</script>