<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoRecetariosLogic.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/especialidades_pdo.php');

$objEspecialidades = new especialidades_pdo();

if (isset($_GET['idColegiado']) || isset($_POST['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    if (isset($_GET['idColegiado'])) {
        $idColegiado = $_GET['idColegiado'];
    } else {
        $idColegiado = $_POST['idColegiado'];
    }
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $continua = TRUE;
    
        if (isset($_POST['mensaje'])) {
        ?>
           <div class="ocultarMensaje"> 
               <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
           </div>
         <?php
            $idReceta = $_POST['idReceta'];
            $idEspecialidad = $_POST['idEspecialidad'];
            $serie = $_POST['serie'];
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $cantidad = $_POST['cantidad'];
        } else {
            $idReceta = NULL;
            $idEspecialidad = NULL;
            $serie = NULL;
            $desde = NULL;
            $hasta = NULL;
            $cantidad = NULL;
        }

    ?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Solicitud de Recetarios</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_recetarios.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Recetarios del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <div class="row">
        <div class="col-md-2">
            <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
        </div>
        <div class="col-md-5">
            <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center"><h4><b>Nuevo Entrega de Recetarios</b></h4></div>
    </div>

    <?php
    if (isset($_POST['idReceta'])) {
        $idReceta = $_POST['idReceta'];
    } else {
        $idReceta = NULL;
    }
    ?>
    <div class="row">&nbsp;</div>
    <form id="datosRecetario" autocomplete="off" name="datosRecetario" method="POST" onSubmit="" target="_blank" action="datosColegiadoRecetas/genera_receta.php">
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Especialidades *</label>
                <select class="form-control" id="idEspecialidad" name="idEspecialidad" required="">
                    <option value="">-- Seleccione Especialidad --</option>
                    <?php
                    $resEspecialidades = $objEspecialidades->obtenerEspecialidades();
                    if ($resEspecialidades['estado']) {
                        foreach ($resEspecialidades['datos'] as $row) {
                            if ($row['idTipoEspecialidad'] == 3) { continue; }
                            ?>
                            <option value="<?php echo $row['idEspecialidad'] ?>" <?php if($idEspecialidad == $row['idEspecialidad']) { echo 'selected'; } ?>><?php echo $row['nombreEspecialidad'] ?></option>
                        <?php
                        }
                    } else {
                    ?>
                        <div class="col-md-12">
                            <div class="<?php echo $resEspecialidades['clase']; ?>" role="alert">
                                <span class="<?php echo $resEspecialidades['icono']; ?>" aria-hidden="true"></span>
                                <span><strong><?php echo $resEspecialidades['mensaje']; ?></strong></span>
                            </div>        
                        </div>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Serie *</label>
                <input class="form-control text-uppercase" type="text" id="serie" name="serie" value="<?php echo $serie; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>N&uacute;mero Desde *</label>
                <input class="form-control" type="number" id="desde" name="desde" value="<?php echo $desde; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>N&uacute;mero Hasta *</label>
                <input class="form-control" type="number" id="hasta" name="hasta" value="<?php echo $desde; ?>" required=""/>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit"  class="btn btn-success btn-lg" >Confirma Entrega</button>
                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
            </div>
        </div>    
    </form>
</div>    
</div>
<?php
    } else {
    ?>
        <div class="col-md-12">
            <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
                <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
            </div>        
        </div>
    <?php
    }
}
require_once '../html/footer.php';
