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
    //obtengo los consejeros filtrados por asistencia
    $colegiadoCargoLogic = new colegiadoCargoLogic();
    $resConsejeros = $colegiadoCargoLogic->obtenerConsejerosVigentes();
    if ($resConsejeros['estado']) {
        $resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
        $arrayAsistentes = array();
        $arrayNoAsistentes = array();
        if ($resAsistentes['estado']) {
        	//si NO estan cargados los consejeros vigentes, entonces primero los agregamos a la reunion con el campo presente=N
        	if (sizeof($resAsistentes['datos']) == 0) {
        		$resAsistentes = $reunionConsejoLogic->agregarAsistentesPorIdReunionConsejo($idReunionConsejo, $resConsejeros['datos']);
        		if ($resAsistentes['estado']) {
 					$resAsistentes = $reunionConsejoLogic->obtenerAsistentesPorIdReunionConsejo($idReunionConsejo);
        		} else {
		        	$continua = FALSE;
					$mensaje .= $resAsistentes['mensaje'];
		        }
        	}

        	if ($continua) {
	            $asisten = 0;
	            $no_asisten = 0;
	            foreach ($resAsistentes['datos'] as $fila) {
	                if ($fila['presente'] <> 'S') {
	                	$no_asisten += 1;
	                	$arrayNoAsistentes[$no_asisten] = $fila['idReunionConsejoAsistente'];
	                } else {
	                	$asisten += 1;
	                	$arrayAsistentes[$asisten] = $fila['idReunionConsejoAsistente'];
	                }
	            }
	        }         
        } else {
        	$continua = FALSE;
			$mensaje .= $resAsistentes['mensaje'];
        }
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
                        <h3>Asistentes: <?php echo sizeof($arrayAsistentes); ?></h3>
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
                                	$idReunionConsejoAsistente = $fila['idReunionConsejoAsistente'];
                                    $matricula = $fila['matricula'];
                                    $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
                                    $asiste = $fila['presente'];
                                    if ($asiste <> 'S') continue;
	                                ?>
	                                <tr>
	                                    <td><?php echo $orden; ?></td>
	                                    <td><?php echo $matricula; ?></td>
	                                    <td><?php echo $apellidoNombre; ?></td>
	                                    <td>
	                                    	<a href="datosReunionConsejo\abm_reunion_consejo_asistente.php?id=<?php echo $idReunionConsejoAsistente; ?>&no_asiste" class="btn btn-info">NO Asiste</a>
	                                    </td>
	                                </tr>    
                                	<?php
                                	$orden += 1;
                                }
                                ?>
                            </tbody>
                        </table>      
                    </div>
                    <div class="col-md-6">
                    	<form method="POST" action="datosReunionConsejo\abm_reunion_consejo_asistente_varios.php">
                    		<div class="row">
                    			<div class="col-md-4">
		                        	<h3>NO Asisten: <?php echo sizeof($arrayNoAsistentes); ?></h3>
		                        </div>
		                        <div class="col-md-8">
		                        	<br>
			                        <button type="submit" class="btn btn-info">Confirma Seleccionados</button>
									<input type="hidden" name="idReunionConsejo" id="idReunionConsejo" value="<?php echo $idReunionConsejo ?>" />
								</div>
							</div>
	                        <table id="tablaAsistentes" class="table">
	                            <thead>
	                                <tr>
	                                	<th><b>Orden</b></th>
	                                	<th><b>Asiste</b></th>
	                                    <th><b>Matrícula</b></th>
	                                    <th><b>Apellido y Nombre</b></th>
	                                    <th></th>
	                                </tr>
	                            </thead>
	                            <tbody>
	                                <?php
	                                $orden = 1;
	                                foreach ($resAsistentes['datos'] as $fila) {
	                                	$idReunionConsejoAsistente = $fila['idReunionConsejoAsistente'];
	                                    $matricula = $fila['matricula'];
	                                    $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
	                                    $asiste = $fila['presente'];
	                                    if ($asiste == 'S') continue;
		                                ?>
		                                <tr>
		                                    <td><?php echo $orden; ?></td>
		                                    <td>
		                                        <input type="checkbox" name="asistencia[]" class="asistencia" id="asistencia_<?php echo $idReunionConsejoAsistente ?>" data="<?php echo $idReunionConsejoAsistente ?>" value="<?php echo $idReunionConsejoAsistente ?>" />
		                                    </td>
		                                    <td><?php echo $matricula; ?></td>
		                                    <td><?php echo $apellidoNombre; ?></td>
		                                    <td>
		                                    	<a href="datosReunionConsejo\abm_reunion_consejo_asistente.php?id=<?php echo $idReunionConsejoAsistente; ?>&asiste" class="btn btn-info">Asiste</a>
		                                    </td>
		                                </tr>    
	                                	<?php
	                                	$orden += 1;
	                                }
	                                ?>
	                            </tbody>
	                        </table>  
	                    </form>
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
        <div class="<?php echo $resConsejeros['clase'];?>" role="alert">
            <span class="<?php echo $resTresConsejerosiposTramites['icono'];?>" aria-hidden="true"></span>
            <span><?php echo $resConsejeros['mensaje'];?></span>
        </div>
    <?php
    }    
} else {
?>
    <div class="<?php echo $resDependencia['clase'];?>" role="alert">
        <span class="<?php echo $resDependencia['icono'];?>" aria-hidden="true"></span>
        <span><?php echo $resDependencia['mensaje'];?></span>
    </div>
<?php 
}    
include("../html/footer.php");

