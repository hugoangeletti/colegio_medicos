<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');

if (!isset($_GET['id_docente'])) {
    die("Error: No se especificó un ID de docente válido.");
}
$id_docente = intval($_GET['id_docente']);

// Capturar el estado de la redirección para las alertas
$mensaje = "";
$tipo_mensaje = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $tipo_mensaje = "alert alert-success";
        $mensaje = htmlspecialchars($_GET['msg']);
    } elseif ($_GET['status'] === 'error') {
        $tipo_mensaje = "alert alert-danger";
        $mensaje = "Error al guardar: " . htmlspecialchars($_GET['msg']);
    }
}
$cursos_pdo = new cursos_pdo();

// Obtener datos del docente
$resDocente = $cursos_pdo->obtenerDocentePorId($id_docente);
if ($resDocente['estado']) {
    $apellido_nombre = $resDocente['datos']['ApellidoNombres'];
}

?>
    <!-- Bloque de alertas que lee el estado enviado por guardar_asignacion.php -->
    <?php if (!empty($mensaje)): ?>
       <div class="ocultarMensaje"> 
            <div class="<?php echo $tipo_mensaje;?>" role="alert">
                <strong><?php echo $mensaje;?></strong>
            </div>
       </div>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><b>Asignar Cursos a: <?php echo htmlspecialchars($apellido_nombre); ?></b></h4>
        </div>
        <div class="panel-body">
            
            <!-- FORMULARIO DE ENVÍO -->
            <div class="well">
                <h4><b>Asignar un nuevo curso vigente</b></h4>
                
                <!-- Apunta directamente al nuevo archivo PHP independiente -->
                <form action="datosCurso/guardar_asignacion.php" method="POST" class="form-inline">
                    
                    <!-- Enviamos el ID del docente oculto para que lo procese el backend -->
                    <input type="hidden" name="id_docente" value="<?php echo $id_docente; ?>">
                    <input type="hidden" name="accion" value="agregar">

                    <div class="form-group" style="width: 50%;">
                        <?php
                        $resCursos = $cursos_pdo->obtenerCursos('A');
                        if ($resCursos['estado']) {
                            $cursos_disponibles = $resCursos['datos'];
                        ?>
                        <select name="id_curso" id="id_curso" class="form-control" style="width: 100%;" required>
                            <option value="">-- Seleccione un curso vigente para asignar --</option>
                            <?php foreach ($cursos_disponibles as $cd): ?>
                                <option value="<?php echo $cd['idCurso']; ?>">
                                    [<?php echo htmlspecialchars($cd['idCurso']); ?>] <?php echo htmlspecialchars($cd['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php 
                        }
                        ?>
                    </div>

                    <div class="form-group" style="width: 20%;">
                        <select name="id_cursos_cargo" id="id_cursos_cargo" class="form-control" style="width: 100%;" required>
                            <option value="">-- Seleccione un cargo en el curso --</option>
                            <option value="1">Director</option>
                            <option value="2">Coordinador</option>
                            <option value="3">Docente</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success" <?php echo (count($cursos_disponibles) == 0) ? 'disabled' : ''; ?>>
                        <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Asignación
                    </button>
                    <a href="docente_lista.php" class="btn btn-default">Volver a la lista</a>
                </form>
            </div>

            <br>

            <!-- TABLA DE CURSOS ASIGNADOS -->
            <h4><b>Cursos actualmente asignados</b></h4>
            <?php 
            $resCursos = $cursos_pdo->obtenerCursosPorDocente($id_docente);
            if ($resCursos['estado']) {
                $cursos_asignados = $resCursos['datos'];
                if (count($cursos_asignados) > 0) { ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 150px;">Id</th>
                                <th>Nombre del Curso</th>
                                <th>Cargo en el Curso</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cursos_asignados as $ca): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ca['id_cursos_docente']); ?></td>
                                    <td><?php echo '['.htmlspecialchars($ca['id_cursos']).'] '. htmlspecialchars($ca['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($ca['cargo']); ?></td>
                                    <td>
                                        <form action="datosCurso/guardar_asignacion.php" method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar esta asignación de curso?');">
                                            
                                            <!-- Enviamos el ID del docente oculto para que lo procese el backend -->
                                            <input type="hidden" name="id_docente" value="<?php echo $id_docente; ?>">
                                            <input type="hidden" name="id_cursos_docente" value="<?php echo $ca['id_cursos_docente']; ?>">
                                            <input type="hidden" name="id_curso" value="<?php echo $ca['id_cursos']; ?>">
                                            <input type="hidden" name="accion" value="borrar">
                                            
                                            <button type="submit" class="btn btn-danger" 
                                                    title="Borrar Asignación">
                                                <span class="glyphicon glyphicon-remove"></span> 
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php 
                } else { 
                ?>
                    <div class="alert alert-warning">Este docente aún no tiene ningún curso asignado.</div>
                <?php 
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
// Desvanecer la alerta después de 4 segundos
setTimeout(function() {
    var msg = document.querySelector('.ocultarMensaje');
    if(msg) msg.style.display = 'none';
}, 4000);
</script>
