<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoCargoLogic.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/reunion_consejo_pdo.php');

$continua = TRUE;
$mensaje = "";
$reunionConsejoLogic = new reunion_consejo_pdo();

// Inicialización de variables para Nueva Reunión
$idReunionConsejo = 0;
$fecha = "";
$numeroActa = "";
$tipoReunion = "O";
$observacion = "";
$readOnly = "";
$requerido = " required ";

// Detectar si es Edición o Alta
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idReunionConsejo = intval($_GET['id']);
    $resReunion = $reunionConsejoLogic->obtenerReunionConsejoPorId($idReunionConsejo);
    
    if ($resReunion['estado']) {
        $reunion = $resReunion['datos'];
        $fecha = $reunion['fecha']; 
        $numeroActa = $reunion['numeroActa'];
        $tipoReunion = isset($reunion['tipoReunion']) ? $reunion['tipoReunion'] : $tipoReunion;
        $observacion = isset($reunion['observacion']) ? $reunion['observacion'] : $observacion;
    } else {
        $continua = FALSE;
        $mensaje .= $resReunion['mensaje'];
    }
} 
// NOTA: Si no viene un ID, la variable $idReunionConsejo se queda en 0 y continúa para dar de Alta.

if ($continua) {
    $colegiadoCargoLogic = new colegiadoCargoLogic();
    $resConsejeros = $colegiadoCargoLogic->obtenerConsejerosVigentes();
    
    if ($resConsejeros['estado']) {
        
        $listaFinalConsejeros = array();

        if ($idReunionConsejo > 0) {
            // MODO EDICIÓN: Traer asistentes ya vinculados a esta reunión
            $resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
            if ($resAsistentes['estado']) {
                $listaFinalConsejeros = $resAsistentes['datos'];
            } else {
                $continua = FALSE;
                $mensaje .= $resAsistentes['mensaje'];
            }
        } else {
            // MODO ALTA: Usamos directamente los consejeros vigentes mapeando la estructura esperada por la tabla
            foreach ($resConsejeros['datos'] as $item) {
                $listaFinalConsejeros[] = array(
                    'idReunionConsejoAsistente' => 0, // No tiene todavía
                    'idColegiadoCargo'          => $item['idColegiadoCargo'],
                    'matricula'                 => $item['matricula'],
                    'apellido'                  => $item['apellido'],
                    'nombre'                    => $item['nombre'],
                    'presente'                  => 'N' // Por defecto nadie está marcado al crear
                );
            }
        }
?>
        <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-9 text-center">
                        <h4><b><?php echo ($idReunionConsejo > 0) ? 'Editar Reunión de Consejo' : 'Nueva Reunión de Consejo'; ?></b></h4>
                    </div>
                    <div class="col-md-3 text-right">
                        <form method="POST" action="reunion_consejo_lista.php">
                            <button type="submit" class="btn btn-default" name='volver' id='name'>Volver </button>
                       </form>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($continua) { ?>
                
                <form method="POST" action="datosReunionConsejo\abm_reunion_consejo_asistente_varios.php">
                    <input type="hidden" name="idReunionConsejo" id="idReunionConsejo" value="<?php echo $idReunionConsejo ?>" />
                    
                    <div class="well" style="background-color: #f9f9f9;">
                        <legend style="margin-bottom: 10px; font-size: 16px; font-weight: bold;">Datos de la Reunión</legend>
                        <div class="row">
                            <div class="col-md-2">
                                <label for="fecha">Fecha de la reunión: *</label>
                                <input class="form-control" type="date" name="fecha" id="fecha" value="<?php echo $fecha; ?>" <?php echo $readOnly.$requerido; ?>/>
                            </div>
                            <div class="col-md-2">
                                <label for="tipoReunion">Tipo de reunión: *</label>
                                <select class="form-control" id="tipoReunion" name="tipoReunion"  <?php echo $readOnly.$requerido; ?>>
                                    <option value="O" <?php if($tipoReunion == "O") { echo 'selected'; } ?>>ORDINARIA</option>
                                    <option value="E" <?php if($tipoReunion == "E") { echo 'selected'; } ?>>EXTRAORDINARIA</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="numeroActa">Número de Acta: *</label>
                                <input class="form-control" type="text" name="numeroActa" id="numeroActa" value="<?php echo $numeroActa; ?>" <?php echo $readOnly; ?>/>
                            </div>
                            <div class="col-md-6">
                                <label for="observacion">Observación: </label>
                                <input class="form-control" type="text" name="observacion" id="observacion" value="<?php echo $observacion; ?>" <?php echo $readOnly; ?>/>
                            </div>
                        </div>
                    </div>

                    <div class="row text-center" style="margin-bottom: 20px;">
                        <div class="col-md-6">
                            <div class="well well-sm" style="background-color: #d9edf7; margin-bottom: 0;">
                                <h4>Asistentes: <b id="totalAsistentes">0</b></h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="well well-sm" style="background-color: #f2dede; margin-bottom: 0;">
                                <h4>No Asisten: <b id="totalNoAsistentes">0</b></h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" style="margin-top: 15px; margin-bottom: 15px;">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary btn-lg">Confirmar Reunión y Asistencias</button>
                        </div>
                    </div>

                    <table id="tablaAsistenciaUnica" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="10%"><b>Orden</b></th>
                                <th width="15%" class="text-center"><b>¿Asiste?</b></th>
                                <th width="20%"><b>Matrícula</b></th>
                                <th><b>Apellido y Nombre</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orden = 1;
                            foreach ($listaFinalConsejeros as $fila) {
                                $idReunionConsejoAsistente = $fila['idReunionConsejoAsistente'];
                                $matricula = $fila['matricula'];
                                $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
                                $asiste = $fila['presente']; 
                                
                                // Si es ALTA (id=0), mandamos el idColegiadoCargo para procesar en el backend.
                                // Si es EDICIÓN, mandamos el id de la tabla relacional.
                                $valorCheckbox = ($idReunionConsejo === 0) ? $fila['idColegiadoCargo'] : $idReunionConsejoAsistente;
                            ?>
                            <tr>
                                <td><?php echo $orden; ?></td>
                                <td class="text-center">
                                    <input type="checkbox" name="asistencia[]" class="check-asistencia" 
                                           value="<?php echo $valorCheckbox; ?>" 
                                           style="transform: scale(1.4);"
                                           <?php echo ($asiste == 'S') ? 'checked' : ''; ?> />
                                </td>
                                <td><?php echo $matricula; ?></td>
                                <td><?php echo $apellidoNombre; ?></td>
                            </tr>    
                            <?php
                                $orden += 1;
                            }
                            ?>
                        </tbody>
                    </table>  
                </form>
                <?php } ?>
            </div>
        </div>
        </div>
<?php
    } else {
?>
        <div class="<?php echo $resConsejeros['clase'];?>" role="alert">
            <span class="<?php echo $resConsejeros['icono'];?>" aria-hidden="true"></span>
            <span><?php echo $resConsejeros['mensaje'];?></span>
        </div>
<?php
    }    
} else {
?>
    <div class="alert alert-danger" role="alert">
        <span><?php echo $mensaje; ?></span>
    </div>
<?php 
}    

include("../html/footer.php");
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function calcularTotales() {
        var totalCheckboxes = $('.check-asistencia').length;
        var asistentes = $('.check-asistencia:checked').length;
        var noAsistentes = totalCheckboxes - asistentes;

        $('#totalAsistentes').text(asistentes);
        $('#totalNoAsistentes').text(noAsistentes);
    }

    $('.check-asistencia').on('change', function() {
        calcularTotales();
    });

    calcularTotales();
});
</script>