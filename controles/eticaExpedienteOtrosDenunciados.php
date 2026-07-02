<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id']) {
	$idEticaExpediente = $_GET['id'];
} else {
	$continua = FALSE;
	$mensaje .= "INGRESO INCORRECTO";
}
if ($continua) {
?>

<div class="row">&nbsp;</div> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Otros denunciados</b></h4></div>
<div class="panel-body">
    <?php

    $resExpediente = $eticaExpedienteLogic->obtenerEticaExpedientePorId($idEticaExpediente);
    if ($resExpediente['estado']) {
    	$expediente = $resExpediente['datos'];
    ?>
	    <div class="row">
    		<div class="col-md-6">Expediente: <b><?php echo $expediente['caratula']; ?></b></div>
	        <div class="col-md-2">
	            <form method="POST" action="eticaExpedienteOtrosDenunciados_form.php">
	                <button type="submit"  class="btn btn-success" >Agregar denunciado </button>
	                <input type="hidden" id="idEticaExpediente" name="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>">
	                <input type="hidden" id="accion" name="accion" value="1">
	            </form>
	        </div>
	    </div>     
    	<div class="row">&nbsp;</div>

    	<?php 
    	$otrosDenunciados = $eticaExpedienteLogic->obtenerOtrosDenunciadosPorIdEticaExpediente($idEticaExpediente);
    	if ($otrosDenunciados['estado']) {
    	?>
    	<div class="row">
    		<div class="col-md-8">
	            <table  id="tablaDenunciados" class="display">
	                <thead>
	                    <tr>
	                    	<th style="display: none;">Id</th>
	                        <th>N&ordm; Matrícula</th>
	                        <th>Apellido y Nombre</th>
	                        <th>Acción</th>
	                    </tr>
	               	</thead>
	               	<tbody>
	               		<?php
	                   	foreach ($otrosDenunciados['datos'] as $datos) {
	                       	$idEticaExpedienteOtroDenunciado = $datos['idEticaExpedienteOtroDenunciado'];
	                       	$matricula = $datos['matricula'];
	                       	$apellido = $datos['apellido'];
	                       	$nombre = $datos['nombre'];
	                   	?>
	                   	<tr>
	                		<td><?php echo $matricula;?></td>
	                        <td><?php echo trim($apellido).' '.trim($nombre);?></td>
	                        <td><a href="datosEticaExpediente/abm_eticaExpedienteOtrosDenunciados.php?id=<?php echo $idEticaExpedienteOtroDenunciado; ?>" class="btn btn-danger" role="button" onclick="return confirmar()">Eliminar</a></td>
	                   </tr>     
	                   <?php
	                   }
	               		?>
	               	</tbody>    
	            </table>
            </div>
        </div>
        <?php    
        }
        else
        {
        ?>
        	<div class="row">
	            <div class="col-md-12 alert alert-warning" role="alert">
	            	<span class="glyphicon glyphicon-exclamation-sign" ></span><span><strong>&nbsp;&nbsp;NO HAY OTROS DENUNCIADOS REGISTRADOS</strong></span>
	            </div>
            </div>
        <?php
        }    
    }
    else 
    {
    ?>
        <h1>Colegiados</h1>
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span><strong>&nbsp;&nbsp;ERROR AL BUSCAR LOS RECLAMOS</strong></span>
        </div>
    <?php
    }
    ?>
</div>
</div>
<?php    
} else {
?>
	<div class="row">
		<div class="col-md-12 alert alert-danger" role="alert">
	        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span><strong>&nbsp;&nbsp;ERROR: INGRESO INCORRECTO</strong></span>
	    </div>
    </div>
<?php 
}
?>
<div class="row text-center">
    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="eticaExpediente_lista.php">
        <button type="submit"  class="btn btn-info" >Volver </button>
    </form>
</div>
<?php
require_once '../html/footer.php';

?>
<script>
$(document).ready(
    function () {
                $('#tablaDenunciados').DataTable({
                    "iDisplayLength":10,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmar()
{
	if(confirm('¿Estas seguro de elimiar esta entrega de recetarios?'))
		return true;
	else
		return false;
}
</script>