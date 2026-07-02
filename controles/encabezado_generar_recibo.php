<?php
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();

if (isset($idAsistente) && $idAsistente <> "") {
    $cursos_pdo = new cursos_pdo();
    $resAsistente = $cursos_pdo->obtenerAsistentePorId($idAsistente);
	if ($resAsistente['estado']) {
	    $asistente = $resAsistente['datos'];
	    $idColegiado = $asistente['idColegiado'];
	    if (!isset($idColegiado)) {
		    $matricula = NULL;
			$email = NULL;
		}
	    $apellidoNombre = $asistente['apellidoNombre'];
		$tituloCurso = $asistente['tituloCurso'];
	}
} else {
	$tituloCurso = NULL;
}

if (isset($idColegiado) && $idColegiado <> "") {
	$colegiadoLogic = new colegiadoLogic();
	$resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
	if ($resColegiado['estado'] && $resColegiado['datos']) {
	    $colegiado = $resColegiado['datos'];
	    $matricula = $colegiado['matricula'];
	    $apellidoNombre = $colegiado['apellido'].', '.$colegiado['nombre'];
		$email = NULL;
	    $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
	    if ($resContacto['estado']){
	        $colegiadoContacto = $resContacto['datos'];
	        $email = $colegiadoContacto['email'];
	    }
	}
}
?>
        <div class="col-md-12 alert alert-info">
        	<div class="row">
	            <div class="col-md-9">
	                <h4>Caja Diaria - <?php echo $tituloCajaDiaria; ?></h4>
	            </div>
	            <div class="col-md-3 text-left">
	                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
	                    <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
	                </form>
	            </div>
            </div>
        	<div class="row">
	            <h4>
	            	<?php 
	            	if (isset($matricula)) {
	            	?>
			            <div class="col-md-2">
			                <label>Matr&iacute;cula:&nbsp; </label><?php echo $matricula; ?>
			            </div>
		            <?php 
			        }
			        ?>
		            <div class="col-md-5">
		                <label>Apellido y Nombres:&nbsp; </label><?php echo $apellidoNombre; ?>
		            </div>
		            <?php 
			        if (isset($email)) {
			        ?>
			            <div class="col-md-5">
			                <label>Mail registrado:&nbsp; </label><?php echo $email; ?>
			            </div>
		            <?php
		        	}
		        	?>
		        	<br>
	            	<?php 
	            	if (isset($tituloCurso)) {
	            	?>
			            <div class="col-md-6">
			                <label>Curso:&nbsp; </label><?php echo $tituloCurso; ?>
			            </div>
		            <?php 
			        } 
			        ?>
	            </h4>
            </div>
        </div>
<!--
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Caja Diaria - Cobranza de colegiación</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cajadiaria.php">
                    <button type="submit"  class="btn btn-info" >Volver a Caja Diaria</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row alert-info">
            <h4>
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-4">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
            </div>
            <div class="col-md-4">
                <label>Mail registrado:&nbsp; </label><?php echo $colegiadoContacto['email']; ?>
            </div>
            <div class="col-md-2">&nbsp;</div>
            </h4>
        </div>
    </div>
</div>
-->