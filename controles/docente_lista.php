<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "language": {
                "url": "../public/lang/esp.lang"
            },
            dom: 'T<"clear">lfrtip',
            tableTools: {
               "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
               "aButtons": [
                    {
                        "sExtends": "pdf",
                        "mColumns" : [0, 1, 2, 3, 4],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                        "sTitle": "Listado de docentes",
                        "sPdfOrientation": "portrait",
                        "sFileName": "listado_de_docentes.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                    }
                    
            ]
            }
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
<div class="panel-heading"><h4><b>Directores y Docentes de cursos</b></h4></div>
<div class="panel-body">
    <div class="row">
        <div class="col-xs-9">&nbsp;</div>
        <div class="col-xs-3">
            <a href="docente_form.php?agregar" class="btn btn-info">Agregar Director/Docente</a>
        </div>
    </div>
    <br>
    <?php
    $resDocentes = $cursos_pdo->obtenerDocentes(0);
    //var_dump($facturas);
    if ($resDocentes['estado'] && sizeof($resDocentes['datos'])>0) {
    ?>    
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Matrícula</th>
                    <th>Apellido y Nombres</th>
                    <th>Fecha de carga</th>
                    <th style="width: 30px">Editar</th>
                    <th style="width: 30px">Cursos asignados</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($resDocentes['datos'] as $dato) {
                $id_docente = $dato['id_docente_cursos'];
                $matricula = $dato['matricula'];
                $apellido_nombre = $dato['apellido_nombre'];
                $fecha_carga = $dato['fecha_carga'];
                ?>
                <tr>
            	    <td><?php echo $id_docente;?></td>
		            <td><?php echo $matricula;?></td>
                    <td><?php echo $apellido_nombre;?></td>
                    <td><?php echo $fecha_carga; ?></td>
                    <td>
                        <a href="docente_form.php?editar&id=<?php echo $id_docente; ?>" class="btn btn-info">Editar</a>
                    </td>
                    <td>
                        <button type="button" 
                                class="btn btn-warning btn-sm btn-ver-cursos" 
                                data-id="<?php echo $id_docente; ?>" 
                                data-toggle="modal" 
                                data-target="#modalCursos">
                            Ver Cursos
                        </button>
                    </td>
               </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    <?php
    } else {
      ?>
        <div class="<?php echo $resDocentes['clase']; ?>" role="alert">
            <span class="<?php echo $resDocentes['icono']; ?>" ></span>
            <span><strong><?php echo $resDocentes['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
    ?>
</div>
</div>

<!-- Modal para mostrar cursos -->
<div class="modal fade" id="modalCursos" tabindex="-1" role="dialog" aria-labelledby="modalCursosLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modalCursosLabel"><b>Cursos Asignados</b></h4>
      </div>
      <div class="modal-body">
        <!-- El contenido se cargará aquí dinámicamente -->
        <div id="contenidoModalCursos" class="text-center">
            <p>Cargando cursos...</p>
        </div>
      </div>
      <div class="modal-footer">
        <!-- NUEVO BOTÓN: Enlace para agregar un nuevo curso -->
        <a href="#" id="btnAgregarCursoDocente" class="btn btn-success">
            <span class="glyphicon glyphicon-plus"></span> Asignar nuevo curso
        </a>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php
require_once '../html/footer.php';
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const botonesCursos = document.querySelectorAll('.btn-ver-cursos');
    const contenedorModal = document.getElementById('contenidoModalCursos');
    // Seleccionamos el nuevo botón del modal
    const btnAgregarCurso = document.getElementById('btnAgregarCursoDocente');

    botonesCursos.forEach(boton => {
        boton.addEventListener('click', function() {
            const idDocente = this.getAttribute('data-id');
            
            // Actualizamos dinámicamente el enlace del botón para pasar el ID por GET
            btnAgregarCurso.setAttribute('href', 'docente_asignar_curso.php?id_docente=' + idDocente);
            
            // Mostramos mensaje de carga
            contenedorModal.innerHTML = '<div class="text-center"><p>Buscando información...</p></div>';
            
            const datos = new FormData();
            datos.append('id_docente', idDocente);

            fetch('curso.php?por_docente', {
                method: 'POST',
                body: datos
            })
            .then(respuesta => {
                if (!respuesta.ok) throw new Error('Error en el servidor');
                return respuesta.text();
            })
            .then(html => {
                contenedorModal.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                contenedorModal.innerHTML = '<div class="alert alert-danger">Error al cargar los cursos.</div>';
            });
        });
    });
});
</script>
