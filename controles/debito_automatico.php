<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/debitoAutomaticoLogic.php');
$debitoAutomaticoLogic = new debitoAutomaticoLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "order": [[ 0, "desc" ]],
            dom: 'T<"clear">lfrtip',
        });
    });
            
function confirmaAnular()
{
    if(confirm('¿Estas seguro de ANULAR este PROCESO?'))
        return true;
    else
        return false;
}
   
</script>

<?php
if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
}   
?> 
<div class="panel panel-info">
<div class="panel-heading"><h4>Débito Automático - Listado de Envios generados</h4></div>
<div class="panel-body">
    <div class="row">
        <?php
        if (isset($_POST['anio']) && $_POST['anio'] != ""){
            $anio = $_POST['anio'];
        } else {
            $anio = date('Y');
        }
        if (isset($_POST['mes']) && $_POST['mes'] != ""){
            $mes = $_POST['mes'];
        } else {
            $mes = date('m');
        }
        if (isset($_POST['tipoDebitoSeleccionado']) && $_POST['tipoDebitoSeleccionado'] != ""){
            $tipoDebitoSeleccionado = $_POST['tipoDebitoSeleccionado'];
        } else {
            $tipoDebitoSeleccionado = "";
        }
        ?>
        <div class="row">
            <div class="col-xs-6">
                <form method="POST" action="debito_automatico.php">
                    <div class="col-xs-3">
                        <label for="anio">Año</label>
                        <select class="form-control" id="anio" name="anio" required onChange="this.form.submit()">
                            <option value="0" selected>Todos</option>
                            <?php
                            $anioDebito = date('Y');
                            while ($anioDebito >= 2024) {
                            ?>
                                <option value="<?php echo $anioDebito; ?>" <?php if($anioDebito == $anio) { echo 'selected'; } ?>><?php echo $anioDebito; ?></option>
                            <?php
                                $anioDebito--;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-3">
                        <label for="anio">Mes</label>
                        <select class="form-control" id="mes" name="mes" required onChange="this.form.submit()">
                            <option value="" selected>Todos</option>
                            <?php
                            $mesDebito = 1;
                            while ($mesDebito <= 12) {
                            ?>
                                <option value="<?php echo rellenarCeros($mesDebito, 2); ?>" <?php if($mesDebito == $mes) { echo 'selected'; } ?>><?php echo obtenerMes($mesDebito); ?></option>
                            <?php
                                $mesDebito++;
                            }
                            ?>
                        </select>                        
                    </div>
                    <div class="col-xs-6">
                        <label for="tipoDebitoSeleccionado">Tipo débito</label>
                        <select class="form-control" id="tipoDebitoSeleccionado" name="tipoDebitoSeleccionado" required onChange="this.form.submit()">
                            <option value="" selected>Todos</option>
                            <option value="<?php echo TARJETA_DEBITO; ?>" <?php if($tipoDebitoSeleccionado == TARJETA_DEBITO) { echo 'selected'; } ?>>TARJETA DEBITO</option>
                            <option value="<?php echo TARJETA_CREDITO; ?>" <?php if($tipoDebitoSeleccionado == TARJETA_CREDITO) { echo 'selected'; } ?>>TARJETA CREDITO</option>
                            <option value="<?php echo CBU; ?>" <?php if($tipoDebitoSeleccionado == CBU) { echo 'selected'; } ?>>CBU</option>
                        </select>
                    </div>
                </form>    
            </div>
            <div class="col-xs-3"></div>
            <div class="col-xs-3">
                <?php
                if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 123)){
                ?>
                    <a href="debito_automatico_form.php" class="btn btn-primary">Generar lote envío Débito</a>
                <?php 
                } 
                ?>
            </div>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $mes = rellenarCeros($mes, 2);
    $resEnvios = $debitoAutomaticoLogic->obtenerDebitoAutomaticoGenerados($anio, $mes, $tipoDebitoSeleccionado);
    if ($resEnvios['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Tipo débito</th>
                    <th>Fecha proceso</th>
                    <th>Fecha Vencimiento</th>
                    <th style="text-align: center;">Importe total</th>
                    <th>Archivo</th>
                    <th style="text-align: center; width: 200px;">Acciones</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resEnvios['datos'] as $dato) 
                  {
                      $idDebitoAutomatico = $dato['idDebitoAutomatico'];
                      $tipoDebito = $dato['tipoDebito'];
                      $fechaProceso = $dato['fechaProceso'];
                      $fechaDebito = $dato['fechaDebito'];
                      $totalDebitar = $dato['totalDebitar'];
                      $nombreArchivo = $dato['nombreArchivo'];
                      $pathArchivo = $dato['pathArchivo'];
                      $borrado = $dato['borrado'];
                      if ($borrado == 1) {
                        $estado = 'BORRADO';
                      } else {
                        $estado = "ACTIVO";
                      }

                  ?>
                    <tr>
                        <td><?php echo $idDebitoAutomatico;?></td>
                        <td><?php echo $debitoAutomaticoLogic->obtenerTipoDebito($tipoDebito);?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar(substr($fechaProceso, 0, 10));?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaDebito);?></td>
                        <td style="text-align: right;"><?php echo $totalDebitar;?></td>
                        <td><?php echo $nombreArchivo;?></td>
                        <td style="text-align: center;">
                            <?php 
                            if($borrado == 0) { 
                            ?>
                                <?php
                                if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 123)){
                                ?>
                                <div class="btn-group">
                                  <button type="button" class="btn btn-info dropdown-toggle"
                                          data-toggle="dropdown">
                                    Archivos <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="datosDebitoAutomatico/genera_archivos.php?id=<?php echo $idDebitoAutomatico; ?>" class="btn btn-info">Generar archivos</a>
                                    </li>
                                    <li>
                                        <a href="datosDebitoAutomatico/descargar_archivos.php?id=<?php echo $idDebitoAutomatico ?>" class="btn btn-default">Descargar Archivo</a>
                                    </li>
                                    <li>
                                        <a href="datosDebitoAutomatico/borrar_archivos.php?id=<?php echo $idDebitoAutomatico; ?>" class="btn btn-warning" onclick="return confirmaAnular()">Borrar proceso</a>
                                    </li>
                                  </ul>
                                </div>
                                <?php 
                                }
                                ?>
                            <?php 
                            } else {
                                echo '<b>BORRADO</b>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($borrado == 0) { 
                            ?>
                                <a href="debito_automatico_detalle.php?id=<?php echo $idDebitoAutomatico; ?>&ori=<?php echo $anio.'_'.$mes.'_'.$tipoDebitoSeleccionado; ?>" class="btn btn-default">Matrículas</a>
                            <?php
                            }
                            ?>
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
        <div class="<?php echo $resEnvios['clase']; ?>" role="alert">
            <span class="<?php echo $resEnvios['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resEnvios['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';