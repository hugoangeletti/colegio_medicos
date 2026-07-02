<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaDetalleCaja').DataTable({
                    "iDisplayLength":25,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmaAnular()
{
    if(confirm('¿Estas seguro de ANULAR este RECIBO?'))
        return true;
    else
        return false;
}

function confirmaEnvio()
{
    if(confirm('¿Estas seguro de ENVIAR RECIBO POR MAIL?'))
        return true;
    else
        return false;
}

</script>
<?php
$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCajaDiaria = $_GET['id'];
    $resCajaDiaria = $cajaDiariaLogic->obtenerCajaDiariaPorId($idCajaDiaria);
    if ($resCajaDiaria['estado']) {
        $cajaDiaria = $resCajaDiaria['datos'];
        $fechaCaja = $cajaDiaria['fechaApertura'];
        $estadoCaja = $cajaDiaria['estado'];
        $estadoCajaDetalle = 'Abierta';
        if ($estadoCaja == 'C') {
            $estadoCajaDetalle = 'Cerrada';
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resRecibo['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCajaDiariaMovimiento";
}

if ($continua) {
?>
    <div class="panel panel-info">
    <div class="panel-heading">
        <h4>
            <b>Caja del día <?php echo cambiarFechaFormatoParaMostrar($fechaCaja); ?> - <?php echo $estadoCajaDetalle; ?></b>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="cajadiaria_lista.php" class="btn btn-info">Volver </a>
        </h4>
    </div>
    <?php
    if ($continua) {
    ?>
        <div class="panel-body">
            <?php
            $resMovimientosCaja = $cajaDiariaLogic->obtenerCajaDiariaMovimientos($idCajaDiaria);
            if ($resMovimientosCaja['estado']) {
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <table  id="tablaDetalleCaja" class="display">
                            <thead>
                                <tr>
                                    <th style="display: none;">Id</th>
                                    <th style="text-align: center;">Comprobante</th>
                                    <th style="text-align: center;">Matricula</th>
                                    <th style="text-align: center;">Asistente</th>
                                    <th style="text-align: center;">Apellido y Nombre</th>
                                    <th style="text-align: center;">Importe</th>
                                    <th style="text-align: center;">Correo Electrónico</th>
                                    <th style="text-align: center;">Estado</th>
                                    <th style="text-align: center;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resMovimientosCaja['datos'] as $dato){
                                    $idCajaDiariaMovimiento = $dato['idCajaDiariaMovimiento'];
                                    $idColegiado = $dato['idColegiado'];
                                    $idAsistente = $dato['idAsistente'];
                                    $matricula = $dato['matricula'];
                                    $apellidoNombre = $dato['apellidoNombre'];
                                    $monto = $dato['monto'];
                                    $tipo = $dato['tipo'];
                                    $numero = $dato['numero'];
                                    $mail = $dato['correoElectronico'];
                                    $estado = $dato['estado'];
                                    if (isset($estado)) {
                                        if ($estado == 'A') {
                                            $estadoDetalle = 'ANULADO';
                                            $style = 'color: red;';
                                        } else {
                                            $estadoDetalle = 'OK';
                                            $style = '';
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td style="display: none;"><?php echo $idCajaDiariaMovimiento; ?></td>
                                        <td style="text-align: center;"><?php echo $tipo.'-'.$numero;?></td>
                                        <td style="text-align: center;"><?php echo $matricula?></td>
                                        <td style="text-align: center;"><?php echo $idAsistente?></td>
                                        <td style="text-align: center;"><?php echo $apellidoNombre;?></td>
                                        <td style="text-align: center;"><?php echo $monto;?></td>
                                        <td style="text-align: center;"><?php if (isset($mail)) { echo $mail; } else { echo '<b>Verificar correo electronico</b>'; }?></td>
                                        <td style="text-align: center;"><?php echo $estadoDetalle;?></td>
                                        <!--<td style="text-align: center;">
                                            <form method="POST" action="cajadiaria_especialistas_expedientes.php">
                                                <button type="submit" class="btn btn-info">Ver expedientes</button>
                                                <input type="hidden" id="listaIdMesaEntrada" name="listaIdMesaEntrada" value="<?php echo $listaIdMesaEntrada; ?>">
                                            </form>
                                        </td>-->
                                        <td>
                                            <a href="cajadiaria_recibo_detalle.php?id=<?php echo $idCajaDiariaMovimiento; ?>&mov=1" class="btn btn-primary">Ver Detalle</a>
                                            <a href="cajadiaria_recibo_imprimir.php?id=<?php echo $idCajaDiariaMovimiento; ?>" class="btn btn-primary" >Imprimir</a>
                                            <?php 
                                            if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 85) && isset($mail) && strtoupper($mail) <> 'NR') {
                                                //si tiene mail registrado da la opcion de envio del mail
                                            ?>
                                                <a href="cajadiaria_recibo_envia_mail.php?id=<?php echo $idCajaDiariaMovimiento; ?>" class="btn btn-primary" onclick="return confirmaEnvio()">Envía mail</a>
                                            <?php
                                            }                                            
                                            ?>
                                            <?php 
                                            if (($estadoCaja == 'C' || $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 78)) && $estado <> 'A') {
                                            ?>
                                                <a href="datosCajaDiaria/anular_recibo.php?id=<?php echo $idCajaDiaria.'_'.$idCajaDiariaMovimiento; ?>&estado=cerrada" class="btn btn-danger" onclick="return confirmaAnular()">Anular</a>
                                            <?php 
                                            }
                                            if ($_SESSION['user_id'] == 1) {
                                            ?>
                                                <a href="datosCajaDiaria/borrar_recibo.php?id=<?php echo $idCajaDiaria.'_'.$idCajaDiariaMovimiento; ?>&estado=cerrada" class="btn btn-danger" onclick="return confirmaAnular()">Borrar PDF</a>
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
                    </div>
                </div>
            <?php
            } else {
            ?>
                <div class="row">&nbsp;</div>
                <div class="<?php echo $resMovimientosCaja['clase']; ?>" role="alert">
                    <span class="<?php echo $resMovimientosCaja['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resMovimientosCaja['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
            ?>
        </div>
    <?php
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
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="cajadiaria.php">
                <button type="submit"  class="btn btn-info" >Volver</button>
            </form>
        </div>
    </div>
<?php
}
require_once '../html/footer.php';
