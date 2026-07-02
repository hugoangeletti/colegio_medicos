<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../dataAccess/pagosNoRegistradosLogic.php');
$pagosNoRegistradosLogic = new pagosNoRegistradosLogic();
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
<div class="panel-heading"><h4><b>Pagos No Registrados</b></h4></div>
<div class="panel-body">
        <?php 
        if (!isset($_POST['idColegiado']) && !isset($_GET['idColegiado'])) {
        ?>
        <div class="col-md-12">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_pagosnoregistrados.php">
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
                        <form method="POST" action="tesoreria_pagosnoregistrados_nuevo.php?idColegiado=<?php echo $idColegiado; ?>">
                            <button type="submit" class="btn btn-success btn-lg">Nuevo Pago No Registrado</button>
                            <input type="hidden" id="accion" name="accion" value="1">
                        </form>
                    </div>
                    <div class="col-md-12">&nbsp;</div>
                    <?php
                    $resPagosNoRegistrados = $pagosNoRegistradosLogic->obtenerPagosNoRegistrados($idColegiado);
                    if ($resPagosNoRegistrados['estado']){
                    ?>
                        <div class="col-md-12 table-responsive">
                            <table id="tablaOrdenada" class="display">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Tipo de pago</th>
                                        <th>Recibo</th>
                                        <th>Cuota Colegiación</th>
                                        <th>Cuota Plan de Pagos</th>
                                        <th>Fecha Pago</th>
                                        <th>Fecha Carga</th>
                                        <th>Lugar de Pago</th>
                                        <th>Detalle</th>
                                        <th style="width: 30px">Acción</th>
                                    </tr>
                                </thead>
                          <tbody>
                              <?php
                                  foreach ($resPagosNoRegistrados['datos'] as $dato) 
                                  {
                                      $idPagoNoRegistrado = $dato['idPagoNoRegistrado'];
                                      $cuota = $dato['cuota'];
                                      $recibo = $dato['recibo'];
                                      $fechaPago = $dato['fechaPago'];
                                      $fechaCarga = $dato['fechaCarga'];
                                      $lugarPago = $dato['lugarPago'];
                                      $detalle = $dato['detalle'];
                                      $tipoPago = $dato['tipoPago'];
                                      if ($tipoPago == 'P') {
                                        $tipoPago = 'Cuota Plan Pagos';
                                        $cuotaPP = $dato['idPlanPago'].'-'.$dato['cuotaPlanPago'];
                                        $periodo = NULL;
                                        $cuota = NULL;
                                      } else {
                                        $tipoPago = 'Cuota colegiación';
                                        $idPlanPago = NULL;
                                        $cuotaPP = NULL;
                                        $cuota = $dato['periodo'].'-'.$dato['cuota'];
                                      }
                                  ?>
                                    <tr>
                                        <td><?php echo $idPagoNoRegistrado;?></td>
                                        <td><?php echo $tipoPago;?></td>
                                        <td><?php echo $recibo;?></td>
                                        <td><?php echo $cuota;?></td>
                                        <td><?php echo $cuotaPP;?></td>
                                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaPago);?></td>
                                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaCarga);?></td>
                                        <td><?php echo $lugarPago;?></td>
                                        <td><?php echo $detalle;?></td>
                                        <td>
                                            <a href="tesoreria_pagosnoregistrados_anular.php?idColegiado=<?php echo $idColegiado; ?>&idPago=<?php echo $idPagoNoRegistrado; ?>&accion=2" class="btn btn-danger center-block btn-sm">Anular</a>
                                        </td>
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
                            <div class="<?php echo $resPagosNoRegistrados['clase']; ?>" role="alert">
                                <span class="<?php echo $resPagosNoRegistrados['icono']; ?>" ></span>
                                <span><strong><?php echo $resPagosNoRegistrados['mensaje']; ?></strong></span>
                            </div>
                        </div>
                    <?php
                    }    
                } else {
                    echo 'Error al buecar el colegiao';
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