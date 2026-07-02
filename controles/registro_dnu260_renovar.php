<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();

$continua = TRUE;
if (isset($_GET['idRegistro']) && $_GET['idRegistro'] > 0) {
    $idRegistro = $_GET['idRegistro'];
    $resRegistro = $registroDNU260Logic->obtenerRegistroPorId($idRegistro);
    if ($resRegistro['estado']) {
        $registro = $resRegistro['datos'];
        $apellido = $registro['apellido'];
        $nombre = $registro['nombre'];
        $numero = $registro['numero'];
        $titulo = "Renovar Registro Número: ".$numero.' - '.trim($apellido).', '.trim($nombre); 
    } else {
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

if ($continua) {
    if (isset($_POST['mensaje'])) {
        //var_dump($_POST);
        $fechaRenovacion = $_POST['fechaRenovacion'];
        ?>
        <div class="ocultarMensaje">
            <div class="<?php echo $_POST['clase']; ?>" role="alert">
                <span class="<?php echo $_POST['icono'];?>" aria-hidden="true"></span>
                <span><?php echo $_POST['mensaje'];?></span>
            </div>
        </div>
    <?php
    } else {
        $fechaRenovacion = date('Y-m-d');
    }
}
if ($continua) {
?>
<div class="panel panel-danger">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><?php echo $titulo; ?></h4>
            </div>
            <div class="col-md-3 text-right">
                <form  method="POST" action="registro_dnu260_lista.php">
                    <button type="submit" class="btn btn-danger" name='volver' id='name'>Volver al listado </button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="col-md-12">
        <form id="datosRenovacion" autocomplete="off" name="datosRenovacion" method="POST" onSubmit="" action="datosRegistroDnu260/renovar_registro.php?id=<?php echo $idRegistro ?>">
            <div class="row">
                <div class="col-md-3">
                    <label>Fecha de Renovación *</label>
                <input class="form-control" type="date" name="fechaRenovacion" value="<?php echo $fechaRenovacion; ?>" required=""/>
                </div>
                <div class="col-md-9 text-left">
                    <br>
                    <button type="submit"  class="btn btn-success btn-lg" >Confirma </button>
                </div>
            </div>
        </form>
    </div>
    </div>
</div>
<?php
} else {
?>
    <div class="row">
        <div class="col-md-12 alert alert-danger">MAL INGRESO</div>
    </div>
<?php
}
require_once '../html/footer.php';
