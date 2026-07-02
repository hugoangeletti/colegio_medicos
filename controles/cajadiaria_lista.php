<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();

$fechaCaja = date('Y-m-d');
//verificar si existe caja abierta del dia, en caso contrario debe abrir una caja y si esta abierta de otra fecha, se debe cerrar esta caja antes de continuar
$continua = TRUE;
$mensaje = '';
if (isset($_POST['anioCajas']) && $_POST['anioCajas'] <> "") {
    $anioCajas = $_POST['anioCajas'];
} else {
    $anioCajas = date('Y');    
}

?>
<script>
$(document).ready(
    function () {
                $('#tablaCajas').DataTable({
                    "iDisplayLength":10,
                    "order": [[ 0, "desc" ]],
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
        <h4><b>Cajas Diarias</b></h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <label>Seleccione el año: </label>
                <form method="POST" action="cajadiaria_lista.php">
                    <select class="form-control" id="anioCajas" name="anioCajas" required onChange="this.form.submit()">
                        <?php
                        $anio = date('Y');
                        while ($anio >= 2007) {
                        ?>
                            <option value="<?php echo $anio; ?>" <?php if($anio == $anioCajas) { echo 'selected'; } ?>><?php echo $anio; ?></option>
                        <?php
                            $anio--;
                        }
                        ?>
                    </select>
                </form>
            </div>
            <div class="col-md-3">
                <?php 
                //si no hay caja abierta, muestro el boton para abrir
                $resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
                if ($resCajaDiaria['estado']) {
                    if (!empty($resCajaDiaria['datos'])){
                        $abrirCaja = FALSE;
                    } else {
                        $abrirCaja = TRUE;
                    }
                } else {
                        $abrirCaja = FALSE;
                }
                if ($abrirCaja) {
                ?>
                    <br>
                    <form method="POST" action="cajadiaria_abrir.php">
                        <button type="submit" class="btn btn-success">Abrir caja del día</button>
                    </form>
                <?php 
                } 
                ?>
            </div>
        </div>

        <?php
        $resCajaDiaria = $cajaDiariaLogic->obtenerCajasDiarias($anioCajas);
        if ($resCajaDiaria['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaCajas" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th style="text-align: center;">Fecha</th>
                                <th style="text-align: center;">Total Recaudación</th>
                                <th style="text-align: center;">Estado</th>
                                <th style="text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resCajaDiaria['datos'] as $dato){
                                $idCajaDiaria = $dato['idCajaDiaria'];
                                $fechaApertura = $dato['fechaApertura'];
                                $horaApertura = $dato['horaApertura'];
                                $totalRecaudacion = $dato['totalRecaudacion'];
                                $estado = $dato['estado'];
                                
                                switch ($estado) {
                                    case 'A':
                                        $detalleEstado = "Abierta";
                                        break;
                                    
                                    case 'B':
                                        $detalleEstado = "Anulada";
                                        break;
                                    
                                    case 'C':
                                        $detalleEstado = "Cerrada";
                                        break;
                                    
                                    default:
                                        $detalleEstado = "";
                                        break;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $idCajaDiaria; ?></td>
                                    <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaApertura); ?></td>
                                    <td style="text-align: center;"><?php echo $totalRecaudacion; ?></td>
                                    <td style="text-align: center;"><?php echo $detalleEstado;?></td>
                                    <td style="width: 500px;">
                                        <div class="row">
                                            <?php 
                                            if ($estado == 'A') {
                                            ?>
                                                <div class="col-md-4 text-center">
                                                    <a href="cajadiaria.php" class="btn btn-primary">Ver recibos</a>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    <a href="cajadiaria_cerrar.php?id=<?php echo $idCajaDiaria; ?>&lis=1" class="btn btn-primary">Cerrar caja del día</a>
                                                </div>
                                            <?php
                                            } else {
                                            ?>
                                                <div class="col-md-4 text-center">
                                                    <a href="cajadiaria_movimientos.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Ver recibos</a>
                                                </div>                                            
                                                <div class="col-md-4 text-center">
                                                    <a href="cajadiaria_movimientos_imprimir.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary" target="_BLANK">Imprimir caja</a>
                                                </div>                                            
                                                <div class="col-md-4 text-center">
                                                    <a href="cajadiaria_movimientos_resumen_cuenta.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary" target="_BLANK">Imprimir resumen</a>
                                                </div>                                            
                                            <?php
                                            }
                                            ?>
                                        </div>
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
            <div class="<?php echo $resCajaDiaria['clase']; ?>" role="alert">
                <span class="<?php echo $resCajaDiaria['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resCajaDiaria['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
    <div class="col-md-3">
        <form method="POST" action="cajadiaria.php">
            <button type="submit" class="btn btn-success">Volver a caja del día</button>
        </form>
    </div>
</div>
<?php
require_once '../html/footer.php';
