<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();

if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        //include 'menuColegiado.php';
    }
    
    if (isset($_POST['mensaje'])) {
    ?>
       <div class="ocultarMensaje"> 
           <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php        
        $telefonoFijo = $_POST['telefonoFijo'];
        $telefonoMovil = $_POST['telefonoMovil'];
        $mail = $_POST['mail'];
    } else {
        $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resContacto['estado']) {
            $contacto = $resContacto['datos'];
            $telefonoFijo = $contacto['telefonoFijo'];
            $telefonoMovil = $contacto['telefonoMovil'];
            $mail = $contacto['email'];
        } else {
            $telefonoFijo = '';
            $telefonoMovil = '';
            $mail = '';
        }
    }
    ?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Datos de contacto actuales del colegiado</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <div class="row">
        <div class="col-md-2">
            <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
        </div>
        <div class="col-md-4">
            <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
        </div>
        <div class="col-md-6">&nbsp;</div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center"><h4><b>Actualizar datos de contacto del colegiado</b></h4></div>
    </div>
    <form id="datosContacto" autocomplete="off" name="datosContacto" method="POST" onSubmit="" action="datosColegiadoContacto\actualiza_contacto.php">
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-3">
                <label>Tel&eacute;fono Fijo *</label>
                <input class="form-control" type="text" id="telefonoFijo" name="telefonoFijo" value="<?php echo $telefonoFijo; ?>" placeholder="Ej: 0221 4256311" required=""/>
            </div>
            <div class="col-md-3">
                <label>Tel&eacute;fono m&oacute;vil *</label>
                <input class="form-control" type="text" id="telefonoMovil" name="telefonoMovil" value="<?php echo $telefonoMovil; ?>" placeholder="Ej: 221 5551133" required=""/>
            </div>
            <div class="col-md-6">
                <label>Correo Electr&oacute;nico *</label>
                <input type="email" class="form-control" id="mail" name="mail" value="<?php echo $mail; ?>" placeholder="Ingrese un correo electrónico válido" required="" >
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="text-center">
                <button type="submit"  class="btn btn-success " >Confirma actualizaci&oacute;n</button>
                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                <input type="hidden" name="origenForm" id="origenForm" value="<?php echo $origenForm; ?>" />
            </div>
        </div>    
    </form>
    </div>    
</div>
<?php
}
require_once '../html/footer.php';
