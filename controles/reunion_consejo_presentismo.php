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

if (isset($_POST['fechaDesde']) && isset($_POST['fechaHasta'])) {
	$fechaDesde = $_POST['fechaDesde'];
	$fechaHasta = $_POST['fechaHasta'];

    //obtengo los consejeros filtrados por asistencia
    $resConsejeros = $reunionConsejoLogic->obtenerConsejerosPresentismo($fechaDesde, $fechaHasta);
    if ($resConsejeros['estado']) {
    	//$listaConsejeros = json_encode($resConsejeros['datos']);
		//cuento las reuniones entre las fechas seleccionadas
		$resReuniones = $reunionConsejoLogic->obtenerReunionesEntreFechas($fechaDesde, $fechaHasta);
    	if ($resReuniones['estado']) {
    		$cantidadReuniones = sizeof($resReuniones['datos']);
    	} else {
    		$cantidadReuniones = 0;
    	}
   		?>
        <div class="panel panel-info">
            <div class="panel-heading">
            	<div class="row">
        			<div class="col-md-9 text-center">
		            	<h4><b>Presentismo por Reunión de Consejo entre <?php echo cambiarFechaFormatoParaMostrar($fechaDesde);?> y <?php echo cambiarFechaFormatoParaMostrar($fechaHasta);?> - Cantidad de reuniones: <?php echo $cantidadReuniones; ?> </b></h4>
		            </div>
					<div class="col-md-2 text-right">
						<form method="POST" action="reunion_consejo_presentismo_imprimir.php" target="_BLANK">
						    <button type="submit" class="btn btn-info">Imprimir presentismo </button>
						    <!--<input type="hidden" name="listaConsejeros" id="listaConsejeros" value="<?php echo $listaConsejeros; ?>">-->
						    <input type="hidden" name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde; ?>">
						    <input type="hidden" name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta; ?>">
						    <input type="hidden" name="cantidadReuniones" id="cantidadReuniones" value="<?php echo $cantidadReuniones; ?>">
		    			</form>
					</div>
					<div class="col-md-1 text-right">
						<a href="reunion_consejo_lista.php?agregar" class="btn btn-info" >Volver</a>
					</div>
            	</div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                    	<?php 
                    	if ($cantidadReuniones > 0) {
                    	?>
	                        <table id="tablaAsistentes" class="table">
	                            <thead>
	                                <tr>
	                                	<th><b>Orden</b></th>
	                                    <th><b>Matrícula</b></th>
	                                    <th><b>Apellido y Nombre</b></th>
	                                    <th colspan="<?php echo $cantidadReuniones; ?>" style="text-align: center;"><b>R E U N I O N E S</b></th>
	                                    <th style="text-align: center;"><b>Cantidad</b></th>
	                                </tr>
	                            </thead>
	                            <tbody>
	                                <?php
	                                $orden = 1;
	                                foreach ($resConsejeros['datos'] as $fila) {
	                                	$idColegiadoCargo = $fila['idColegiadoCargo'];
	                                    $matricula = $fila['matricula'];
	                                    $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
		                                ?>
		                                <tr>
		                                    <td><?php echo $orden; ?></td>
		                                    <td><?php echo $matricula; ?></td>
		                                    <td><?php echo $apellidoNombre; ?></td>
		                                	<?php
		                                    $reuniones = explode(',', $fila['reuniones']);
		                                    $cantidadAsistencias = 0;
		                                    foreach ($reuniones as $reunion) {
		                                    	$reunion_array = explode('_', $reunion);
		                                    	$fecha = $reunion_array[0];
		                                    	$asiste = $reunion_array[1];
		                                    	if ($asiste == 'S') {
		                                    		$cantidadAsistencias += 1;
		                                    		$fecha_asiste = cambiarFechaFormatoParaMostrar($fecha);
		                                    	} else {
		                                    		$fecha_asiste = "";
		                                    	}
		                                    	?>
		                                    	<td><?php echo $fecha_asiste; ?></td>
		                                    <?php 
		                                	}
		                                    ?>
		                                    <td style="text-align: center;"><?php echo $cantidadAsistencias; ?></td>
		                                </tr>    
	                                	<?php
	                                	$orden += 1;
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
    <?php
    } else {
	?>
		<div class="row">&nbsp;</div>
        <div class="<?php echo $resConsejeros['clase']; ?>" role="alert">
            <span class="<?php echo $resConsejeros['icono']; ?>" ></span>
            <span><strong><?php echo $resConsejeros['mensaje']; ?></strong></span>
        </div>
	<?php    	
    }   
} else {
?>
	<div class="panel panel-info">
    	<div class="panel-heading">
           	<div class="row">
        		<div class="col-md-9 text-center">
		           	<h4><b>Presentismo por Reunión de Consejo entre fechas</b></h4>
		        </div>
				<div class="col-md-3 text-right">
					<a href="reunion_consejo_lista.php?agregar" class="btn btn-info" >Volver</a>
				</div>
            </div>
		</div>
        <div class="panel-body">
        	<div class="row">
			    <form method="POST" action="reunion_consejo_presentismo.php">
			        <div class="col-xs-2">
			            <label for="fechaDesde">Fecha desde: *</label>
			            <input class="form-control" type="date" name="fechaDesde" id="fechaDesde" required>
			        </div>
			        <div class="col-xs-2">
			            <label for="fechaHasta">Fecha hasta: *</label>
			            <input class="form-control" type="date" name="fechaHasta" id="fechaHasta" required>
			        </div>
			        <div class="col-xs-2">
			        	<br>
					    <button type="submit" class="btn btn-info">Confirma </button>
					</div>
    			</form>
    		</div>
    	</div>
    </div>
<?php
}    
include("../html/footer.php");

