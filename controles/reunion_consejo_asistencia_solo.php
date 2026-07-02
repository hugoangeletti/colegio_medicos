<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reunionConsejoLogic.php');
require_once ('../dataAccess/colegiadoCargoLogic.php');

$continua = TRUE;
$mensaje = "";
$reunionConsejoLogic = new reunionConsejoLogic();

if (isset($_GET['id']) && $_GET['id'] <> "") {
	$idReunionConsejo = $_GET['id'];
	$resReunion = $reunionConsejoLogic->obtenerReunionConsejoPorId($idReunionConsejo);
	if ($resReunion['estado']) {
		$reunion = $resReunion['datos'];
		$fechaReunion = $reunion['fecha'];
		$numeroActa = $reunion['numeroActa'];
	} else {
		$continua = FALSE;
		$mensaje .= $resReunion['mensaje'];
	}
} else {
	$continua = FALSE;
	$mensaje .= 'Falta idReunionConsejo - ';
}

if ($continua) {
    $colegiadoCargoLogic = new colegiadoCargoLogic();
    $resConsejeros = $colegiadoCargoLogic->obtenerConsejerosVigentes();
    if ($resConsejeros['estado']) {
        $resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
        
        if ($resAsistentes['estado']) {
        	if (sizeof($resAsistentes['datos']) == 0) {
        		$resAsistentes = $reunionConsejoLogic->agregarAsistentesPorIdReunionConsejo($idReunionConsejo, $resConsejeros['datos']);
        		if ($resAsistentes['estado']) {
					$resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
        		} else {
		        	$continua = FALSE;
					$mensaje .= $resAsistentes['mensaje'];
		        }
        	}
        } else {
        	$continua = FALSE;
			$mensaje .= $resAsistentes['mensaje'];
        }
?>
		<div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
            	<div class="row">
        			<div class="col-md-9 text-center">
		            	<h4><b>Reunión de Consejo de fecha <?php echo cambiarFechaFormatoParaMostrar($fechaReunion);?> - Acta N° <?php echo $numeroActa;?></b></h4>
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
                
                <div class="row text-center" style="margin-bottom: 20px;">
                    <div class="col-md-6">
                        <div class="well well-sm" style="background-color: #d9edf7;">
                            <h4>Asistentes: <b id="totalAsistentes">0</b></h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well well-sm" style="background-color: #f2dede;">
                            <h4>No Asisten: <b id="totalNoAsistentes">0</b></h4>
                        </div>
                    </div>
                </div>

                <form method="POST" action="datosReunionConsejo\abm_reunion_consejo_asistente_varios.php">
                    <input type="hidden" name="idReunionConsejo" id="idReunionConsejo" value="<?php echo $idReunionConsejo ?>" />
                    
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary btn-lg">Confirmar Asistencias Seleccionadas</button>
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
                            foreach ($resAsistentes['datos'] as $fila) {
                                $idReunionConsejoAsistente = $fila['idReunionConsejoAsistente'];
                                $matricula = $fila['matricula'];
                                $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
                                $asiste = $fila['presente']; // 'S' o 'N'
                            ?>
                            <tr>
                                <td><?php echo $orden; ?></td>
                                <td class="text-center">
                                    <input type="checkbox" name="asistencia[]" class="check-asistencia" 
                                           value="<?php echo $idReunionConsejoAsistente; ?>" 
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <script>
$(document).ready(function() {
    // Función encargada de contar y actualizar los textos
    function calcularTotales() {
        var totalCheckboxes = $('.check-asistencia').length;
        var asistentes = $('.check-asistencia:checked').length;
        var noAsistentes = totalCheckboxes - asistentes;

        $('#totalAsistentes').text(asistentes);
        $('#totalNoAsistentes').text(noAsistentes);
    }

    // Escuchar el cambio de estado en cualquier checkbox
    $('.check-asistencia').on('change', function() {
        calcularTotales();
    });

    // Ejecutar al cargar la página por primera vez
    calcularTotales();
});
</script>