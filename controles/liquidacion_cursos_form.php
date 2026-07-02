<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
$continua = TRUE;
$mensaje = "";
if (isset($_GET['agregar'])) {
    $accion = 'agregar';
    $id_liquidacion = NULL;
    $id_curso = NULL;
} else {
    if (isset($_GET['editar'])) {
        $accion = 'editar';
        if (isset($_GET['id']) && $_GET['id']) {
            $id_liquidacion = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta id_liquidacion_cursos - ";
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion - ";
    }
}
?>
<div class="panel panel-info">
    <?php
    $cursos_pdo = new cursos_pdo();
    if ($continua) {
        if ($accion == 'editar') {
            $resLiquidacion = $cursos_pdo->obtenerLiquidacionPorId($id_liquidacion);
            if ($resLiquidacion['estado']) {
                $liquidacion = $resLiquidacion['datos'];
            } else {
                $mensaje .= $resLiquidacion['mensaje'];
                $continua = FALSE;
            }
        } else {
            $fecha_cobranza = date('Y-m-d');
            $periodo_liquidacion = date('Y-m', strtotime("-1 month"));
        }
    }

    if ($continua) {
    ?>
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Generar liquidación de cursos</h4>
                </div>
                <div class="col-md-3 text-left">
                    <a href="liquidacion_cursos_listado.php" class="btn btn-primary">Volver</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php 
            if ($_POST) {
                //muestro los pagos registrados
                $id_curso = $_POST['idCurso'];
                $anio_periodo = $_POST['anio_periodo'];
                $mes_periodo = $_POST['mes_periodo'];
                $fecha_cobranza = $_POST['fecha_cobranza'];
                $criterio_liquidacion = $_POST['criterio_liquidacion'];

                if (isset($id_curso) && $id_curso <> '0') {
                    $resCurso = $cursos_pdo->obtenerCursoPorId($id_curso);
                    if ($resCurso['estado']) {
                        $titulo = 'Curso: '.$resCurso['datos']['titulo'];
                        $valorCuotaLiquidacion= $resCurso['datos']['valorCuotaLiquidacion'];
                        $porcentajeRetencionColegio = $resCurso['datos']['porcentajeRetencionColegio'];
                    }
                    $resPagos = $cursos_pdo->obtenerDetalleCobranzaPorPeriodoFechaPago($id_curso, $fecha_cobranza, $anio_periodo, $mes_periodo, $criterio_liquidacion);
                } else {
                    //procesa todos los cursos
                    $resPagos = $cursos_pdo->obtenerDetalleCobranzaPorCursoPeriodoFechaPago($fecha_cobranza, $anio_periodo, $mes_periodo, $criterio_liquidacion);
                    $titulo = 'PARA TODOS LOS CURSOS.';
                }

                if ($resPagos['estado']) {
                ?>
                    <form action="datosCurso/abm_liquidacion_cursos.php?agregar" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $titulo; ?>
                                <input type="hidden" name="id_curso" id="id_curso" value="<?php echo $id_curso; ?>">
                            </div>
                            <div class="col-md-2">
                                Criterio Liquidación: <?php echo $criterio_liquidacion; ?>
                                <input type="hidden" name="criterio_liquidacion" id="criterio_liquidacion" value="<?php echo $criterio_liquidacion; ?>">
                            </div>
                            <div class="col-md-2">
                                Fecha limite cobranza: <?php echo cambiarFechaFormatoParaMostrar($fecha_cobranza); ?>
                                <input type="hidden" name="fecha_cobranza" id="fecha_cobranza" value="<?php echo $fecha_cobranza; ?>">
                            </div>
                            <div class="col-md-2">
                                Período a liquidar: <?php echo $anio_periodo.'-'.$mes_periodo; ?>
                                <input type="hidden" name="anio_periodo" id="anio_periodo" value="<?php echo $anio_periodo; ?>">
                                <input type="hidden" name="mes_periodo" id="mes_periodo" value="<?php echo $mes_periodo; ?>">
                            </div>
                        </div>
                        <?php 
                        //si se selecciono un curso
                        if ($id_curso <> '0') {
                        ?>
                            <table id="pagos" class="table">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; width: 80px;">
                                            <input type="checkbox" id="seleccionarTodos" onclick="seleccionarTodo(this)">
                                            <br><small>Todos</small>
                                        </th>
                                        <th style="text-align: center;">Asistente</th>
                                        <th style="text-align: center;">Cuota</th>
                                        <th style="text-align: center;">Fecha Vencimiento</th>
                                        <th style="text-align: right;">Importe cobrado</th>
                                        
                                        <!-- CAMBIO: Ocultar la cabecera si es null o vacío -->
                                        <?php if (!empty($valorCuotaLiquidacion)) { ?>
                                            <th style="text-align: right;">Importe base</th>
                                        <?php } ?>
                                        
                                        <th style="text-align: center;">Fecha Pago</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resPagos['datos'] as $pago) { ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <input type="checkbox" 
                                                   name="pagos_seleccionados[]" 
                                                   value="<?php echo $pago['idCursosAsistenteCuotas']; ?>" 
                                                   class="check-pago"
                                                   data-importe="<?php echo $pago['importe']; ?>"
                                                   data-importe-base="<?php echo $pago['importe_base']; ?>"
                                                   onclick="actualizarTotal()"
                                                   checked> <!-- Atributo checked para que venga marcado -->
                                        </td>
                                        <td style="text-align: left;"><?php echo $pago['apellidoNombre']; ?></td>
                                        <td style="text-align: center;"><?php echo $pago['cuota']; ?></td>
                                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($pago['fechaVencimiento']); ?></td>
                                        <td style="text-align: right;">$ <?php echo number_format($pago['importe'], 2, ',', '.'); ?></td>
                                        
                                        <!-- CAMBIO: Ocultar la celda si es null o vacío -->
                                        <?php if (!empty($valorCuotaLiquidacion)) { ?>
                                            <td style="text-align: right;">$ <?php echo number_format($pago['importe_base'], 2, ',', '.'); ?></td>
                                        <?php } ?>
                                        
                                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($pago['fechaPago']); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        <?php 
                        } else {
                            //si es para todos los cursos
                            ?>
                            <table id="pagos_cursos" class="table">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; width: 80px;">
                                            <input type="checkbox" id="seleccionarTodos" onclick="seleccionarTodo_Cursos(this)">
                                            <br><small>Todos</small>
                                        </th>
                                        <th style="text-align: left;">Curso</th>
                                        <th style="text-align: right;">Importe</th>
                                        <th style="text-align: center;">Acción</th> <!-- Nueva Columna -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resPagos['datos'] as $pago) { ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <input type="checkbox" 
                                                   name="cursos_seleccionados[]" 
                                                   value="<?php echo $pago['idCursos']; ?>" 
                                                   class="check-pago"
                                                   data-importe="<?php echo $pago['importe']; ?>"
                                                   onclick="actualizarTotal()"
                                                   checked>
                                        </td>
                                        <td style="text-align: left;"><?php echo $pago['titulo']; ?></td>
                                        <td style="text-align: right;">$ <?php echo number_format($pago['importe'], 2, ',', '.'); ?></td>
                                        <td style="text-align: center;">
                                            <!-- Botón por fila -->
                                            <button type="button" 
                                                    class="btn btn-info btn-xs" 
                                                    onclick="verDetalleCurso(<?php echo $pago['idCursos']; ?>)"
                                                    title="Ver detalle de cobro">
                                                <i class="glyphicon glyphicon-search"></i> Detalle
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php                            
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-6">
                                <div class="well">
                                    <h4>Resumen de Liquidación</h4>
                                    
                                    <!-- Cantidad de cuotas -->
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-3">
                                            <p style="margin: 0; font-size: 16px;">Cantidad de cuotas:</p>
                                        </div>
                                        <div class="col-xs-3 text-right">
                                            <p style="margin: 0; font-size: 16px; font-weight: bold;"><span id="cantSeleccionada">0</span></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Total Cobrado real en caja -->
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-3">
                                            <h3 style="margin: 0; font-size: 20px;">Total Cobrado:</h3>
                                        </div>
                                        <div class="col-xs-3 text-right">
                                            <h3 style="margin: 0; font-size: 20px; font-weight: bold;"><span id="totalMonto">$ 0,00</span></h3>
                                        </div>
                                    </div>

                                    <!-- Total Base acumulado -->
                                    <?php 
                                    if (!empty($valorCuotaLiquidacion)) {
                                    ?>
                                        <div class="row" style="margin-bottom: 10px;">
                                            <div class="col-xs-3">
                                                <h3 style="margin: 0; font-size: 20px;">Total Importe Base:</h3>
                                            </div>
                                            <div class="col-xs-3 text-right">
                                                <h3 style="margin: 0; font-size: 20px; font-weight: bold;"><span id="totalImporteBase">$ 0,00</span></h3>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    
                                    <!-- Total a Liquidar -->
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-xs-3">
                                            <h3 style="margin: 0; font-size: 22px; color: #2e7d32;">Total a Liquidar (<?php echo 100 - (float)$porcentajeRetencionColegio.'%'; ?>):</h3>
                                        </div>
                                        <div class="col-xs-3 text-right">
                                            <h3 style="margin: 0; font-size: 22px; font-weight: bold; color: #2e7d32;"><span id="totalImporteLiquidar">$ 0,00</span></h3>
                                        </div>
                                    </div>
                                    
                                    <!-- Inputs ocultos para enviar al servidor mediante el formulario -->
                                    <input type="hidden" name="monto_total" id="monto_total_hidden" value="0">
                                    <input type="hidden" name="monto_base_total" id="monto_base_total_hidden" value="0"> 
                                    <input type="hidden" name="monto_liquidar_total" id="monto_liquidar_total_hidden" value="0"> 
                                    
                                    <button type="submit" class="btn btn-success btn-lg btn-block" id="btnConfirmar" disabled>
                                        Confirmar y Generar Lote
                                    </button>
                                </div>

                            </div>
                        </div>
                    </form>
                <?php
                } else {
                ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $titulo; ?>
                        </div>
                        <div class="col-md-2">
                            Criterio Liquidación: <?php echo $criterio_liquidacion; ?>
                        </div>
                        <div class="col-md-2">
                            Fecha limite cobranza: <?php echo cambiarFechaFormatoParaMostrar($fecha_cobranza); ?>
                        </div>
                        <div class="col-md-2">
                            Período a liquidar: <?php echo $anio_periodo.'-'.$mes_periodo; ?>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger" role="alert">
                                <span class="glyphicon glyphicon-exclamation-sign" ></span>
                                <span><strong><?php echo $resPagos['mensaje']; ?></strong></span>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
            ?>
                <form method="POST" action="liquidacion_cursos_form.php?agregar">
                    <div class="row">
                        <!-- Selección de Curso -->
                        <div class="col-md-12">
                            <label>Curso a liquidar</label>
                            <select class="form-control" id="idCurso" name="idCurso">
                                <option value="0">Todos los cursos</option>                     
                                <?php
                                $resCursos = $cursos_pdo->obtenerCursos('A');
                                foreach ($resCursos['datos'] as $dato) {
                                    $selected = ($id_curso == $dato['idCurso']) ? 'selected' : '';
                                    echo "<option value='".$dato['idCurso']."' $selected>".$dato['titulo']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-md-12">&nbsp;</div>

                        <!-- Selección de Criterio de Filtro -->
                        <div class="col-md-12" style="margin-bottom: 15px;">
                            <label style="display: block;">Criterio de Liquidación *</label>
                            <label class="radio-inline">
                                <input type="radio" name="criterio_liquidacion" value="periodo" checked onclick="alternarCriterioFiltro('periodo')">
                                Por Período (Año y Mes)
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="criterio_liquidacion" value="fecha" onclick="alternarCriterioFiltro('fecha')">
                                Por Fecha Límite Cobranza
                            </label>
                        </div>

                        <!-- Bloque Selección de Periodo (Año y Mes) -->
                        <div id="bloque_periodo">
                            <div class="col-md-2">
                                <label>Año Período *</label>
                                <select class="form-control" name="anio_periodo" id="anio_periodo" required>
                                    <?php 
                                    $anioAct = date('Y');
                                    for($i=$anioAct; $i>=2025; $i--) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>Mes Período *</label>
                                <select class="form-control" name="mes_periodo" id="mes_periodo" required>
                                    <?php
                                    $meses = array("01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre");
                                    foreach($meses as $num => $nombre) {
                                        $sel = ($num == date('m')) ? 'selected' : '';
                                        echo "<option value='$num' $sel>$nombre</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Bloque Fecha Límite de Cobranza -->
                        <div id="bloque_fecha" class="col-md-2" style="display: none;">
                            <label for="fecha_cobranza">Fecha límite de cobranza *</label>
                            <input type="date" class="form-control" name="fecha_cobranza" id="fecha_cobranza" value="<?php echo $fecha_cobranza; ?>">
                        </div>

                        <div class="col-md-12">&nbsp;</div>
                        
                        <!-- Botón de Acción -->
                        <div class="col-md-12 text-center">
                            <input type="hidden" name="accion" value="<?php echo $accion; ?>">
                            <?php if($id_liquidacion) { ?>
                                <input type="hidden" name="id_liquidacion" value="<?php echo $id_liquidacion; ?>">
                            <?php } ?>
                            <button type="submit" class="btn btn-info btn-lg">
                                <span class="glyphicon glyphicon-floppy-disk"></span> Confirmar Liquidación
                            </button>
                        </div>
                    </div>
                </form>

            <?php 
            }
            ?>
        </div>
    <?php
    } else {
    ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <span class="glyphicon glyphicon-exclamation-sign" ></span>
                    <span><strong><?php echo $mensaje; ?></strong></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="liquidacion_cursos_listado.php">
                    <button type="submit"  class="btn btn-info" >Volver</button>
                </form>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="row">&nbsp;</div>
</div>
<?php 
require_once '../html/footer.php';
?>
<script type="text/javascript"> 
    function seleccionarTodo(source) {
        checkboxes = document.getElementsByName('pagos_seleccionados[]');
        for(var i=0, n=checkboxes.length; i<n; i++) {
            checkboxes[i].checked = source.checked;
        }
        actualizarTotal();
    }

    function seleccionarTodo_Cursos(source) {
        checkboxes = document.getElementsByName('cursos_seleccionados[]');
        for(var i=0, n=checkboxes.length; i<n; i++) {
            checkboxes[i].checked = source.checked;
        }
        actualizarTotal();
    }

    // Calculamos el porcentaje a liquidar en PHP: 100 - retención
    // Si $porcentajeRetencionColegio es 30, el resultado será 70
    const PORCENTAJE_LIQUIDAR = (100 - <?php echo (float)$porcentajeRetencionColegio; ?>) / 100;

    function actualizarTotal() {
        let totalCobrado = 0;
        let totalBase = 0;
        let cantidad = 0;

        // Recorremos todos los checkboxes que estén marcados
        $('.check-pago:checked').each(function() {
            // Sumamos el importe cobrado real
            let importe = parseFloat($(this).data('importe')) || 0;
            totalCobrado += importe;

            // Sumamos el importe base
            let importeBase = parseFloat($(this).data('importe-base')) || 0;
            totalBase += importeBase;

            cantidad++;
        });

        // CÁLCULO DINÁMICO: Multiplica el total base por la variable calculada (ej: 0.70)
        let totalLiquidar = totalBase * PORCENTAJE_LIQUIDAR;

        // Actualizamos la cantidad de cuotas en el HTML
        $('#cantSeleccionada').text(cantidad);
        
        // Formateamos y mostramos los tres valores en pantalla ($ XX.XXX,XX)
        let formatoMoneda = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
        $('#totalImporteBase').text('$ ' + totalBase.toLocaleString('es-AR', formatoMoneda));
        $('#totalMonto').text('$ ' + totalCobrado.toLocaleString('es-AR', formatoMoneda));
        $('#totalImporteLiquidar').text('$ ' + totalLiquidar.toLocaleString('es-AR', formatoMoneda));

        // Guardamos los valores numéricos limpios en los inputs ocultos para el envío del formulario
        $('#monto_total_hidden').val(totalCobrado);
        $('#monto_base_total_hidden').val(totalBase);
        $('#monto_liquidar_total_hidden').val(totalLiquidar);

        // Control del estado del botón confirmar
        if (cantidad > 0) {
            $('#btnConfirmar').prop('disabled', false);
        } else {
            $('#btnConfirmar').prop('disabled', true);
        }
    }

    $(document).ready(function() {
        // Marcamos el checkbox principal "Todos"
        $('#seleccionarTodos').prop('checked', true);
        
        // Ejecutamos la función para que sume todo lo marcado por defecto
        actualizarTotal();
    });

    function verDetalleCurso(idCurso) {
        // 1. Capturar valores de los inputs
        const fechaPago = document.getElementById('fecha_cobranza').value;
        const anio = document.getElementById('anio_periodo').value;
        const mes = document.getElementById('mes_periodo').value;
        const criterio_liquidacion = document.getElementById('criterio_liquidacion').value;
        const contenedor = document.getElementById('modalDetalleContenido');

        // Validar que tengamos los datos necesarios
        if(!fechaPago || !anio || !mes) {
            alert("Por favor, complete la fecha y el periodo.");
            return;
        }

        // 2. Preparar los datos a enviar (FormData simula un formulario)
        const datos = new FormData();
        datos.append('id_curso', idCurso);
        datos.append('fecha_pago', fechaPago);
        datos.append('anio', anio);
        datos.append('mes', mes);
        datos.append('criterio_liquidacion', criterio_liquidacion);

        // 3. Mostrar el modal y poner un estado de carga
        contenedor.innerHTML = '<p class="text-center">Cargando detalle...</p>';
        // Si usas Bootstrap 3 o 4 con jQuery para los modales, esta línea sigue siendo necesaria:
        $('#miModalDetalle').modal('show'); 

        // 4. Realizar la petición
        fetch('cobranzas_cursos.php', {
            method: 'POST',
            body: datos
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text(); // Esperamos HTML del controlador
        })
        .then(html => {
            contenedor.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            contenedor.innerHTML = '<div class="alert alert-danger">No se pudo cargar el detalle.</div>';
        });
    }

    function alternarCriterioFiltro(criterio) {
        const bloquePeriodo = document.getElementById('bloque_periodo');
        const bloqueFecha = document.getElementById('bloque_fecha');
        
        const anio = document.getElementById('anio_periodo');
        const mes = document.getElementById('mes_periodo');
        const fecha = document.getElementById('fecha_cobranza');

        if (criterio === 'periodo') {
            // Mostrar periodo, ocultar fecha
            bloquePeriodo.style.display = 'block';
            //bloqueFecha.style.display = 'none';
            bloqueFecha.style.display = 'block';
            
            // Configurar obligatorios
            anio.required = true;
            mes.required = true;
            fecha.required = false;
            fecha.value = ''; // Limpia el valor si cambia de criterio
        } else {
            // Mostrar fecha, ocultar periodo
            bloquePeriodo.style.display = 'none';
            bloqueFecha.style.display = 'block';
            
            // Configurar obligatorios
            anio.required = false;
            mes.required = false;
            fecha.required = true;
        }
    }

    // Inicializar el estado al cargar la página
    document.addEventListener("DOMContentLoaded", function() {
        alternarCriterioFiltro('periodo');
    });

</script>

<!-- Modal para el Detalle -->
<div class="modal fade" id="miModalDetalle" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Detalle de Cuotas Pagadas</h4>
            </div>
            <div class="modal-body" id="modalDetalleContenido">
                <!-- AQUÍ ES DONDE JAVASCRIPT BUSCA ESCRIBIR EL RESULTADO -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>