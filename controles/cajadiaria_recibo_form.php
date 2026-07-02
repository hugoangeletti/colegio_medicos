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
$resCajaDiaria = $cajaDiariaLogic->obtenerCajaAbierta();
if ($resCajaDiaria['estado']) {
    $idCajaDiaria = $resCajaDiaria['datos']['idCajaDiaria'];
    $fechaApertura = $resCajaDiaria['datos']['fechaApertura'];
    $abrirCaja = FALSE;
    if ($fechaCaja > $fechaApertura) {
        $mensaje .= 'EXISTE UNA CAJA ABIERTA CON FECHA <b>'.cambiarFechaFormatoParaMostrar($fechaApertura).'</b>. Cierre y abra una caja con fecha del día actual';
        $abrirCaja = TRUE;
        $idCajaDiaria = NULL;
    }
} else {
    $mensaje .= 'ERROR AL BUSCAR CAJA DEL DIA';
    $continua = FALSE;
}
?>

<?php
if ($continua) {
    if ($abrirCaja) {
    ?> 
        <div class="row">&nbsp;</div>
        <div class="alert alert-danger" role="alert">
            <span><strong><?php echo $mensaje; ?></strong></span>
        </div>        
    <?php 
    }
?>
    <div class="panel panel-info">
    <div class="panel-heading">
        <h4><b>Caja del día <?php echo cambiarFechaFormatoParaMostrar($fechaCaja); ?></b></h4>
    </div>
    <div class="panel-body">
        <?php 
        if (isset($idColegiado)) {
        ?>
            <div class="row">
                <div class="col-md-2">
                    <form method="POST" action="cajadiaria_especialistas_recibo.php">
                        <div align="right">
                            <button type="submit" class="btn btn-primary">Especialistas</button>
                            <input type="hidden" id="accion" name="accion" value="1">
                            <input type="hidden" id="accion" name="accion" value="<?php echo $idColegiado; ?>">
                        </div>
                    </form>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">
                <div class="col-md-12 text-center">
                    <h5>Seleccione al colegiado/a</h5>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
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
                            <input type="hidden" name="origen" id="origen" value="nuevo" />
                        </div>
                    </div>
                </form>
            </div>
        <?php 
        }
        ?>
        <?php
        $resMovimientosCaja = $cajaDiariaLogic->obtenerCajaDiariaMovimientos($idCajaDiaria);
        if ($resMovimientosCaja['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaColegiacion" class="display">
                        <thead>
                            <tr>
                                <th style="display: none;">Id</th>
                                <th style="text-align: center;">Comprobante</th>
                                <th style="text-align: center;">Matricula / Asistente</th>
                                <th style="text-align: center;">Apellido y Nombre</th>
                                <th style="text-align: center;">Importe</th>
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
                                $estado = $dato['estado'];
                                ?>
                                <tr>
                                    <td style="display: none;"><?php echo $idCajaDiariaMovimiento; ?></td>
                                    <td style="text-align: center;"><?php echo $tipo.'-'.$numero;?></td>
                                    <td style="text-align: center;"><?php echo $matricula.''.$idAsistente?></td>
                                    <td style="text-align: center;"><?php echo $apellidoNombre;?></td>
                                    <td style="text-align: center;"><?php echo $monto;?></td>
                                    <td style="text-align: center;"><?php echo $estado;?></td>
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
            <div class="<?php echo $resMovimientosCaja['clase']; ?>" role="alert">
                <span class="<?php echo $resMovimientosCaja['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resMovimientosCaja['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
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