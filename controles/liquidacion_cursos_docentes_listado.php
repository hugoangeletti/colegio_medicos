<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/usuarioLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "desc" ]],
            //"order": [[ 2, "desc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });   

</script>

<?php
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
$cursos_pdo = new cursos_pdo();
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-8">
                <h4><b>Liquidaciones Docentes de Cursos</b></h4>
            </div>
            <div class="col-md-2 text-right">
                <a href="liquidacion_cursos_docentes_form.php?agregar" class="btn btn-primary">Generar Liquidación a docentes</a>
            </div>
            <div class="col-md-2 text-left">
                <a href="liquidacion_cursos_saldos_form.php?agregar" class="btn btn-primary">Generar Liquidación de saldos</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        // Obtener año actual para los valores por defecto
        $anioActual = date('Y');

        // Capturar lo seleccionado por el usuario (si ya envió el formulario)
        $tipo_liquidacion = isset($_POST['tipoLiquidacion']) ? $_POST['tipoLiquidacion'] : NULL;
        if (empty($tipo_liquidacion)) {
            $tipo_liquidacion = 'docentes';
            if (isset($_GET['saldos'])) {
                $tipo_liquidacion =  'saldos';
            }
        }

        $anioElegido = isset($_POST['periodoSeleccionado']) ? $_POST['periodoSeleccionado'] : $anioActual;

        $docenteElegido = isset($_POST['docenteSeleccionado']) ? $_POST['docenteSeleccionado'] : NULL;
        $docente_buscar = isset($_POST['docente_buscar']) ? $_POST['docente_buscar'] : NULL;
        $cursoElegido = isset($_POST['cursoSeleccionado']) ? $_POST['cursoSeleccionado'] : 'TODOS';
        $curso_buscar = isset($_POST['curso_buscar']) ? $_POST['curso_buscar'] : NULL;
        $criterio_busqueda = isset($_POST['criterio_busqueda']) ? $_POST['criterio_busqueda'] : 'periodo';
        ?>
        <div class="row">
            <?php 
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criterio_busqueda'])) {
            ?>
                <div class="col-md-10" style="margin-bottom: 15px;">
                    <?php
                    switch ($criterio_busqueda) {
                        case 'periodo':
                            echo '<h4>Búsqueda por Período: <b>'.$anioElegido.'</b></h4>';
                            break;
                        
                        case 'curso':
                            $resCurso = $cursos_pdo->obtenerCursoPorId($cursoElegido);
                            if ($resCurso['estado']) {
                                echo '<h4>Búsqueda por Curso: <b>'.$resCurso['datos']['titulo'].'</b></h4>';
                            } else {
                                echo '<h4>ERROR: <b>'.$resCurso['mensaje'].'</b></h4>';
                            }
                            break;
                        
                        case 'docente':
                            $resDocente = $cursos_pdo->obtenerDocentePorId($docenteElegido);
                            if ($resDocente['estado']) {
                                echo '<h4>Búsqueda por Docente: <b>'.$resDocente['datos']['ApellidoNombres'].'</b></h4>';
                            } else {
                                echo '<h4>ERROR: <b>'.$resDocente['mensaje'].'</b></h4>';
                            }
                            break;
                        
                        default:
                            // code...
                            break;
                    }
                    ?>
                </div>
                <div class="col-md-2 text-right">
                    <a href="liquidacion_cursos_docentes_listado.php" class="btn btn-default" title="Inicializar">Inicializar campos</a>
                </div>
            <?php
            } else {
            ?>
                <div class="col-md-1">
                    <form method="POST" action="liquidacion_cursos_docentes_listado.php">                
                        <!-- Selector tipo liquidacion docentes o saldos -->
                        <label>Tipo Liquidación:</label>
                        <select class="form-control" id="tipoLiquidacion" name="tipoLiquidacion" required onChange="this.form.submit()">
                            <option value='docentes' <?php if ($tipo_liquidacion == 'docentes') { echo 'selected'; } ?>>DOCENTES</option>
                            <option value='saldos' <?php if ($tipo_liquidacion == 'saldos') { echo 'selected'; } ?>>SALDOS</option>
                        </select>
                    </form>
                </div>
                <!-- Selección de Criterio de Filtro -->
                <?php 
                if ($tipo_liquidacion == 'docentes') {
                ?>
                <div class="col-md-3" style="margin-bottom: 15px;">
                    <label style="display: block;">Criterio de Busqueda *</label>
                    <label class="radio-inline">
                        <input type="radio" name="criterio_busqueda" value="periodo" <?php if ($criterio_busqueda == 'periodo') { ?>checked<?php } ?> onclick="alternarCriterioFiltro('periodo')">
                        Por Período (Año y Mes)
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="criterio_busqueda" value="curso" <?php if ($criterio_busqueda == 'curso') { ?>checked<?php } ?> onclick="alternarCriterioFiltro('curso')">
                        Por Curso
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="criterio_busqueda" value="docente" <?php if ($criterio_busqueda == 'docente') { ?>checked<?php } ?> onclick="alternarCriterioFiltro('docente')">
                        Por Docente
                    </label>
                </div>
                <?php } ?>
                <div class="col-md-8">
                    <div id="bloque_periodo">
                        <form method="POST" action="liquidacion_cursos_docentes_listado.php">                
                            <!-- Selector de Año -->
                            <div class="col-md-2">
                                <label>Año:</label>
                                <select class="form-control" id="periodoSeleccionado" name="periodoSeleccionado" required>
                                    <?php 
                                    $anioLimite = 2026; 
                                    for ($i = $anioActual; $i >= $anioLimite; $i--) {
                                        $selected = ($i == $anioElegido) ? 'selected' : '';
                                        echo "<option value='$i' $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <br>
                                <button type="submit"  class="btn btn-info " >Buscar</button>
                                <input type="hidden" name="criterio_busqueda" id="criterio_busqueda" value="periodo">
                            </div>
                        </form>
                    </div>
                    <div id="bloque_curso">
                        <form method="POST" action="liquidacion_cursos_docentes_listado.php">
                            <div class="col-md-10">
                                <label>Curso:</label>
                                <input class="form-control" autofocus autocomplete="OFF" type="text" name="curso_buscar" id="curso_buscar" placeholder="Ingrese Título del Curso" required=""/>
                                <input type="hidden" name="cursoSeleccionado" id="cursoSeleccionado" required="" />
                            </div>
                            <div class="col-md-2">
                                <br>
                                <button type="submit"  class="btn btn-info " >Buscar</button>
                                <input type="hidden" name="criterio_busqueda" id="criterio_busqueda" value="curso">
                                <input type="hidden" name="tipoLiquidacion" id="tipoLiquidacion" value="<?php echo $tipo_liquidacion; ?>">
                            </div>
                        </form>
                    </div>
                    <div id="bloque_docente">
                        <form method="POST" action="liquidacion_cursos_docentes_listado.php">
                            <div class="col-md-10">
                                <label>Docente:</label>
                                <input class="form-control" autofocus autocomplete="OFF" type="text" name="docente_buscar" id="docente_buscar" placeholder="Ingrese Apellido del Docente" required=""/>
                                <input type="hidden" name="docenteSeleccionado" id="docenteSeleccionado" required="" />
                            </div>
                            <div class="col-md-2">
                                <br>
                                <button type="submit"  class="btn btn-info " >Buscar</button>
                                <input type="hidden" name="criterio_busqueda" id="criterio_busqueda" value="docente">
                                <input type="hidden" name="tipoLiquidacion" id="tipoLiquidacion" value="<?php echo $tipo_liquidacion; ?>">
                            </div>
                        </form>
                    </div>
                </div>
            <?php 
            }
            ?>
        </div>
        <?php
        $resLiquidaciones = $cursos_pdo->obtenerLiquidacionesDocentesGlobales($anioElegido, $docenteElegido, $cursoElegido);
        if ($resLiquidaciones['estado'] && !empty($resLiquidaciones['datos'])) {
        ?>
            <br>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Cursos incluidos</th>
                        <th style="text-align: right;">Total liquidado</th>
                        <th style="text-align: right;">Total pagado</th>
                        <th style="text-align: right;">Saldo </th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    foreach ($resLiquidaciones['datos'] as $dato) { 
                        if ($dato['TipoLiquidacion'] != $tipo_liquidacion) { continue; }
                    ?>
                    <tr>
                        <td><?php echo $dato['Id']; ?></td>
                        <td><?php echo $dato['TipoLiquidacion']; ?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar(substr($dato['FechaLiquidacion'], 0, 10)); ?></td>
                        <td><?php echo $dato['Cursos_Liquidados']; ?></td>
                        <td style="text-align: right;">$ <?php echo number_format($dato['TotalLiquidado'], 2, ',', '.'); ?></td>
                        <td style="text-align: right;">$ <?php echo number_format($dato['TotalAbonado'], 2, ',', '.'); ?></td>
                        <td style="text-align: right;"> 
                            <?php
                            if ($dato['Saldo'] > 0) {
                                if ($dato['TipoLiquidacion'] == 'docentes') {
                                    if (empty($dato['IdLiquidacionSaldos'])) {
                                        echo '$' . number_format($dato['Saldo'], 2, ',', '.');
                                    } else {
                                        echo '<b>Abonado: $' . number_format($dato['Saldo'], 2, ',', '.') . '</b>';
                                    }
                                }
                            }
                            ?>
                        </td>
                        <td><?php echo $dato['EstadoLiquidacionDocentes']; ?></td>
                        <!-- Columna de Acciones con Botón de Impresión -->
                        <td style="text-align: center; width: 200px;">
                            <div class="btn-group">
                                <!-- Botón Detalle -->
                                <button type="button" 
                                        class="btn btn-success btn-sm btn-ver-docentes" 
                                        data-toggle="modal" 
                                        data-target="#modalDocentes" 
                                        data-id="<?php echo $dato['Id']; ?>" 
                                        title="Ver Docentes">
                                   <i class="glyphicon glyphicon-search"></i>
                                </button>

                                <!-- CONTENEDOR OCULTO DE DOCENTES (Se arma procesando el String agrupado) -->
                                <div id="datos-docentes-<?php echo $dato['Id']; ?>" style="display: none;">
                                    <table class="table table-striped table-condensed" style="margin: 0;">
                                        <thead>
                                            <tr>
                                                <th>Docente</th>
                                                <th style="text-align: right;">Importe</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if (!empty($dato['Detalle_Docentes'])) {
                                                // Separamos los docentes por el delimitador '||'
                                                $listaDocentes = explode('||', $dato['Detalle_Docentes']);
                                                foreach ($listaDocentes as $itemDocente) {
                                                    // Separamos el nombre del importe por el delimitador ':'
                                                    $partes = explode(':', $itemDocente);
                                                    $nombreDocente = $partes[0];
                                                    $importeDocente = isset($partes[1]) ? (float)$partes[1] : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($nombreDocente); ?></td>
                                                        <td style="text-align: right;">$ <?php echo number_format($importeDocente, 2, ',', '.'); ?></td>
                                                    </tr>
                                                    <?php 
                                                }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="2" class="text-center">No hay docentes registrados</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Botón Imprimir PDF -->
                                <!--<a href="liquidacion_cursos_docentes_imprimir.php?id=<?php echo $dato['Id']; ?>" 
                                   class="btn btn-info btn-sm" 
                                   target="_blank" 
                                   title="Imprimir PDF">
                                   <i class="glyphicon glyphicon-print"></i> PDF
                                </a>-->

                                <!-- Botón Anular -->
                                <?php 

                                if ($dato['IdEstadoLiquidacionCursosDocentes'] == $cursos_pdo::LIQUIDACION_DOCENTES_INICIADA 
                                    || $dato['IdEstadoLiquidacionCursosDocentes'] == $cursos_pdo::LIQUIDACION_DOCENTES_FINALIZADA) {
                                    ?>
                                    <a href="datosCurso/abm_liquidacion_cursos_docentes.php?id=<?php echo $dato['Id']; ?>&anular" 
                                        class="btn btn-danger btn-sm" 
                                        title="Anular"
                                        onclick="return confirm('¿Confirma la anulación de esta liquidación?')">
                                        <i class="glyphicon glyphicon-remove"></i>
                                    </a>
                                <?php 
                                } else {
                                    if ($dato['IdEstadoLiquidacionCursosDocentes'] == $cursos_pdo::LIQUIDACION_DOCENTES_SALDOS) {
                                ?>
                                        <a href="datosCurso/abm_liquidacion_cursos_saldos.php?id=<?php echo $dato['Id']; ?>&anular" 
                                            class="btn btn-danger btn-sm" 
                                            title="Anular"
                                            onclick="return confirm('¿Confirma la anulación de esta liquidación?')">
                                            <i class="glyphicon glyphicon-remove"></i>
                                        </a>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resLiquidaciones['clase']; ?>" role="alert">
                <span class="<?php echo $resLiquidaciones['icono']; ?>"></span>
                <span><strong><?php echo $resLiquidaciones['mensaje']; ?></strong></span>
            </div>
        <?php
        }
        ?>
    </div>
</div>

<!-- Modal para Mostrar Docentes -->
<div class="modal fade" id="modalDocentes" tabindex="-1" role="dialog" aria-labelledby="modalDocentesLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalDocentesLabel">Docentes Incluidos en la Liquidación</h4>
            </div>
            <div class="modal-body" id="contenidoModalDocentes">
                <!-- JavaScript inyectará el contenido aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    //buscar asistente
    $(function(){
        var nameIdMap = {};
        $('#docente_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'docente.php',
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
                $('#docenteSeleccionado').val(nameIdMap[item]);
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

    //buscar curso
    $(function(){
        var nameIdMap = {};
        $('#curso_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'curso.php',
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
                $('#cursoSeleccionado').val(nameIdMap[item]);
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

    function confirmaCambio()
    {
        if(confirm('¿Estas seguro de ANULAR la liquidación?'))
            return true;
        else
            return false;
    }
    
    function abrirModalPago(idLiquidacion) {
        // Asigna el ID al campo oculto del formulario
        document.getElementById('modal_id_liquidacion').value = idLiquidacion;
        // Resetea la fecha al día de hoy por defecto
        document.getElementById('modal_id_liquidacion').value = idLiquidacion;
        
        // Abre el modal usando Bootstrap 3/4
        $('#modalCargarPago').modal('show');
    }

    function guardarFechaPago(event) {
        event.preventDefault(); // Evita que la página se recargue

        const idLiq = document.getElementById('modal_id_liquidacion').value;
        const fecha = document.getElementById('modal_fecha_pago').value;

        if (!fecha) {
            alert("Por favor, seleccione una fecha válida.");
            return;
        }

        const datos = new FormData();
        datos.append('id_liquidacion', idLiq);
        datos.append('fecha_pago', fecha);

        // Envía los datos al script ABM que procesa los cambios
        fetch('datosCurso/abm_liquidacion_cursos.php?actualizarPago', {
            method: 'POST',
            body: datos
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor');
            return response.json();
        })
        .then(res => {
            if (res.estado) {
                alert("Pago registrado correctamente.");
                location.reload(); // Recarga la tabla para reflejar el cambio
            } else {
                alert("Error: " + res.mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("No se pudo conectar con el servidor para guardar el pago.");
        });
    }

    function alternarCriterioFiltro(criterio) {
        // 1. Obtener todos los bloques de la interfaz
        const bloquePeriodo = document.getElementById('bloque_periodo');
        const bloqueCurso = document.getElementById('bloque_curso');
        const bloqueDocente = document.getElementById('bloque_docente');

        // 2. Obtener todos los inputs/selects subyacentes
        const inputPeriodo = document.getElementById('periodoSeleccionado');
        const inputCurso = document.getElementById('cursoSeleccionado');
        const inputDocente = document.getElementById('docenteSeleccionado');

        // 3. Ocultar todos los bloques y quitar la obligatoriedad por defecto
        bloquePeriodo.style.display = 'none';
        bloqueCurso.style.display = 'none';
        bloqueDocente.style.display = 'none';

        inputPeriodo.required = false;
        inputCurso.required = false;
        inputDocente.required = false;

        // 4. Activar y mostrar solo el criterio seleccionado
        switch (criterio) {
            case 'periodo':
                bloquePeriodo.style.display = 'block';
                inputPeriodo.required = true;
                // Limpiar los valores de los otros filtros innecesarios
                inputCurso.value = '';
                inputDocente.value = '';
                break;

            case 'curso':
                bloqueCurso.style.display = 'block';
                inputCurso.required = true;
                inputPeriodo.value = '';
                inputDocente.value = '';
                break;

            case 'docente':
                bloqueDocente.style.display = 'block';
                inputDocente.required = true;
                inputPeriodo.value = '';
                inputCurso.value = '';
                break;
        }
    }

    // Inicializar el estado al cargar la página
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Usar 'let' en lugar de 'const' para permitir la reasignación de valores
        let criterio = document.getElementById('criterio_busqueda');

        // 2. Verificar correctamente si el elemento existe y obtener su valor.
        // Si no existe o está vacío, se asigna el valor por defecto 'periodo'.
        if (!criterio || criterio.value.trim() === '') {
            criterio = 'periodo';
        } else {
            criterio = criterio.value;
        }

        // 3. Ejecutar la función con la cadena de texto final
        alternarCriterioFiltro(criterio);
    });

    $(document).ready(function() {
        $('.btn-ver-docentes').on('click', function() {
            // 1. Obtener el ID del botón presionado
            let idLiquidacion = $(this).data('id');
            
            // 2. Localizar el HTML del contenedor oculto usando el ID
            let htmlDocentes = $('#datos-docentes-' + idLiquidacion).html();
            
            // 3. Inyectar el HTML directamente en el cuerpo del modal
            if (htmlDocentes && htmlDocentes.trim() !== "") {
                $('#contenidoModalDocentes').html(htmlDocentes);
            } else {
                // Mensaje de respaldo por si la liquidación no tiene docentes cargados
                $('#contenidoModalDocentes').html('<div class="alert alert-info" style="margin:0;">No hay docentes registrados en esta liquidación.</div>');
            }
        });
    });

</script>
