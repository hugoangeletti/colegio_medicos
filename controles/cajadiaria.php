<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
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
</script>
<?php
$idUsuario = $_SESSION['user_id'];

if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
       <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php
}
$fechaCaja = date('Y-m-d');
//verificar si existe caja abierta del dia, en caso contrario debe abrir una caja y si esta abierta de otra fecha, se debe cerrar esta caja antes de continuar
$continua = TRUE;
$mensaje = '';
$cobranzaHabilitada = TRUE;
$resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
if ($resCajaDiaria['estado']) {
    $cajaDiaria = $resCajaDiaria['datos'];
    if (isset($cajaDiaria)) {
        $idCajaDiaria = $cajaDiaria['idCajaDiaria'];
        $fechaApertura = $cajaDiaria['fechaApertura'];
        $abrirCaja = FALSE;
        if ($fechaCaja > $fechaApertura) {
            $mensaje .= 'EXISTE UNA CAJA ABIERTA CON FECHA <b>'.cambiarFechaFormatoParaMostrar($fechaApertura).'</b>. Cierre y abra una caja con fecha del día actual';
            $abrirCaja = FALSE;
            $fechaCaja = $fechaApertura;
            $cobranzaHabilitada = FALSE;
        }
    } else {
        $mensaje .= 'NO EXISTE UNA CAJA ABIERTA';
        $abrirCaja = TRUE;
        $idCajaDiaria = NULL;        
        $cobranzaHabilitada = FALSE;
    }
} else {
    $mensaje .= 'ERROR AL BUSCAR CAJA DEL DIA';
    $continua = FALSE;
    $cobranzaHabilitada = FALSE;
}
?>

<div class="panel panel-info">
<div class="panel-heading">
    <h4>
        <b>Caja del día <?php echo cambiarFechaFormatoParaMostrar($fechaCaja); ?></b>
        <?php
        if (!$abrirCaja) {
        ?>
            <a href="cajadiaria_cerrar.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-default">Cerrar caja del día</a>
        <?php 
        }
        ?>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <a href="cajadiaria_lista.php" class="btn btn-info">Ver cajas anteriores </a>
    </h4>
</div>
<?php
if ($continua) {
?>
    <div class="panel-body">
        <?php
        if ($abrirCaja) {
        ?> 
            <div class="col-md-12 alert alert-danger" role="alert">
                <span><strong><?php echo $mensaje; ?></strong></span>
            </div>
        <?php 
            if (!isset($idCajaDiaria)) {
            ?>
                <div class="row">
                    <div class="col-md-3">
                        <br>
                        <form method="POST" action="cajadiaria_abrir.php">
                            <button type="submit" class="btn btn-success">Abrir caja del día</button>
                        </form>
                    </div>
                </div>
                <br>
            <?php
            }
        } else {
        ?>
            <div class="row">
                <div class="col-md-10">
                    <?php 
                    if ($cobranzaHabilitada) {
                    ?>
                        <b>Generar recibo: </b>
                        <a href="cajadiaria_colegiacion_recibo.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Colegiación / plan de pagos</a>
                        &nbsp;
                        <a href="cajadiaria_especialistas_listado.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Especialistas</a>
                        &nbsp;
                        <a href="cajadiaria_cursos_recibo.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Cursos</a>
                        &nbsp;
                        <a href="cajadiaria_tipo_pago_recibo.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Por tipo de pago</a>
                        <!--&nbsp;
                        <a href="cajadiaria_firma_recibo.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Certificación de firma</a>-->
                        &nbsp;
                        <a href="cajadiaria_otros_ingresos_recibo.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Personalizado otros ingresos</a>
                        <?php 
                        if ($usuarioLogic->verificarRolUsuario($idUsuario, 77)) {
                         //tiene permiso para condonar deuda
                        ?>
                            &nbsp;
                            <a href="cajadiaria_devolucion_recibo.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Devoluciones</a>
                        <?php 
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
            $resMovimientosCaja = $cajaDiariaLogic->obtenerCajaDiariaMovimientos($idCajaDiaria);
            if ($resMovimientosCaja['estado']) {
            ?>
                <div class="row">&nbsp;</div>
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
                                        <td style="text-align: center;"><?php echo $mail;?></td>
                                        <td style="text-align: center; <?php echo $style; ?>"><?php echo $estadoDetalle;?></td>
                                        <!--<td style="text-align: center;">
                                            <form method="POST" action="cajadiaria_especialistas_expedientes.php">
                                                <button type="submit" class="btn btn-info">Ver expedientes</button>
                                                <input type="hidden" id="listaIdMesaEntrada" name="listaIdMesaEntrada" value="<?php echo $listaIdMesaEntrada; ?>">
                                            </form>
                                        </td>-->
                                        <td>
                                            <a href="cajadiaria_recibo_detalle.php?id=<?php echo $idCajaDiariaMovimiento; ?>" class="btn btn-primary">Ver Detalle</a>
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
                                            if ($estado != 'A') {
                                            ?>
                                                <a href="datosCajaDiaria/anular_recibo.php?id=<?php echo $idCajaDiaria.'_'.$idCajaDiariaMovimiento; ?>" class="btn btn-danger" onclick="return confirmaAnular()">Anular</a>
                                            <?php 
                                            }
                                            if ($_SESSION['user_id'] == 1) {
                                            ?>
                                                <a href="datosCajaDiaria/borrar_recibo.php?id=<?php echo $idCajaDiaria.'_'.$idCajaDiariaMovimiento; ?>" class="btn btn-danger" onclick="return confirmaAnular()">Borrar recibo</a>
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
require_once '../html/footer.php';
