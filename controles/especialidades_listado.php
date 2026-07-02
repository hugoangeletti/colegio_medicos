<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/especialidades_pdo.php');

$objEspecialidades = new especialidades_pdo();
$resultado = $objEspecialidades->obtenerEspecialidades();

$permiso_abm = $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 140);

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
?>

<!--<div class="container" style="margin-top: 20px;">-->
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <h2>Listado de Especialidades</h2>
            </div>
            
            <?php if (isset($resultado['mensaje']) && $resultado['mensaje'] != "OK"): ?>
                <div class="<?php echo $resultado['clase']; ?>">
                    <span class="<?php echo $resultado['icono']; ?>"></span> <?php echo $resultado['mensaje']; ?>
                </div>
            <?php endif; ?>

            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-6">
                    <h3 style="margin-top: 0;">Especialidades</h3>
                </div>
                <div class="col-md-6 text-right">
                    <a href="especialidades_imprimir.php" target="_blank" class="btn btn-danger">
                        <span class="glyphicon glyphicon-print"></span> Imprimir PDF
                    </a>
                    
                    <?php if ($permiso_abm) { ?>     
                        <a href="especialidades_form.php" class="btn btn-primary">
                            <span class="glyphicon glyphicon-plus"></span> Nueva Especialidad
                        </a>
                    <?php } ?>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tablaEspecialidades" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr class="info">
                            <th>Id</th>
                            <th>Especialidad</th>
                            <th>Código</th>
                            <th>Código Res. 62707</th>
                            <th>Tipo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($resultado['estado'] && !empty($resultado['datos'])): 
                            foreach ($resultado['datos'] as $reg): 
                                if ($reg['idTipoEspecialidad'] == 3) { continue; }
                        ?>
                            <tr>
                                <td><?php echo $reg['idEspecialidad']; ?></td>
                                <td><?php echo htmlspecialchars($reg['nombreEspecialidad']); ?></td>
                                <td><?php echo htmlspecialchars(isset($reg['codigo']) ? $reg['codigo'] : '-'); ?></td>
                                <td><?php echo htmlspecialchars($reg['codigoResolucion']); ?></td>
                                <td><?php echo htmlspecialchars($reg['tipoEspecialidad']); ?></td>
                                <td class="text-center">
                                    <?php if ($permiso_abm) { ?>     
                                        <a href="especialidades_form.php?id=<?php echo $reg['idEspecialidad']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                            <span class="glyphicon glyphicon-pencil"></span> Editar
                                        </a>
                                    <?php } else {
                                        echo '';
                                    } ?>
                                    <?php
                                    $calificaciones = $reg['calificacionesAgregadas'] != '' ? $reg['calificacionesAgregadas'] : 'Ninguna';
                                    ?>
                                    <button type="button" 
                                            class="btn btn-info btn-sm btn-ver-calificaciones" 
                                            data-nombre="<?php echo htmlspecialchars($reg['nombreEspecialidad']); ?>"
                                            data-calificaciones="<?php echo htmlspecialchars($calificaciones); ?>"
                                            title="<?php echo ($calificaciones == 'Ninguna') ? 'NO TIENE CALIFICACIONES' : ' Ver Calificaciones Agregadas'; ?>"
                                            <?php echo ($calificaciones == 'Ninguna') ? 'disabled' : ''; ?>> <span class="glyphicon glyphicon-eye-open"></span> <?php echo ($calificaciones == 'Ninguna') ? 'NO TIENE CALIFICACIONES' : ' Ver Calificaciones Agregadas'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<!--</div>-->

<div class="modal fade" id="modalCalificaciones" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Calificaciones Agregadas</h4>
      </div>
      <div class="modal-body">
        <p>Calificaciones Agregadas reconocidas: <strong id="lblEspecialidad"></strong></p>
        <div class="well" id="listadoCalificacionesContenedor" style="background-color: #fff; margin-bottom: 0;">
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Inicialización de DataTable con traducción completa al español
    $('#tablaEspecialidades').DataTable({
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "autoWidth": false,
        "pageLength": 25, // Cantidad de filas por defecto
        "order": [[ 1, "asc" ]] // Ordena inicialmente por el nombre de la especialidad
    });

    // Evento delegado para abrir el modal (necesario al usar DataTables por el cambio de páginas)
    $(document).on('click', '.btn-ver-calificaciones', function() {
        var nombre = $(this).data('nombre');
        var calificaciones = $(this).data('calificaciones');

        $('#lblEspecialidad').text(nombre);
        $('#listadoCalificacionesContenedor').html(calificaciones);
        $('#modalCalificaciones').modal('show');
    });
});
</script>
<?php 
require_once '../html/footer.php';
?>