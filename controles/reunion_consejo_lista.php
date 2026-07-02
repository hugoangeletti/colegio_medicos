<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/reunion_consejo_pdo.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });   

function confirmaAnular(accion)
{
    if(confirm('¿Estas seguro de ' + accion + ' registro?'))
        return true;
    else
        return false;
}

// FUNCIÓN JAVASCRIPT PURA PARA CARGAR EL MODAL
function verAsistentes(boton) {
    // Recuperamos los atributos asignados por PHP
    var numeroActa = boton.getAttribute('data-acta');
    var asistentesJson = boton.getAttribute('data-asistentes');
    
    // Parseamos el JSON de los asistentes
    var asistentes = JSON.parse(asistentesJson);
    
    // Cambiamos el título del modal
    document.getElementById('tituloModalAsistentes').innerHTML = '<b>Asistentes - Reunión N° ' + numeroActa + '</b>';
    
    var cuerpo = document.getElementById('cuerpoModalAsistentes');
    cuerpo.innerHTML = ''; // Limpiamos el contenedor
    
    if (asistentes.length > 0) {
        var html = '<ul class="list-group">';
        for (var i = 0; i < asistentes.length; i++) {
            html += '<li class="list-group-item">';
            html += '<strong>' + asistentes[i].apellido_nombre + '</strong> ';
            html += '<span class="badge">Mat: ' + asistentes[i].matricula + '</span>';
            html += '</li>';
        }
        html += '</ul>';
        cuerpo.innerHTML = html;
    } else {
        cuerpo.innerHTML = '<div class="alert alert-warning text-center">No se registraron asistentes o la reunión fue borrada.</div>';
    }
}
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
?> 
<div class="container">
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Listado de Reuniones de Consejo</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] != "") {
                $periodoSeleccionado = $_POST['periodoSeleccionado'];
            } else {
                $periodoSeleccionado = date('Y');
            }

            $reunionConsejoLogic = new reunion_consejo_pdo();
            $resReuniones = $reunionConsejoLogic->obtenerReunionesPorPeriodo($periodoSeleccionado);           
            
            // Traemos todos los asistentes del año seleccionado de una sola vez
            //$resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorPeriodo($periodoSeleccionado);
            ?>
            <div class="row">
                <div class="col-xs-8">
                    <form method="POST" action="reunion_consejo_lista.php">
                        <div class="col-xs-5">
                            <label for="periodoSeleccionado">Seleccione Año:</label>
                            <select class="form-control" id="periodoSeleccionado" name="periodoSeleccionado" required onChange="this.form.submit()">
                                <option value="" selected>Seleccione Año</option>
                                <?php
                                $periodo = date('Y');
                                while ($periodo >= 2012) {
                                ?>
                                    <option value="<?php echo $periodo; ?>" <?php if($periodo == $periodoSeleccionado) { echo 'selected'; } ?>><?php echo $periodo; ?></option>
                                <?php
                                    $periodo--;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-xs-3">&nbsp;</div>
                    </form>    
                </div>
                <div class="col-xs-2 text-center">
                    <?php
                    if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 120)) {
                    ?>
                        <a href="reunion_consejo_asistencia.php" class="btn btn-info" >Agregar Reunión</a>
                    <?php 
                    } 
                    ?>
                </div>
                <div class="col-xs-2 text-center">
                    <a href="reunion_consejo_presentismo.php" class="btn btn-info" >Imprimir Presentismo</a>
                </div>
            </div>
            <?php
            if ($resReuniones['estado']){
            ?>
                <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Id</th>
                                <th style="text-align: center;">Fecha</th>
                                <th style="text-align: center;">Reunión N°</th>
                                <th style="text-align: center;">Tipo de reunión</th>
                                <th style="text-align: center;">Asistieron</th>
                                <th style="text-align: center;">No Asistieron</th>
                                <th style="text-align: center; width: 450px;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resReuniones['datos'] as $dato) {
                          $idReunionConsejo = $dato['id'];
                          $fecha = $dato['fecha'];
                          $numeroReunion = $dato['numeroActa'];
                          $tipoReunion = $dato['tipoReunion'];
                          $observacion = $dato['observacion'];
                          switch ($tipoReunion) {
                              case 'O':
                                  $tipoReunionDetalle = "ORDINARIA";
                                  break;
                              case 'E':
                                  $tipoReunionDetalle = "EXTRAORDINARIA";
                                  break;
                              default:
                                  $tipoReunionDetalle = "Sin detalle (".$tipoReunion.")";
                                  break;
                          }
                          $cantidadAsisten = $dato['cantidadAsisten'];
                          $cantidadNoAsisten = $dato['cantidadNoAsisten'];
                          $boton_borra_activar = 'Borrar';
                          if ($dato['borrado'] == 1) {
                            $tipoReunionDetalle = '<b>BORRADA</b>'; 
                            $cantidadAsisten = null;
                            $cantidadNoAsisten = null;
                            $boton_borra_activar = 'Activar';
                          }

                          // Buscamos si esta reunión en particular tiene asistentes en nuestro array pre-cargado
                          $resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
                          $listadoAsistentes = $resAsistentes['datos'];

                          $asistentesDeEstaReunion = array();
                          foreach ($listadoAsistentes as $asistente) {
                              if ($asistente['presente'] <> 'S') { continue; }
                              //$asistentesDeEstaReunion[] = $asistente['apellido'].' '.$asistente['nombre'].' ('.$asistente['matricula'].')';
                              $asistentesDeEstaReunion[] = array('apellido_nombre' => $asistente['apellido'].' '.$asistente['nombre'], 'matricula' => $asistente['matricula']);
                          }
                          // Lo transformamos en un string JSON seguro para atributos HTML
                          $jsonAsistentes = htmlspecialchars(json_encode($asistentesDeEstaReunion), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr style="text-align: center;">
                           <td><?php echo $idReunionConsejo;?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($fecha);?></td>
                           <td><?php echo $numeroReunion;?></td>
                           <td><?php echo $tipoReunionDetalle;?></td>
                           <td><?php echo $cantidadAsisten;?></td>
                           <td><?php echo $cantidadNoAsisten;?></td>
                           <td>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAsistentes" data-acta="<?php echo $numeroReunion; ?>" data-asistentes="<?php echo $jsonAsistentes; ?>" onclick="verAsistentes(this)" title="Ver Asistentes">
                                    <span class="glyphicon glyphicon-eye-open"></span> Ver Asistentes
                                </button>

                                <?php
                                if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 120)) {
                                ?>
                                    <a href="reunion_consejo_asistencia.php?id=<?php echo $idReunionConsejo; ?>" class="btn btn-info">Editar Reunion / Asistencia</a>
                                    <a href="datosReunionConsejo\abm_reunion_consejo.php?id=<?php echo $idReunionConsejo; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular('<?php echo $boton_borra_activar; ?>')"><?php echo $boton_borra_activar; ?></a>
                                    <a href="reunion_consejo_texto.php?id=<?php echo $idReunionConsejo; ?>&editar" class="btn btn-info">Texto</a>
                                <?php
                                } else {
                                ?>
                                    <a href="reunion_consejo_presentes.php?id=<?php echo $idReunionConsejo; ?>" class="btn btn-info">Presentes</a>
                                <?php
                                }
                                ?>
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
                <div class="row">&nbsp;</div>
                <div class="<?php echo $resReuniones['clase']; ?>" role="alert">
                    <span class="<?php echo $resReuniones['icono']; ?>" ></span>
                    <span><strong><?php echo $resReuniones['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="modalAsistentes" tabindex="-1" role="dialog" aria-labelledby="tituloModalAsistentes">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="tituloModalAsistentes">Asistentes</h4>
      </div>
      <div class="modal-body" id="cuerpoModalAsistentes">
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