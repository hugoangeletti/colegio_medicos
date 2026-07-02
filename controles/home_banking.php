<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/homeBankingLogic.php');
$homeBankingLogic = new homeBankingLogic();
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
<div class="panel-heading"><h4>HomeBanking - Listado de Envios generados</h4></div>
<div class="panel-body">
    <div class="row">
        <?php
        if (isset($_POST['anio']) && $_POST['anio'] != ""){
            $anio = $_POST['anio'];
        } else {
            $anio = date('Y');
        }
        ?>
        <div class="row">
            <div class="col-xs-6">
                <form method="POST" action="home_banking.php">
                    <div class="col-xs-3">
                        <select class="form-control" id="anio" name="anio" required onChange="this.form.submit()">
                            <option value="0" selected>Todos</option>
                            <?php
                            $anioDebito = date('Y');
                            while ($anioDebito >= 2023) {
                            ?>
                                <option value="<?php echo $anioDebito; ?>" <?php if($anioDebito == $anio) { echo 'selected'; } ?>><?php echo $anioDebito; ?></option>
                            <?php
                                $anioDebito--;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-6">
                    </div>
                    <div class="col-xs-3">&nbsp;</div>
                </form>    
            </div>
            <div class="col-xs-3"></div>
            <div class="col-xs-3">
                <?php
                if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 124)){
                ?>
                    <a href="home_banking_form.php" class="btn btn-primary">Generar lote envío HomeBanking</a>
                <?php 
                }
                ?>
            </div>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resEnvios = $homeBankingLogic->obtenerHomaBankingGenerados($anio);
    if ($resEnvios['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Fecha proceso</th>
                    <th>Período procesado</th>
                    <th>Fecha Vencimiento</th>
                    <th>Importe total</th>
                    <th>Codigo</th>
                    <th>Archivo Control</th>
                    <th>Archivo Refresh</th>
                    <th>Archivo PagoMisCuentas</th>
                    <th style="text-align: center; width: 200px;">Acciones / Detalle</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resEnvios['datos'] as $dato) 
                  {
                      $idHomeBankingArchivo = $dato['idHomeBankingArchivo'];
                      $fechaProceso = $dato['fechaProceso'];
                      $periodoProceso = $dato['periodoProceso'];
                      $fechaVencimiento = $dato['fechaPrimerVencimiento'];
                      $importeTotal = $dato['importe'];
                      $codigo = $dato['codigo'];
                      $control = $dato['control'];
                      $refresh = $dato['refresh'];
                      $pagoMisCuentas = $dato['pagoMisCuentas'];
                      $pathArchivo = $dato['pathArchivo'];
                      $borrado = $dato['borrado'];
                  ?>
                    <tr>
                        <td><?php echo $idHomeBankingArchivo;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar(substr($fechaProceso, 0, 10));?></td>
                        <td><?php echo $periodoProceso;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaVencimiento);?></td>
                        <td><?php echo $importeTotal;?></td>
                        <td><?php echo $codigo;?></td>
                        <td><?php echo $control;?></td>
                        <td><?php echo $refresh;?></td>
                        <td><?php echo $pagoMisCuentas;?></td>
                        <td>
                            <?php 
                            if($borrado == 0) { 
                            ?>
                                <?php
                                if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 124)){
                                ?>
                                <div class="btn-group">
                                  <button type="button" class="btn btn-info dropdown-toggle"
                                          data-toggle="dropdown">
                                    Archivos <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="datosHomeBanking/genera_archivos.php?id=<?php echo $idHomeBankingArchivo; ?>" class="btn btn-info">Generar archivos</a>
                                    </li>
                                    <li>
                                        <a href="datosHomeBanking/descargar_archivos.php?id=<?php echo $idHomeBankingArchivo ?>&tipo=control&origen=LINK" class="btn btn-default">Descargar CONTROL</a>
                                    </li>
                                    <li>
                                        <a href="datosHomeBanking/descargar_archivos.php?id=<?php echo $idHomeBankingArchivo ?>&tipo=refresh&origen=LINK" class="btn btn-default">Descargar REFRESH</a>
                                    </li>
                                    <li>
                                        <a href="datosHomeBanking/descargar_archivos.php?id=<?php echo $idHomeBankingArchivo ?>&origen=PMC" class="btn btn-default">Descargar PagoMisCuentas</a>
                                    </li>
                                    <li>
                                        <a href="datosHomeBanking/borrar_archivos.php?id=<?php echo $idHomeBankingArchivo; ?>" class="btn btn-warning" onclick="return confirmaAnular()">Borrar proceso</a>
                                    </li>
                                  </ul>
                                </div>
                                <?php 
                                }
                                ?>
                                <a href="home_banking_detalle.php?id=<?php echo $idHomeBankingArchivo; ?>&anio=<?php echo $anio; ?>" class="btn btn-default">Matrículas</a>
                            <?php 
                            } else {
                                echo '<b>BORRADO</b>';
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