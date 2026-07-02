<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/agremiacionesDebitoLogic.php');
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":100,
            "order": [[ 2, "asc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });   

function confirmaProceso()
{
    if(confirm('¿Estas seguro de generar el lote?'))
        return true;
    else
        return false;
}

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
    <div class="panel-heading"><h4><b>Matrículas por débito de Agremiaciones</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            if (isset($_POST['idLugarPago']) && $_POST['idLugarPago'] != "") {
                $idLugarPago = $_POST['idLugarPago'];
            } else {
                $idLugarPago = '';
            }

            if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] != "") {
                $periodoSeleccionado = $_POST['periodoSeleccionado'];
            } else {
                $periodoSeleccionado = PERIODO_ACTUAL;
                $periodo = PERIODO_ACTUAL;
                $periodoAnterior = PERIODO_ACTUAL - 1;
            }

            if ($periodoSeleccionado == PERIODO_ACTUAL) {
                $periodo = $periodoSeleccionado;
                $periodoAnterior = $periodoSeleccionado - 1;
            } else {
                $periodo = $periodoSeleccionado + 1;
                $periodoAnterior = $periodoSeleccionado;
            }
            ?>
            <div class="row">
                <div class="col-xs-6">
                    <form method="POST" action="debito_agremiaciones.php">
                        <div class="col-xs-3">
                            <select class="form-control" id="periodoSeleccionado" name="periodoSeleccionado" required onChange="this.form.submit()">
                                <option value="<?php echo $periodoAnterior; ?>" <?php if($periodoAnterior == $periodoSeleccionado) { echo 'selected'; } ?>><?php echo $periodoAnterior; ?></option>
                                <option value="<?php echo $periodo; ?>" <?php if($periodo == $periodoSeleccionado) { echo 'selected'; } ?>><?php echo $periodo; ?></option>
                            </select>
                        </div>
                        <div class="col-xs-6">
                            <select class="form-control" id="idLugarPago" name="idLugarPago" required onChange="this.form.submit()">
                                <option value="" selected>Seleccione Entidad</option>
                                <?php
                                $resLugares = $lugarPagoLogic->obtenerLugaresDePago();
                                if ($resLugares['estado']) {
                                    foreach ($resLugares['datos'] as $lugarPago) {
                                        if ($lugarPago['codigoCaja'] == "AGRE") {
                                            ?>
                                            <option value="<?php echo $lugarPago['id']; ?>" <?php if($lugarPago['id'] == $idLugarPago) { echo 'selected'; } ?>><?php echo $lugarPago['nombre']; ?></option>
                                        <?php
                                        }
                                    }
                                } 
                                ?>
                            </select>
                        </div>
                        <div class="col-xs-3">&nbsp;</div>
                    </form>    
                </div>
                <div class="col-xs-3"></div>
                <div class="col-xs-3">
                    <?php
                    if (isset($idLugarPago) && $idLugarPago <> "") {
                    ?>
                        <a href="debito_agremiaciones_form.php?idLugarPago=<?php echo $idLugarPago;?>&periodo=<?php echo $periodoSeleccionado; ?>" class="btn btn-primary" >Agregar Matrícula</a>
                        <a href="cobranza_lotes_form.php?accion=1&idLugarPago=<?php echo $idLugarPago;?>&anio=<?php echo $periodoSeleccionado; ?>&debito_agremiaciones" class="btn btn-success" >Generar lote de pagos</a>
                    <?php 
                    }
                    ?>
                </div>
            </div>
            <?php
            if (isset($idLugarPago) && $idLugarPago <> "") {
                $agremiacionesDebitoLogic = new agremiacionesDebitoLogic();
                $resDebito = $agremiacionesDebitoLogic->obtenerColegiadoPorAgremiacion($idLugarPago, $periodoSeleccionado);
                if ($resDebito['estado'] && sizeof($resDebito['datos']) > 0){
                ?>
                    <br>
                        <table id="tablaOrdenada" class="display">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Matrícula</th>
                                    <th>Apellido y Nombre</th>
                                    <th>Estado matricular</th>
                                    <th>Observación</th>
                                    <th style="width: 30px">Borrar</th>
                                </tr>
                            </thead>
                      <tbody>
                          <?php
                          foreach ($resDebito['datos'] as $dato) {
                              $idAgremiacionesDebito = $dato['idAgremiacionesDebito'];
                              $idColegiado = $dato['idColegiado'];
                              $matricula = $dato['matricula'];
                              $estadoActual = $dato['estadoActual'];
                              $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                              $tipoMovimientoDetalle = $dato['tipoMovimientoDetalle'];
                              $estadoTipoMovimiento = $dato['estadoTipoMovimiento'];
                              $observacion = "";
                              switch ($estadoTipoMovimiento) {
                                  case 'A':
                                      $observacion = "ACTIVO";
                                      break;
                                  
                                  case 'I':
                                      $observacion = "NO ABONA CUOTAS";
                                      break;
                                  
                                  case 'C':
                                  case 'J':
                                  case 'F':
                                      $observacion = "DE BAJA";
                                      break;
                                  
                                  default:
                                      $observacion = "";
                                      break;
                              }
                              $conDebitoAutomatico = $dato['conDebitoAutomatico'];
                              if (isset($conDebitoAutomatico) && $conDebitoAutomatico <> "") {
                                $observacion .= ' - '.$conDebitoAutomatico;
                              }
                              ?>
                            <tr>
                        	   <td><?php echo $idAgremiacionesDebito;?></td>
                               <td><?php echo $matricula;?></td>
                               <td><?php echo $apellidoNombre;?></td>
                               <td><?php echo $tipoMovimientoDetalle;?></td>
                               <td><?php echo $observacion;?></td>
                               <td style="text-align: center;">
                                    <form id="datosDebitoAgremiacion" name="datosDebitoAgremiacion" method="POST" action="datosDebitoAgremiacion\abm_debito_agremiaciones.php">
                                        <button type="submit" name='confirma' id='confirma' class="btn btn-primary" onclick="return confirmaAnular()">Borrar</button>
                                        <input type="hidden" name="idAgremiacionesDebito" id="idAgremiacionesDebito" value="<?php echo $idAgremiacionesDebito; ?>" />
                                        <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>" />
                                        <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>" />
                                    </form>
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
                    <div class="row">&nbsp;</div>
                    <div class="<?php echo $resDebito['clase']; ?>" role="alert">
                        <span class="<?php echo $resDebito['icono']; ?>" ></span>
                        <span><strong><?php echo $resDebito['mensaje']; ?></strong></span>
                    </div>
                <?php
                }    
            }
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';