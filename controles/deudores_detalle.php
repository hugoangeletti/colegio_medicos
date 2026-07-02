<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/deudoresLogic.php');
require_once ('../dataAccess/usuarioLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "asc" ]],
            //"order": [[ 2, "desc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });   

function confirmaAnular(accion)
{
    if(confirm('¿Estas seguro de ' + accion + ' registro?'))
        return true;
    else
        return false;
}

</script>

<?php
$continua = TRUE;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idDeudores = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= "Ingreso Incorrecto - falta idDeudores";
}
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Listado de Deudores</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            $deudoresLogic = new deudoresLogic();
            $resDeudores = $deudoresLogic->obtenerListadoDeudoresPorId($idDeudores);
            if ($resDeudores['estado']) {
                $deudores = $resDeudores['datos'];
                $tipo_filtro = $deudoresLogic->tipoFiltro($deudores['tipo_filtro']);
                $periodo_limite = $deudores['periodo_limite'];
                $cuotas_adeudadas = $deudores['cuotas_adeudadas'];
            } else {
                $mensaje .= $deudores['mensaje'];
                $continua = FALSE;
            }
            if ($continua) {
            ?>
                <div class="row">
                    <div class="col-xs-4">
                        <label>Tipo filtro: </label><?php echo $tipo_filtro; ?>
                    </div>
                    <div class="col-xs-2">
                        <label>Período límite: </label><?php echo $periodo_limite; ?>
                    </div>
                    <div class="col-xs-2">
                        <label>Cuotas adeudadas: </label><?php echo $cuotas_adeudadas; ?>
                    </div>
                    <div class="col-xs-4 text-rigth">
                        <a href="deudores_listado.php" class="btn btn-info" >Volver</a>
                    </div>
                </div>
                <?php
                $resColegiados = $deudoresLogic->obtenerDetalleListadoDeudores($idDeudores);
                if ($resColegiados['estado']){
                ?>
                    <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Id</th>
                                <th style="text-align: center;">Matrícula</th>
                                <th style="text-align: left;">Apellido y Nombre</th>
                                <th style="text-align: center;">Cuotas adeudadas</th>
                                <th style="text-align: center;">Períodos adeudadss</th>
                                <th style="width: 400px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($resColegiados['datos'] as $dato) {
                            $idDeudoresColegiado = $dato['idDeudoresColegiado'];
                            $matricula = $dato['matricula'];
                            $apellidoNombre = $dato['apellidoNombre'];
                            $cuotas_adeudadas = $dato['cuotas_adeudadas'];
                            $periodos_adeudados = $dato['periodos_adeudados'];
                            ?>
                            <tr style="text-align: center;">
                        	   <td><?php echo $idDeudoresColegiado;?></td>
                               <td><?php echo $matricula;?></td>
                               <td style="text-align: left;"><?php echo $apellidoNombre;?></td>
                               <td><?php echo $cuotas_adeudadas;?></td>
                               <td><?php echo $periodos_adeudados;?></td>
                               <td>
                                    <a href="#" class="btn btn-info">Accion</a>
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
                    <div class="<?php echo $resColegiados['clase']; ?>" role="alert">
                        <span class="<?php echo $resColegiados['icono']; ?>" ></span>
                        <span><strong><?php echo $resColegiados['mensaje']; ?></strong></span>
                    </div>
                <?php
                } 
            } else {
            ?>
                <div class="row">&nbsp;</div>
                <div class="alert alert-danger" role="alert">
                    <span><strong><?php echo $mensaje; ?></strong></span>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';