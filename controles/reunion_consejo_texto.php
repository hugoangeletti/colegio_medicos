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
    //obtengo los consejeros filtrados por asistencia
    $resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
    if ($resAsistentes['estado']) {
	?>
		<div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
            	<div class="row">
        			<div class="col-md-9 text-center">
		            	<h4><b>Reunión de Consejo de fecha <?php echo cambiarFechaFormatoParaMostrar($fechaReunion);?> - Acta N° <?php echo $numeroActa;?></b></h4>
		            </div>
					<div class="col-md-3 text-right">
					    <form  method="POST" action="reunion_consejo_lista.php">
					        <button type="submit" class="btn btn-default" name='volver' id='name'>Volver </button>
					   </form>
					</div>
            	</div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <h3>Asistentes: </h3>
                        <?php
                        $texoAsistentes = "";
                        foreach ($resAsistentes['datos'] as $fila) {
                        	if ($fila['presente'] == 'S') {
	                            $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
	                            if ($texoAsistentes == "") {
	                            	$texoAsistentes = $apellidoNombre;
	                            } else {
	                            	$texoAsistentes .= ', '.$apellidoNombre;
	                            }
	                        }
                        }
                        ?>
                        <textarea class="form-control" type="text" name="texoAsistentes" id="texoAsistentes" rows="20" readonly><?php echo $texoAsistentes; ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        </div>
    <?php
    } else {
    ?>
        <div class="<?php echo $resAsistentes['clase'];?>" role="alert">
            <span class="<?php echo $resAsistentes['icono'];?>" aria-hidden="true"></span>
            <span><?php echo $resAsistentes['mensaje'];?></span>
        </div>
    <?php
    }    
}    
include("../html/footer.php");

