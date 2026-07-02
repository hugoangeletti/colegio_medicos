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

if (isset($_SESSION['alerta'])) { 
    $alerta = $_SESSION['alerta'];
    ?>
    <!-- 3. Renderizar el bloque de Bootstrap dinámicamente -->
    <div class="<?php echo $alerta['clase']; ?> alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <i class="<?php echo $alerta['icono']; ?>"></i> 
        <?php echo $alerta['mensaje']; ?>
    </div>
    <?php 
    // 4. Limpiar la alerta de la memoria para que no vuelva a aparecer al recargar
    unset($_SESSION['alerta']); 
}

if (isset($_GET['agregar'])) {
    $accion = 'agregar';
    $id_liquidacion = NULL;
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
<div class="container" style="margin-top: 20px;">
    <div class="panel panel-info">
        <?php
        $cursos_pdo = new cursos_pdo();
        if ($continua) {
        ?>
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-9">
                        <h3>Generar liquidación de Saldos pendientes</h3>
                    </div>
                    <div class="col-md-3 text-right">
                        <a href="liquidacion_cursos_docentes_listado.php" class="btn btn-primary">Volver</a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?php
                $resPagos = $cursos_pdo->obtenerLiquidacionesConSaldoPendientePago();
                if ($resPagos['estado'] && sizeof($resPagos['datos']) > 0) {
                ?>
                    <form id="formLiquidacion" action="datosCurso/abm_liquidacion_cursos_saldos.php?agregar" method="POST">
                        <table id="pagos_cursos" class="table">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 80px;">
                                        <!-- Ajustado para que al marcar "Todos" ejecute la lógica completa -->
                                        <input type="checkbox" id="seleccionarTodos" onclick="seleccionarTodo_Cursos(this)">
                                        <br><small>Todos</small>
                                    </th>
                                    <th style="text-align: left;">Id</th>
                                    <th style="text-align: left;">Fecha liqudación</th>
                                    <th style="text-align: left;">Cursos</th>
                                    <th style="text-align: right;">Saldo a liquidar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resPagos['datos'] as $pago) { ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <!-- Añadido data-id-curso para poder agrupar docentes por AJAX -->
                                        <input type="checkbox" 
                                               name="liquidaciones_seleccionadas[]" 
                                               value="<?php echo $pago['Id']; ?>" 
                                               class="check-pago"
                                               data-id-curso="<?php echo $pago['IdCursos']; ?>"
                                               data-importe="<?php echo $pago['Saldo']; ?>"
                                               onclick="actualizarInterfaz()">
                                    </td>
                                    <td style="text-align: left;"><?php echo $pago['Id']; ?></td>
                                    <td style="text-align: left;"><?php echo cambiarFechaFormatoParaMostrar(substr($pago['FechaLiquidacion'], 0, 10)); ?></td>
                                    <td style="text-align: left;"><?php echo $pago['Cursos_Liquidados']; ?></td>
                                    <td style="text-align: right;">$ <?php echo number_format($pago['Saldo'], 2, ',', '.'); ?></td>
                                    <!--<td style="text-align: center;">
                                        <button type="button" 
                                                class="btn btn-info btn-xs" 
                                                onclick="verDetalleDocentes(<?php echo $pago['IdCursos']; ?>)"
                                                title="Ver detalle docentes">
                                            <i class="glyphicon glyphicon-search"></i> Docentes
                                        </button>
                                    </td>-->
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="row">
                            <!-- Columna Izquierda: Mostrar docentes dinámicamente -->
                            <div class="col-md-6 col-sm-6">
                                <div class="well">
                                    <h4>Docentes Involucrados</h4>
                                    <div id="contenedor_docentes">
                                        <p class="text-muted">Seleccione cursos para listar los docentes...</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Columna Derecha: Resumen de Liquidación -->
                            <div class="col-md-6 col-sm-6">
                                <div class="well">
                                    <h4>Resumen de Liquidación</h4>
                                    <p>Cantidad de cuotas: <span id="cantSeleccionada">0</span></p>
                                    <h3>Total Liquidaciones: <span id="totalMonto">$ 0,00</span></h3>
                                    <input type="hidden" name="monto_total" id="monto_total_hidden" value="0">
                                    
                                    <h3>Total pago a docentes: 
                                        <input type="number" step="0.01" name="monto_liquidar" id="monto_liquidar_hidden" value="0" class="form-control" readonly style="background-color: #eee; font-weight: bold;">
                                    </h3>

                                    <!-- Contenedor dinámico para la alerta visual -->
                                    <div id="alerta-monto" style="margin-top: 15px;"></div>

                                    <button type="submit" class="btn btn-success btn-lg btn-block" id="btnConfirmar" disabled>
                                        Confirmar Liquidación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php
                } else {
                ?>
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
</div>
<?php 
require_once '../html/footer.php';
?>
<script type="text/javascript"> 
    // Vincular la validación al envío del formulario
    document.addEventListener("DOMContentLoaded", () => {
        actualizarInterfaz();
        
        // CORREGIDO: Se añade '#' para buscar el formulario por su ID real
        const formulario = document.querySelector('#formLiquidacion');
        if (formulario) {
            formulario.addEventListener('submit', function(evento) {
                if (!validarMontosMaximos()) {
                    evento.preventDefault(); // Detiene el envío si la validación falla
                }
            });
        }
    });

    function seleccionarTodo_Cursos(source) {
        const checkboxes = document.querySelectorAll('.check-pago');
        checkboxes.forEach(cb => cb.checked = source.checked);
        actualizarInterfaz();
    }

    function actualizarInterfaz() {
        const checkboxes = document.querySelectorAll('.check-pago:checked');
        
        let totalMonto = 0;
        let cantCuotas = checkboxes.length;
        let cursosIds = [];
        
        checkboxes.forEach(cb => {
            totalMonto += parseFloat(cb.getAttribute('data-importe')) || 0;
            cursosIds.push(cb.getAttribute('data-id-curso'));
        });
        
        document.getElementById('cantSeleccionada').textContent = cantCuotas;
        document.getElementById('totalMonto').textContent = '$ ' + totalMonto.toLocaleString('es-AR', { minimumFractionDigits: 2 });
        document.getElementById('monto_total_hidden').value = totalMonto;
        
        cargarDocentesAjax(cursosIds);
    }

    function cargarDocentesAjax(cursosIds) {
        const contenedor = document.getElementById('contenedor_docentes');
        
        if (cursosIds.length === 0) {
            contenedor.innerHTML = '<p class="text-muted">Seleccione cursos para listar los docentes...</p>';
            document.getElementById('monto_liquidar_hidden').value = 0;
            validarMontosMaximos(); 
            return;
        }
        
        fetch('datosCurso/obtener_docentes_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: cursosIds })
        })
        .then(response => response.text())
        .then(html => {
            contenedor.innerHTML = html;
            sumarMontosManuales();
        })
        .catch(error => {
            contenedor.innerHTML = '<p class="text-danger">Error al cargar docentes.</p>';
            console.error('Error:', error);
        });
    }

    function alternarInputDocente(checkbox, idDocente) {
        const inputMonto = document.getElementById('monto_docente_' + idDocente);
        if (!checkbox.checked) {
            inputMonto.value = "0.00";
            inputMonto.disabled = true;
        } else {
            inputMonto.disabled = false;
        }
        sumarMontosManuales();
    }

    function sumarMontosManuales() {
        const inputs = document.querySelectorAll('.input-monto-docente');
        let sumaTotal = 0;
        inputs.forEach(input => {
            if (!input.disabled) {
                sumaTotal += parseFloat(input.value) || 0;
            }
        });
        document.getElementById('monto_liquidar_hidden').value = sumaTotal.toFixed(2);
        
        validarMontosMaximos();
    }

    // UNIFICADA Y CORREGIDA: Controla topes, alertas y bloquea si el monto es 0
    function validarMontosMaximos() {
        const montoTotalCursos = parseFloat(document.getElementById('monto_total_hidden').value) || 0;
        const montoDocentesAsignado = parseFloat(document.getElementById('monto_liquidar_hidden').value) || 0;
        const contenedorAlerta = document.getElementById('alerta-monto');
        const btnConfirmar = document.getElementById('btnConfirmar');
        const cantidadCuotas = parseInt(document.getElementById('cantSeleccionada').textContent) || 0;

        // CASO 1: No hay cursos seleccionados o el monto total acumulado de docentes es cero
        if (cantidadCuotas === 0 || montoDocentesAsignado === 0) {
            contenedorAlerta.innerHTML = ''; // Limpia alertas de exceso
            btnConfirmar.disabled = true;
            btnConfirmar.classList.add('disabled');
            return false;
        }

        // CASO 2: El monto asignado supera el pozo disponible de los cursos
        if (montoDocentesAsignado > montoTotalCursos) {
            contenedorAlerta.innerHTML = `
                <div class="alert alert-danger" style="margin-bottom: 15px; padding: 10px;">
                    <i class="glyphicon glyphicon-exclamation-sign"></i> 
                    <strong>¡Atención!</strong> El monto total a liquidar ($ ${montoDocentesAsignado.toFixed(2)}) supera el total disponible de los cursos ($ ${montoTotalCursos.toFixed(2)}).
                </div>
            `;
            btnConfirmar.disabled = true;
            btnConfirmar.classList.add('disabled');
            return false; 
        } 

        // CASO 3: Todo está correcto y es mayor a cero
        contenedorAlerta.innerHTML = '';
        btnConfirmar.disabled = false;
        btnConfirmar.classList.remove('disabled');
        return true; 
    }

    function verDetalleDocentes(idCurso) {
        const contenedor = document.getElementById('modalDetalleContenido');
        //const criterioElement = document.getElementById('criterio_liquidacion');
        //const criterio_liquidacion = criterioElement ? criterioElement.value : 'docentes';
        const criterio_liquidacion = 'docentes';
        
        const datos = new FormData();
        datos.append('id_curso', idCurso);
        datos.append('criterio_liquidacion', criterio_liquidacion);
        
        contenedor.innerHTML = '<p class="text-center">Cargando detalle...</p>';
        $('#miModalDetalle').modal('show'); 
        
        fetch('cobranzas_cursos.php', {
            method: 'POST',
            body: datos
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.text();
        })
        .then(html => {
            contenedor.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            contenedor.innerHTML = '<div class="alert alert-danger">No se pudo cargar el detalle.</div>';
        });
    }

    function alternarCriterioFiltro() {
        // Mantenemos tu función segura para evitar que rompa la pantalla
        const elemento = document.getElementById('ID_DE_TU_ELEMENTO_AQUI'); 
        if (elemento) {
            elemento.style.display = 'block'; 
        } else {
            console.warn("El elemento para alternar filtro no se encontró en el DOM.");
        }
    }
</script>

<!-- Modal para el Detalle -->
<div class="modal fade" id="miModalDetalle" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Docentes del curso</h4>
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