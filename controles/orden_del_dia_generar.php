<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/ordenDelDiaLogic.php');
$ordenDelDiaLogic = new ordenDelDiaLogic();

$continua = TRUE;

if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php    
}   

if (isset($_POST['idOrdenDia']) && $_POST['idOrdenDia'] <> "") {
    $idOrdenDia = $_POST['idOrdenDia'];
    $resOrden = $ordenDelDiaLogic->obtenerOrdenDelDiaPorId($idOrdenDia);
    if ($resOrden['estado']) {
        $ordenDelDia = $resOrden['datos'];
        $fechaOrden = $ordenDelDia['fecha'];
        $numero = $ordenDelDia['numero'];
        $periodoOrden = $ordenDelDia['periodo'];
        $fechaDesde = $ordenDelDia['fechaDesde'];
        $fechaHasta = $ordenDelDia['fechaHasta'];
        $observaciones = $ordenDelDia['observaciones'];

        $resOrdenDetalle = $ordenDelDiaLogic->obtenerMovimientosParaOrdenDia($fechaDesde, $fechaHasta);
        if ($resOrdenDetalle['estado']) {
            $ordenDidDetalle = $resOrdenDetalle['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resOrdenDetalle['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje = $resOrden['mensaje'];
    }
} else {
    $idOrdenDia = NULL;
    $continua = FALSE;
    $mensaje = "ACCESO ERRONEO";
}

if ($continua) {
?> 
    <div class="alert alert-info"><h4>Generar Orden del día Nº <?php echo $numero.'/'.$periodoOrden; ?></h4></div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-xs-2">&nbsp;</div>
            <div class="col-md-10 text-right">
                <form  method="POST" action="orden_del_dia_generar.php">
                    <button type="submit" class="btn btn-primary" name='agregar' id='name'>Regenerar orden del día </button>
                    <input type="hidden" id="accion" name="accion" value="1">
                </form>
            </div>
        </div>
        <div class="row">&nbsp;</div>

        <div class="col-md-12">
            <?php
            if (sizeof($ordenDidDetalle) > 0) {
            ?>
                <div class="row">
                    <div class="col-md-2"><b>1</b> - Corresponde a la Planilla de Asuntos Internos.</div>
                    <div class="col-md-2"><b>2</b> - Corresponde a la Planilla de Notas Recibidas.</div>
                    <div class="col-md-2"><b>3</b> - Archivado - Descarta el Trámite Definitivamente.</div>
                    <div class="col-md-2"><b>4</b> - Corresponde a la Planilla de Movimientos Matriculares.</div>
                    <div class="col-md-4">Si no selecciona ninguna opción, automáticamente quedará pospuesto para la próxima reunión.</div>
                    <br>
                </div>
                <div class="row">&nbsp;</div>
                            
                <form id="formDetalleOrden" action="agregarOrdenDiaDetalle.php" method="post">
                    <table>
                        <thead>
                            <tr>
                                <th style="display: none;">Id</th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>Fecha de Trámite</th>
                                <th>Tipo de Trámite</th>
                                <th>Colegiado / Remitente</th>
                                <th>Tema / Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($ordenDidDetalle as $dato) {
                                $idMesaEntrada = $dato['idMesaEntrada'];
                                $fechaIngreso = cambiarFechaFormatoParaMostrar($dato['fechaIngreso']);
                                $tipoTramite = $dato['nombreMovimiento'];
                                $apellido = $dato['apellido'];
                                $nombre = $dato['nombre'];
                                $nombreRemitente = $dato['nombreRemitente'];
                                $tema = $dato['tema'];
                                $observaciones = $dato['observaciones'];
                                $colegiadoRemitente = NULL;
                                $temaObservaciones = NULL;
                                if (isset($apellido) && $apellido <> "") {
                                    $colegiadoRemitente = trim($apellido).' '.trim($nombre);
                                }
                                if (isset($nombreRemitente) && $nombreRemitente <> "") {
                                    $colegiadoRemitente = trim($nombreRemitente);
                                }
                                if (isset($tema) && $tema <> "") {
                                    $temaObservaciones = trim($tema);
                                }
                                if (isset($observaciones) && $observaciones <> "") {
                                    $temaObservaciones = trim($observaciones);
                                }
                                ?>
                                <tr>
                                    <td style="display: none;"><?php echo $idMesaEntrada;?></td>
                                    <td>
                                        <input type="checkbox" name="mesaEntrada1[]" class="me1" id="me1_<?php echo $dato['idMesaEntrada'] ?>" data="<?php echo $dato['idMesaEntrada'] ?>" value="<?php echo $dato['idMesaEntrada'] ?>" disabled="disabled" onChange="verCkeck(this)" />
                                    </td>
                                    <td>
                                        <input type="checkbox" name="mesaEntrada2[]" class="me2" id="me2_<?php echo $dato['idMesaEntrada'] ?>" data="<?php echo $dato['idMesaEntrada'] ?>" value="<?php echo $dato['idMesaEntrada'] ?>" <?php if ($dato['idTipoMesaEntrada'] != 1) { ?> checked <?php } else { ?>  disabled="disabled" <?php } ?>/>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="mesaEntrada3[]" class="me3" id="me3_<?php echo $dato['idMesaEntrada'] ?>" data="<?php echo $dato['idMesaEntrada'] ?>" value="<?php echo $dato['idMesaEntrada'] ?>" disabled="disabled" />
                                    </td>
                                    <td>
                                        <input type="checkbox" name="mesaEntrada4[]" class="me4" id="me4_<?php echo $dato['idMesaEntrada'] ?>" data="<?php echo $dato['idMesaEntrada'] ?>" value="<?php echo $dato['idMesaEntrada'] ?>" <?php if ($dato['idTipoMesaEntrada'] == 1) { ?> checked <?php } else { ?>  disabled="disabled" <?php } ?> onChange="verCkeck(this)" />
                                    </td>
                                    <td><?php echo $fechaIngreso;?></td>
                                    <td><?php echo $tipoTramite;?></td>
                                    <td><?php echo $colegiadoRemitente;?></td>
                                    <td><?php echo $temaObservaciones;?></td>
                               </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </form>
            <?php 
            } else {
                echo "No hay detalle para este tipo de trámite";
            }
            ?>
            </div>
        </div>
    </div>
<?php
} else {
?>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>
<?php        
}
?>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-12">
        <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="orden_del_dia_listado.php">
            <button type="submit"  class="btn btn-info" >Volver</button>
        </form>
    </div>
</div>
<?php
require_once '../html/footer.php';