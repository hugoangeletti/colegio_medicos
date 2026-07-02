<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaColegiacion').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 0, "asc" ]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'                    
                });
    }
);
</script>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-10">
                <h4><b>Caja Diaria - Especialistas pendiente de pago</b></h4>
            </div>
            <div class="col-md-2 text-right">
                <form id="formVolver" name="formVolver" method="POST" action="cajadiaria.php">
                    <button type="submit"  class="btn btn-info" >Volver</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-9">&nbsp;</div>
            <div class="col-md-3">
                <!--
                <form method="POST" action="colegiacion_anual_form.php">
                    <div align="right">
                        <button type="submit" class="btn btn-primary">Nuevo Valor y Vencimiento</button>
                        <input type="hidden" id="accion" name="accion" value="1">
                    </div>
                </form>
            -->
            </div>
        </div>
        <?php
        $resDeuda = $mesaEntradaEspecialistaLogic->obtenerDeudaEspecialistas();
        if ($resDeuda['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaColegiacion" class="display">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Matricula</th>
                                <th style="text-align: center;">Apellido y Nombre</th>
                                <th style="text-align: center;">Importe</th>
                                <th style="text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resDeuda['datos'] as $dato){
                                $listaIdMesaEntrada = $dato['listaIdMesaEntrada'];
                                $idColegiado = $dato['idColegiado'];
                                $matricula = $dato['matricula'];
                                $apellidoNombre = $dato['apellidoNombre'];
                                $total = $dato['total'];
                                ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $matricula;?></td>
                                    <td style="text-align: center;"><?php echo $apellidoNombre;?></td>
                                    <td style="text-align: center;"><?php echo $total;?></td>
                                    <!--<td style="text-align: center;">
                                        <form method="POST" action="cajadiaria_especialistas_expedientes.php">
                                            <button type="submit" class="btn btn-info">Ver expedientes</button>
                                            <input type="hidden" id="listaIdMesaEntrada" name="listaIdMesaEntrada" value="<?php echo $listaIdMesaEntrada; ?>">
                                        </form>
                                    </td>-->
                                    <td style="text-align: center;">
                                        <form method="POST" action="cajadiaria_especialistas_recibo.php">
                                            <button type="submit" class="btn btn-primary">Generar recibo</button>
                                            <input type="hidden" id="listaIdMesaEntrada" name="listaIdMesaEntrada" value="<?php echo $listaIdMesaEntrada; ?>">
                                            <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                        </form>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resDeuda['clase']; ?>" role="alert">
                <span class="<?php echo $resDeuda['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resDeuda['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
</div>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
