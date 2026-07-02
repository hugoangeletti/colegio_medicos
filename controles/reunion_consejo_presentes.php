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
    $resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
    if ($resAsistentes['estado']) {
   		?>
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
            	<?php 
            	if ($continua) {
            	?>
                <div class="row">
                    <div class="col-md-6">
                        <h3>Presentes: </h3>
                        <table id="tablaAsistentes" class="table">
                            <thead>
                                <tr>
                                	<th><b>Orden</b></th>
                                    <th><b>Matrícula</b></th>
                                    <th><b>Apellido y Nombre</b></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $orden = 1;
                                foreach ($resAsistentes['datos'] as $fila) {
                                    $matricula = $fila['matricula'];
                                    $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
                                    $asiste = $fila['presente'];
                                    if ($asiste <> 'S') continue;
	                                ?>
	                                <tr>
	                                    <td><?php echo $orden; ?></td>
	                                    <td><?php echo $matricula; ?></td>
	                                    <td><?php echo $apellidoNombre; ?></td>
	                                </tr>    
                                	<?php
                                	$orden += 1;
                                }
                                ?>
                            </tbody>
                        </table>      
                    </div>
                </div>
            	<?php 
            	}
            	?>
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

