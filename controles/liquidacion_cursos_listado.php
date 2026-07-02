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
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
$cursos_pdo = new cursos_pdo();
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-10">
                <h4><b>Liquidaciones de Cursos</b></h4>
            </div>
            <div class="col-md-2 text-center">
                <a href="liquidacion_cursos_form.php?agregar" class="btn btn-primary">Generar Liquidación</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        // Obtener mes y año actuales para los valores por defecto
        $anioActual = date('Y');
        $mesActual = date('m');

        // Capturar lo seleccionado por el usuario (si ya envió el formulario)
        $anioElegido = isset($_POST['periodoSeleccionado']) ? $_POST['periodoSeleccionado'] : $anioActual;
        $mesElegido = isset($_POST['mesSeleccionado']) ? $_POST['mesSeleccionado'] : $mesActual;
        $periodo = $anioElegido.'-'.$mesElegido;

        // Array de meses para el selector
        $meses = array(
            "01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril",
            "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto",
            "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"
        );
        $idAsistente = isset($_POST['idAsistente']) ? $_POST['idAsistente'] : NULL;
        $asistente_buscar = isset($_POST['asistente_buscar']) ? $_POST['asistente_buscar'] : NULL;
        $cursoElegido = isset($_POST['cursoSeleccionado']) ? $_POST['cursoSeleccionado'] : 'TODOS';
        $criterio_busqueda = isset($_POST['criterio_busqueda']) ? $_POST['criterio_busqueda'] : 'periodo';
        ?>
        <div class="row">
            <?php 
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            ?>
                <div class="col-md-10" style="margin-bottom: 15px;">
                    <?php
                    if ($criterio_busqueda == 'periodo') {
                        echo '<h4>Búsqueda por Período: <b>'.$periodo.'</b></h4>';
                    } else {
                        if ($cursoElegido == 'TODOS') {
                            echo '<h4><b>'.'Búsqueda de TODOS LOS CURSOS'.'</b></h4>';
                        }
                        $resCurso = $cursos_pdo->obtenerCursoPorId($cursoElegido);
                        if ($resCurso['estado']) {
                            echo '<h4>Búsqueda por Curso: <b>'.$resCurso['datos']['titulo'].'</b></h4>';
                        } else {
                            echo '<h4>ERROR: <b>'.$resCurso['mensaje'].'</b></h4>';
                        }
                    }
                    ?>
                </div>
                <div class="col-md-2 text-right">
                    <a href="liquidacion_cursos_listado.php" class="btn btn-default" title="Inicializar">Inicializar campos</a>
                </div>
            <?php
            } else {
            ?>
                <!-- Selección de Criterio de Filtro -->
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
                </div>
                <div class="col-md-8">
                    <div id="bloque_periodo">
                        <form method="POST" action="liquidacion_cursos_listado.php">                
                            <!-- Selector de Año -->
                            <div class="col-md-4">
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

                            <!-- Selector de Mes -->
                            <div class="col-md-4">
                                <label>Mes:</label>
                                <select class="form-control" id="mesSeleccionado" name="mesSeleccionado" required>
                                    <?php 
                                    foreach ($meses as $num => $nombre) {
                                        $selected = ($num == $mesElegido) ? 'selected' : '';
                                        echo "<option value='$num' $selected>$nombre</option>";
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
                        <form method="POST" action="liquidacion_cursos_listado.php">
                            <div class="col-md-10">
                                <label>Curso:</label>
                                <input class="form-control" autofocus autocomplete="OFF" type="text" name="curso_buscar" id="curso_buscar" placeholder="Ingrese Título del Curso" required=""/>
                                <input type="hidden" name="cursoSeleccionado" id="cursoSeleccionado" required="" />
                                <!--
                                <select class="form-control" id="cursoSeleccionado" name="cursoSeleccionado">
                                    <option value="TODOS" <?php echo ($cursoElegido == 'TODOS') ? 'selected' : ''; ?>>-- Todos los Cursos --</option>
                                    <?php
                                    // Llamada a tu base de datos para listar los cursos
                                    $listaCursos = $cursos_pdo->obtenerCursos(''); 
                                    if (!empty($listaCursos)) {
                                        foreach ($listaCursos['datos'] as $curso) {
                                            $selected = ($curso['idCurso'] == $cursoElegido) ? 'selected' : '';
                                            echo "<option value='{$curso['idCurso']}' $selected>{$curso['titulo']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                    -->
                            </div>
                            <div class="col-md-4" id="esUnAsistente" style="display: none;">
                                <label for="asistente_buscar">Buscar asistente</label>
                                <input class="form-control" autocomplete="OFF" type="text" name="asistente_buscar" id="asistente_buscar" value="<?php echo $asistente_buscar; ?>" placeholder="Ingrese Matrícula o Apellido del asistente" />
                                <input type="hidden" name="idAsistente" id="idAsistente" value="<?php echo $idAsistente; ?>" />
                            </div>
                            <div class="col-md-2">
                                <br>
                                <button type="submit"  class="btn btn-info " >Buscar</button>
                                <input type="hidden" name="criterio_busqueda" id="criterio_busqueda" value="curso">
                            </div>
                        </form>
                    </div>
                </div>
            <?php 
            }
            ?>
        </div>
        <?php
        $resLiquidaciones = $cursos_pdo->obtenerLiquidacionesGlobales($anioElegido, $mesElegido, $idAsistente, $cursoElegido);
        //obtenerLiquidacionesPorPeriodo($periodo, $idAsistente, $asistente_buscar);

        if ($resLiquidaciones['estado'] && !empty($resLiquidaciones['datos'])) {
        ?>
            <br>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Curso</th>
                        <th>Período liquidado</th>
                        <th>Fecha de liquidación</th>
                        <th>Fecha de cobranza</th>
                        <th>Total Cobrado</th>
                        <th>Total A Liquidar</th>
                        <th>Total liquidado</th>
                        <th>Cantidad cuotas</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resLiquidaciones['datos'] as $dato) { ?>
                    <tr>
                        <td><?php echo $dato['IdLiquidacionCursos']; ?></td>
                        <td><?php echo '['.$dato['IdCursos'].'] '.$dato['NombreCurso']; ?></td>
                        <td style="text-align: center;"><?php echo substr($dato['PeriodoLiquidacion'], 0, 4) . '-' . substr($dato['PeriodoLiquidacion'], 4, 2); ?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar(substr($dato['FechaLiquidacion'], 0, 10)); ?></td>
                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($dato['FechaCobranza']); ?></td>
                        <td style="text-align: right;">$ <?php echo number_format($dato['TotalCobrado'], 2, ',', '.'); ?></td>
                        <td style="text-align: right;">$ <?php echo number_format($dato['TotalLiquidacion'], 2, ',', '.'); ?></td>
                        <td style="text-align: right;">$ <?php echo number_format($dato['TotalLiquidado'], 2, ',', '.'); ?></td>
                        <td style="text-align: center;"><?php echo $dato['CantidadCuotas']; ?></td>
                        <!-- Columna de Acciones con Botón de Impresión -->
                        <td style="text-align: center; width: 200px;">
                            <div class="btn-group">
                                <!-- Botón Detalle -->
                                <a href="liquidacion_cursos_detalle.php?id=<?php echo $dato['IdLiquidacionCursos']; ?>" 
                                   class="btn btn-success btn-sm" title="Ver Detalle">
                                   <i class="glyphicon glyphicon-search"></i>
                                </a>
                                <!-- Botón Imprimir PDF -->
                                <a href="liquidacion_cursos_imprimir.php?id=<?php echo $dato['IdLiquidacionCursos']; ?>" 
                                   class="btn btn-info btn-sm" 
                                   target="_blank" 
                                   title="Imprimir PDF">
                                   <i class="glyphicon glyphicon-print"></i> PDF
                                </a>

                                <!-- Botón Anular -->
                                <?php 
                                if (empty($dato['IdLiquidacionCursosDocentes'])) {
                                ?>
                                    <button type="button" 
                                            class="btn btn-primary btn-sm" 
                                            title="Cargar Pago"
                                            onclick="abrirModalPago(<?php echo $dato['IdLiquidacionCursos']; ?>)">
                                        <i class="glyphicon glyphicon-usd"></i>
                                    </button>
                                    <a href="datosCurso/abm_liquidacion_cursos.php?id=<?php echo $dato['IdLiquidacionCursos']; ?>&anular" 
                                   class="btn btn-danger btn-sm" 
                                   title="Anular"
                                   onclick="return confirm('¿Confirma la anulación de esta liquidación?')">
                                    <i class="glyphicon glyphicon-remove"></i>
                                    </a>
                                <?php 
                                } else {
                                ?>
                                <?php
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

<!-- Modal Cargar Pago -->
<div class="modal fade" id="modalCargarPago" tabindex="-1" role="dialog" aria-labelledby="pagoModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="pagoModalLabel">Registrar Pago</h4>
            </div>
            <form id="formRegistrarPago" action="datosCurso/abm_liquidacion_cursos.php?actualizarPago" method="POST">
                <div class="modal-body">
                    <!-- Campo Oculto para el ID -->
                    <input type="hidden" id="modal_id_liquidacion" name="id_liquidacion">
                    
                    <div class="form-group">
                        <label for="modal_fecha_pago">Fecha de Pago:</label>
                        <input type="date" 
                               id="modal_fecha_pago" 
                               name="fecha_pago" 
                               class="form-group-sm form-control" 
                               value="<?php echo date('Y-m-d'); ?>" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm">Confirmar Pago</button>
                </div>
            </form>
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
        $('#asistente_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'asistente.php',
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
                $('#idAsistente').val(nameIdMap[item]);
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
        const bloquePeriodo = document.getElementById('bloque_periodo');
        const bloqueCurso = document.getElementById('bloque_curso');

        if (criterio === 'periodo') {
            const anio = document.getElementById('periodoSeleccionado');
            const mes = document.getElementById('mesSeleccionado');

            // Mostrar periodo, ocultar fecha
            bloquePeriodo.style.display = 'block';
            bloqueCurso.style.display = 'none';
            
            // Configurar obligatorios
            anio.required = true;
            mes.required = true;
            //curso_elegido.required = false;
            //curso_elegido.value = ''; // Limpia el valor si cambia de criterio
        } else {
            const curso_elegido = document.getElementById('cursoSeleccionado');

            // Mostrar fecha, ocultar periodo
            bloquePeriodo.style.display = 'none';
            bloqueCurso.style.display = 'block';
            
            // Configurar obligatorios
            //anio.required = false;
            //mes.required = false;
            curso_elegido.required = true;
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
</script>
